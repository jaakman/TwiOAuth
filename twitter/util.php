<?php 

define('API_URL', 'https://api.twitter.com/');



/****
* Util class
*
*/
class Util{
    
    static public function response_decode($response){
        // リクエストが成功しなかった場合
        if( !isset( $response ) || empty( $response ) ){ return null; }
        // 文字列を[&]で区切る
        $parameters = explode( '&' , $response ) ;

        // エラー判定
        if( !isset( $parameters[1] ) || empty( $parameters[1] ) ){ return null; }
        // それぞれの値を格納する配列
        $query = array();

        // [$parameters]をループ処理
        foreach( $parameters as $parameter ){
            // 文字列を[=]で区切る
            $pair = explode( '=' , $parameter ) ;

            // 配列に格納する
            if( isset($pair[1]) ){ $query[ $pair[0] ] = $pair[1] ; }
        }
           
        // エラー判定
        if( !isset( $query['oauth_token'] ) || !isset( $query['oauth_token_secret'] ) ){ return null; }
        return $query;
    }
    
    
    /*****
    * 認証ページへ遷移
    *
    */
    static public function jump_authorize_page($request_token){
        
        // 認証可能なクエリである
        if( isset($request_token) && !empty($request_token) ){
            // ユーザーを認証画面へ飛ばす
            header( 'Location: https://api.twitter.com/oauth/authorize?oauth_token=' . $request_token ) ;
            exit;
        }
        return false;
    }
    
    
    /****
    * エラーメッセージを出力
    *
    */
    static public function errorMsg($msg){
        echo '<script>alert("{$msg}");console.log("{$msg}");</script>';
    }
}
?>