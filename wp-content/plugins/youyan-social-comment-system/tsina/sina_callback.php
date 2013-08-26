<?php
session_start();
if(!class_exists('SaeTOAuthV2') && !class_exists('SaeTClientV2') ){
	require_once('sinaoauth.php');
}
define( "SINA_CALLBACK" , 'sina_callback.php');
$URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pos = strrpos($URL, '/');
$newURL = substr($URL, 0, $pos+1) . SINA_CALLBACK;
$o = new SaeTOAuthV2( $_SESSION['SINA_APP_KEY'] , $_SESSION['SINA_APP_SECRETE'] );

if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = $newURL;
	try {
	$token = $o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
}
$expires_in = ceil(time()+$token['expires_in']);
echo '<script>opener.UYHasBindedSina=1; opener.SINA_ACCESS_TOKEN="' . $token['access_token'] . '"; opener.SINA_EXPIRES_iN="' . $expires_in . '";window.opener.document.getElementById("changeToConnected").style.display="none";window.opener.document.getElementById("connectWrapper").style.display="none";window.opener.document.getElementById("connectWrapperConnected").style.display="block";window.opener.document.getElementById("sina_expire").style.display="none";window.opener.document.getElementById("connectWrapper_expire").style.display="none";  opener.bindMasterSinaCallBack("'.$token['access_token'].'","'.$expires_in.'"); window.close();</script>';
?>
