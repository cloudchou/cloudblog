<?php
session_start();
include_once('t163oauth.php');
$oauth_token = isset($_GET['oauth_token'])?$_GET['oauth_token']:'';
$oauth_verifier = isset($_GET['oauth_verifier'])?$_GET['oauth_verifier']:'';
$t163_keys = $_SESSION['t163_keys'];
$oauth = new OAuth($_SESSION['T163_APP_KEY'], $_SESSION['T163_APP_SECRETE'], $t163_keys['oauth_token'], $t163_keys['oauth_token_secret']);
$t163_access_token = $oauth->getAccessToken($oauth_token);
$_SESSION['t163_access_token'] =  $t163_access_token;
print_r($_SESSION);
echo '<script>opener.UYHasBindedT163=1; opener.T163_ACCESS_TOKEN="' . $t163_access_token['oauth_token'] . '"; opener.T163_ACCESS_SECRETE="' . $t163_access_token['oauth_token_secret'] . '"; window.opener.document.getElementById("changeToConnected").style.display="none";window.opener.document.getElementById("connectWrapperT163").style.display="none";window.opener.document.getElementById("connectWrapperConnectedT163").style.display="block";  opener.bindMasterT163CallBack("'.$t163_access_token['oauth_token'].'","'.$t163_access_token['oauth_token_secret'].'"); window.close();</script>';
?>
