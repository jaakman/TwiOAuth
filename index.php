<?php
/**************************************************

	アクセストークンの取得

**************************************************/
require_once 'TwiOAuth.php';
require_once 'common.php';

// Twitter OAuth class
$twi = new TwiOAuth( Consumer_Key, Consumer_Secret );

// OAuth認証を開始する
$twi->startAuthorize( 'http://172.16.100.190/oauth.php' );
?>
