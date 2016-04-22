<?php 

require_once 'common.php';
require_once 'TwiAPI.php';

session_start();    // セッション開始

// アクセストークンとアクセストークンシークレットがセッションしている場合
if( !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret']) ) { 
    
    $consumer = new Consumer(Consumer_Key, Consumer_Secret);
    $token = new Token($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $api = new TwiAPI($consumer,$token);
    
    if( !empty($_POST['tweet']) && !empty($_POST['tweet_btn'])){
        $val = array('status' => $_POST["tweet"], 'display_coordinates' => 'false');
        $url = 'statuses/update';
    }
    
    
    if( !empty($url) && !empty($val) ) {
        $resp = $api->post($url, $val);
        var_dump($resp);
    }
}
?>


<!doctype HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <title>tweeeeet</title>
    </head>
    <body>
        <form method="POST">
            <p>ツイート<br /><input type="text" name="tweet"></p>
            <input type="submit" name="tweet_btn" value="tweet">
        </form>
    </body>
</html>