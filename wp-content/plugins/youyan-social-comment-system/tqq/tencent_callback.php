<?php
session_start();
require_once 'Weibo.php';
OpenSDK_Tencent_Weibo::init($_SESSION['TENCENT_APP_KEY'], $_SESSION['TENCENT_APP_SECRETE']);
if(OpenSDK_Tencent_Weibo::getAccessToken($_GET['oauth_verifier'])){
	echo '<script>opener.UYHasBindedTencent=1;
	 opener.TENCENT_ACCESS_TOKEN="' . $_SESSION['TENCENT_ACCESS_TOKEN'] . '";
	 opener.TENCENT_OAUTH_TOKEN_SECRET="' . $_SESSION['TENCENT_OAUTH_TOKEN_SECRET'] . '";
	 window.opener.document.getElementById("changeToConnected").style.display="none";
	window.opener.document.getElementById("connectWrapperTencent").style.display="none";
	window.opener.document.getElementById("connectWrapperConnectedTencent").style.display="block"; 
	opener.bindMasterTencentCallBack(
	"'.$_SESSION['TENCENT_ACCESS_TOKEN'].'",
	"'.$_SESSION['TENCENT_OAUTH_TOKEN_SECRET'].'");
	 window.close();   </script>';
}