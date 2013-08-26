<?php
session_start();
require_once 'Weibo.php';
OpenSDK_Tencent_Weibo::init($_GET['TENCENT_APP_KEY'], $_GET['TENCENT_APP_SECRETE']);
define("TENCENT_CALLBACK","tencent_callback.php");
require '../../../../wp-load.php';
require '../../../../wp-admin/includes/admin.php';
do_action('admin_init');
if(!get_option('UYAN_TENCENT_APP_KEY') && !get_option('UYAN_TENCENT_APP_SECRET')){
	update_option('UYAN_TENCENT_APP_KEY',$_GET['TENCENT_APP_KEY']);
	update_option('UYAN_TENCENT_APP_SECRET',$_GET['TENCENT_APP_SECRETE']);
}

$URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pos = strrpos($URL, '/');
$newURL = substr($URL, 0, $pos+1) . TENCENT_CALLBACK;
$_SESSION['TENCENT_APP_KEY'] = $_GET['TENCENT_APP_KEY'];
 $_SESSION['TENCENT_APP_SECRETE'] =  $_GET['TENCENT_APP_SECRETE'];

/* 获取request token */
$request_token = OpenSDK_Tencent_Weibo::getRequestToken($newURL);
$url = OpenSDK_Tencent_Weibo::getAuthorizeURL($request_token);
echo "<script>location.assign('$url');</script>";
