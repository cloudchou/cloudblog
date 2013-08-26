<?php
session_start();
if(!class_exists('SaeTOAuthV2') && !class_exists('SaeTClientV2') ){
	require_once('sinaoauth.php');
}
define( "SINA_CALLBACK" , 'sina_callback.php');
require '../../../../wp-load.php';
require '../../../../wp-admin/includes/admin.php';
do_action('admin_init');
if(!get_option('UYAN_SINA_APP_KEY') && !get_option('UYAN_SINA_APP_SECRET')){
	update_option('UYAN_SINA_APP_KEY',$_GET['SINA_APP_KEY']);
	update_option('UYAN_SINA_APP_SECRET',$_GET['SINA_APP_SECRETE']);
}

$_SESSION['SINA_APP_KEY'] = trim($_GET['SINA_APP_KEY']);
$_SESSION['SINA_APP_SECRETE'] = trim($_GET['SINA_APP_SECRETE']);
$sina_o = new SaeTOAuthV2( $_GET['SINA_APP_KEY'] , $_GET['SINA_APP_SECRETE'] );

$URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pos = strrpos($URL, '/');
$newURL = substr($URL, 0, $pos+1) . SINA_CALLBACK;

$sina_aurl = $sina_o->getAuthorizeURL($newURL);
header("location: ". $sina_aurl);

