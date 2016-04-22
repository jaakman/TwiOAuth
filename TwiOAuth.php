<?php

require_once 'twitter/util.php';
require_once 'twitter/class.php';



/****
* Twitter OAuth class 
*
*
*/
class TwiOAuth{
    
    /***
    * Parameters
    */
    private $consumer = NULL;           // Consumer Info(API info)
    private $access_token = NULL;       // OAuth Token info
    private $bearer_token = NULL;       // Bearer Token info
    private $response = NULL;
    private $query = NULL;
    
    
    
    
    /****
    * __construct
    * @param string consumer_key
    * @param string consumer_secret
    */
    public function __construct( $consumer_key, $consumer_secret ){
        
        if( !empty( $consumer_key ) && !empty( $consumer_secret ) ){
            $this->consumer = new Consumer( $consumer_key, $consumer_secret );
        }else{
            Util::errorMsg( "'Consumer key' and 'Consumer secret' has not been set." );
        }
    }
    
    
    
    /****
    * startAuthorize
    * @param string callback_url
    */
    public function startAuthorize( $callback_url ){
        
        // リクエストトークンを取得
        $query = $this->getRequestToken( $callback_url );
        
        // 認証ページへ遷移
        Util::jump_authorize_page( $query['oauth_token'] );
        
    }
    
    
    
    /****
    * getRequestToken
    * @param string callback_url
    */
    public function getRequestToken( $callback_url ){
        
        $this->access_token = new Token( '', '' );
        // パラメータを設定
        $param = new Parameter( $this->consumer, $this->access_token, $callback_url );
        // ポスト通信
        $response = $this->http( 'oauth/request_token', 'POST', $param );
        // レスポンスをデコード
        $query = Util::response_decode( $response );
        $this->query = $query;
        return $query;
    }
    
    
    
    /****
    * getAccessToken
    * @param string token 
    * @param string verifier
    */
    public function getAccessToken( $token, $verifier ){
        
        // リクエストトークンを設定
        $this->access_token = new Token( $token, null, $verifier );
        
        // パラメータを設定
        $param = new Parameter( $this->consumer, $this->access_token );
        // ポスト通信
        $response = $this->http( 'oauth/access_token','POST', $param );
        // レスポンスをデコード
        $query = Util::response_decode( $response );
        // アクセストークンを設定
        $this->access_token = new Token( $query['oauth_token'], $query['oauth_token_secret'] );
        $this->query = $query;
        return $query;
    }
    
    
    /****
    * getBearerToken
    *
    */
    public function getBearerToken(){
        
        $credential = base64_encode( $this->consumer->key . ':' . $this->consumer->secret );
        // リクエスト用のコンテキストを作成する
        $context = array(
            'http' => array(
                'method' => 'POST' , // リクエストメソッド
                'header' => array(			  // ヘッダー
                    'Authorization: Basic ' . $credential ,
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8' ,
                ) ,
                'content' => http_build_query( array( 'grant_type' => 'client_credentials' ,) ) ,
            ) ,
        ) ;
        $request_url = 'https://api.twitter.com/oauth2/token';
        
        $response = @file_get_contents( $request_url , false , stream_context_create( $context ) );
        
        $query = json_decode( $response );
        
        $this->bearer_token = new Token( $query->access_token, null );
        return $query;
    }
    
    
    /****
    * http
    * @param string api 
    * @param array $param
    * 通信メソッド
    */
    public function http( $api, $method, Parameter $param ){
        
        // リクエストを作成
        if( $api === 'oauth/request_token' || $api === 'oauth/access_token' ){
            $request = new Request( API_URL . $api , $method );
        }else{
            $request = new Request( API_URL . '1.1/' . $api ,$method );
        }
        
        // 署名を作成
        $signature = new Signature;
        // 署名鍵
        $signature->setKey( $this->consumer, $this->access_token );
        // 署名書
        $signature->setData( $request, $param );
        
        // ヘッダーのパラメータ
        $header = $param->getHeader( $signature->getSignature() );
        
        // コンテキストを作成
        $context = new Context( $request->method, $header );
        
        // 追加パラメータをリクエストにマージ
        $request->merge( $param->data_param );
        
        // リクエスト
        // レスポンスを受け取る
        $response = $context->connect( $request->url );
        $this->response = $response;
        return $response;
    }
    
    
    /****
    * Getter Methods
    */
    public function getConsumer() { return $this->consumer; }
    public function getConsumerKey() { return $this->consumer->key; }
    public function getConsumerSecret() { return $this->consumer->secret; }
    public function getOAuth() { return $this->access_token; }
    public function getOAuthToken() { return $this->access_token->token; }
    public function getOAuthSecret() { return $this->access_token->secret; }
    public function getOAuthVerifier() { return $this->access_token->verifier; }
    public function getResponse(){ return $this->response; }
    public function getQuery(){ return $this->query; }
    
    
    /****
    * Setter Methods
    */
    public function setConsumerKey($key){ $this->consumer->key = $key; }
    public function setConsumerSecret($secret) { $this->consumer->secret = $secret; }
    public function setOAuthToken($token) { $this->access_token->token = $token; }
    public function setOAuthSecret($secret) { $this->access_token->secret = $secret; }
    public function setOAuthVerifier($verifier) { $this->access_token->verifier = $verifier;}
} 
?>