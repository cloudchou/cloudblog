<?php
session_start();
require_once('tsohuoauth.php');

$connection = new SohuOAuth($_SESSION['TSOHU_APP_KEY'], $_SESSION['TSOHU_APP_SECRETE'], $_SESSION['tsohu_oauth_token'], $_SESSION['tsohu_oauth_token_secret']);
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
$_SESSION['tsohu_access_token'] = $access_token;
echo '<script>opener.UYHasBindedSina=1; opener.TSOHU_ACCESS_TOKEN="' . $_SESSION['tsohu_access_token']['oauth_token'] . '"; opener.TSOHU_ACCESS_SECRETE="' . $_SESSION['tsohu_access_token']['oauth_token_secret'] . '"; window.opener.document.getElementById("changeToConnected").style.display="none";window.opener.document.getElementById("connectWrapperTSOHU").style.display="none";window.opener.document.getElementById("connectWrapperConnectedTSOHU").style.display="block";  opener.bindMasterTSOHUCallBack("'.$_SESSION['tsohu_access_token']['oauth_token'].'","'.$_SESSION['tsohu_access_token']['oauth_token_secret'].'"); window.close();</script>';
