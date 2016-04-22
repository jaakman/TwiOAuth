<?php
/**************************************************

	アクセストークンの取得

**************************************************/

require_once 'TwiOAuth.php';
require_once 'TwiAPI.php';
require_once 'common.php';

// Twitter OAuth class
$twitter = new TwiOAuth( Consumer_Key, Consumer_Secret );

// GETにoauth_tokenとoauth_verifierが存在する。
if ( !empty( $_GET['oauth_token'] )  && !empty( $_GET['oauth_verifier'] ) ) {
    
    // Twitter OAuth class
    $twitter = new TwiOAuth( Consumer_Key, Consumer_Secret );
    // アクセストークンを取得する
    $query = $twitter->getAccessToken( $_GET['oauth_token'], $_GET['oauth_verifier'] );
    
    session_destroy();
    
    session_start();
    session_regenerate_id(true);
    $_SESSION['oauth_token'] = $query['oauth_token'];
    $_SESSION['oauth_token_secret'] = $query['oauth_token_secret'];
    
    echo '<p>アクセストークン : <br />' . $query['oauth_token'] . '</p>';
    echo '<p>アクセストークンシークレット : <br />' . $query['oauth_token_secret'] . '</p>';
    echo '<p>ユーザ ID : <br />' . $query['user_id'] . '</p>';
    echo '<p>ユーザ名 : <br />' . $query['screen_name'] . '</p>';
    
    header( 'Location: http://172.16.100.190/tweet.php');
}else{
    echo 'アクセストークンの取得に失敗しました。';
}
?>