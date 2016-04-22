<?php

/*****
* Consumer Class
*
*/
class Consumer{
    
    public $key = Null;         // Consumer Key(API Key)
    public $secret = Null;      // Consumer Secret(API Secret)
    
    
    /***
    * __construct
    * @param string key
    * @param string secret 
    */
    public function __construct($key = Null, $secret = Null){
        $this->key = $key;
        $this->secret = $secret;
    }
    
}


/*****
* Token Class
*
*/
class Token{
    
    public $token = Null;           // oauth_token
    public $secret = Null;          // oauth_token_secret
    public $verifier = Null;        // oauth_verifier
    
    /***
    * __construct
    * @param string token   Get oauth_token
    * @param string secret Get oauth_token_secret
    * @param string verifier Get oauth_verifier
    */
    public function __construct($token, $secret, $verifier = null){
        $this->token = $token;
        $this->secret = $secret;
        $this->verifier = $verifier;
    }
    
    
    /****
    * getType
    *
    */
    public function getType(){
        
        if( !empty($this->token) ) {
            if( !empty($this->secret) && empty($this->verifier) ){
                return 'access_token';
            }
            elseif ( empty($this->secret) && !empty($this->verifier) ){
                return 'request_token';
            }
            elseif ( empty($this->secret) && empty($this->verifier) ){
                return 'bearer_token';
            }
        }else{
            return null;
        }
    }
}


/****
* Context Class
*
*/
class Context{
    
    public $context = NULL;     // context
    
    
    /****
    * __construct
    * @param string method 
    * @param string header
    */
    public function __construct($method, $header){
        
        // create context
        $this->context = array(
                    'http' => array(
				        'method' => $method , // request method
				        'header' => array(      // Custom header
					        'Authorization: OAuth ' . $header ,
				        ) ,
			        ) ,
		        );
    }
    
    
    /****
    * connect
    * @param string url 
    */
    public function connect($url){
        
        // $url is not empty, 
        if( !empty($url) && !empty($this->context) ){
            echo $url;
            return @file_get_contents( $url, false, stream_context_create( $this->context ) );
        }
        return null;
    }
}


/****
* Parameter class
*
*/
class Parameter{
    
    public $oauth_param = NULL;           // signature parameter
    public $data_param = NULL;   // add parameter
    public $param = NULL;
    
    
    /****
    * __construct 
    * @param Consumer consumer  Consumer(API) info
    * @param Token token    The oauth token info
    * @param string callback_url The callback url
    */
    public function __construct(Consumer $consumer, Token $token,$callback_url = null){
        
        $param = array();
        
        // 必須パラメータを設定
        $param['oauth_consumer_key'] = $consumer->key;
        $param['oauth_timestamp'] = time();
        $param['oauth_signature_method'] = 'HMAC-SHA1';
        $param['oauth_version'] = '1.0';
        $param['oauth_nonce'] = microtime();
        
        // トークンを設定
        if ( !empty($token) && isset($token) ){
            $param['oauth_token'] = $token->token;
            if( !empty($token->verifier) ){ $param['oauth_verifier'] = $token->verifier; }
        }
        
        // 配列の各パラメータの値をURLエンコード
		foreach( $param as $key => $value ){ $param[ $key ] = rawurlencode( $value ); }
        
        // コールバックURLを設定
        if ( !empty($callback_url) && isset($callback_url) ){ $param['oauth_callbak'] = $callback_url; }
        
        $this->param = $param;
        $this->oauth_param = $param;
    }
    
    
    /****
    * merge 
    * @param string add_params  Adds Parameters
    */
    public function merge($add_params){
        
        // 追加パラメータが存在する
        if( !empty($add_params) ){
            $this->data_param = $add_params;
            $this->param = array_merge($add_params, $this->oauth_param);
        }
    }
    
    
    /****
    * getRequest
    *
    */
    public function getRequest(){
        // 連想配列をアルファベット順に並び替え
        $param = $this->param;
        ksort( $param );
        return str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , rawurlencode( http_build_query( $param, '', '&') ) ) ;

    }
    
    
    /****
    * getHeader 
    * @param string signature   The sginature strings
    */
    public function getHeader( $signature ){
        
        if ( !empty($signature) && isset($signature) ){
            $param = $this->param;
            ksort( $param );
            $param['oauth_signature'] = $signature;
            return http_build_query( $param, '', ',' );
        }
        return null;
    }
}




/****
* Request class
*
*/
class Request{
    
    
    public $url = Null;         // Requet URL
    public $method = Null;  // Request URL is method
    
    
    /****
    * __construct
    * @param string url Request URL
    * @param string method Request method 
    */
    public function __construct($url, $method){
        $this->url = $url;
        $this->method = $method;
    }
    
    
    /****
    * toString
    *
    */
    public function toString(){
        return rawurlencode($this->method) . '&' . rawurlencode($this->url);
    }
    
    
    /****
    * merge
    * @param string The Adds parameter 
    */
    public function merge($param){
        if ( !empty($param) ){
            $this->url .= '?' . http_build_query($param);
        }
    }
}





/******
* Signature Class
*
*/
class Signature{
    
    
    public $key = Null;
    public $data = Null;
    
    
    /****
    * setKey
    * @param Consumer consumer 
    * @param Token token
    */
    public function setKey(Consumer $consumer,Token $token = null){
        
        if( !empty($consumer) && !empty($token) ){ 
            $this->key = rawurlencode($consumer->secret) . '&' . rawurlencode($token->secret);
        }
        elseif( !empty($consumer) && empty($token) ){
            $this->key = rawurlencode($consumer->secret) . '&' . rawurlencode('');
        }
        else{ return false; }
        return true;
    }
    
    
    /****
    * setData
    * @param Request request 
    * @param Parameter param
    */
    public function setData(Request $request, Parameter $param){
        if( !empty($request) && !empty($param) ){
            $this->data = rawurlencode($request->method) . '&' . rawurlencode($request->url) . '&' . $param->getRequest();
        }else{ return false; }
        
        return true;
    }
    
    
    /****
    * getSignature
    *
    */
    public function getSignature(){
        if( !empty($this->key) && !empty($this->data)) return base64_encode( hash_hmac( 'sha1', $this->data, $this->key, TRUE) );
    }
}
?>