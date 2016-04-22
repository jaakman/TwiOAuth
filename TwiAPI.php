<?php

require_once 'twitter/class.php';
require_once 'twitter/util.php';


class TwiAPI{
    
    private $access_token = NULL;
    private $consumer = NULL;
    private $response = NULL;
    
    
    
    public function __construct(Consumer $consumer, Token $token){
        $this->responce = NULL;
        
        $this->consumer = $consumer;
        $this->access_token = $token;
    }
    
    
    
    public function post($api, array $values){
        
        $request = new Request( API_URL . '1.1/' . $api . '.json', 'POST');
        
        $parameter = new Parameter( $this->consumer, $this->access_token );
        $parameter->merge($values);
        
        $signature = new Signature;
        
        $signature->setKey($this->consumer, $this->access_token);
        $signature->setData( $request, $parameter);
        
        $header = $parameter->getHeader( $signature->getSignature() );
        $context = new Context( $request->method, $header );
        
        $request->merge( $parameter->data_param );
        
        $response = $context->connect( $request->url );
        $this->response = json_decode( $response );
        
        return json_decode( $response );
    }
} 
?>