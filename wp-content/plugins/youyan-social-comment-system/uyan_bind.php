<?php

switch ($_POST['update_option']){
case 'bind':
	update_option('uyan_has_binded_sina', 1);
	update_option('UYAN_SINA_ACCESS_TOKEN', $_POST['SINA_ACCESS_TOKEN']);
	update_option('SINA_EXPIRES_iN', $_POST['SINA_EXPIRES_iN']);
	unset($_POST['update_option']);
	break;

case 'unbind':
	update_option('uyan_has_binded_sina', 0);
	update_option('UYAN_SINA_ACCESS_TOKEN', '');
	update_option('UYAN_SINA_ACCESS_SECRET', '');
	unset($_POST['update_option']);
	break;

case 'key':
    update_option('UYAN_SINA_APP_KEY', $_POST['SINA_APP_KEY']);
    update_option('UYAN_SINA_APP_SECRET', $_POST['SINA_APP_SECRETE']);
	break;

case 'bind_tencent':
  update_option('uyan_has_binded_tencent', 1);
  update_option('UYAN_TENCENT_ACCESS_TOKEN', $_POST['TENCENT_ACCESS_TOKEN']);
  update_option('UYAN_TENCENT_OAUTH_TOKEN_SECRET', $_POST['TENCENT_OAUTH_TOKEN_SECRET']);
  
  unset($_POST['update_option']);
  break;

case 'unbind_tencent':
	update_option('uyan_has_binded_tencent', 0);
	update_option('UYAN_TENCENT_ACCESS_TOKEN', '');
	update_option('UYAN_TENCENT_OAUTH_TOKEN_SECRET', '');
	unset($_POST['update_option']);
	break;

case 'key_tencent':
    update_option('UYAN_TENCENT_APP_KEY', $_POST['TENCENT_APP_KEY']);
    update_option('UYAN_TENCENT_APP_SECRET', $_POST['TENCENT_APP_SECRETE']);
	unset($_POST['update_option']);
	break;
	
case 'key_t163':
    update_option('UYAN_T163_APP_KEY', $_POST['T163_APP_KEY']);
    update_option('UYAN_T163_APP_SECRET', $_POST['T163_APP_SECRETE']);
	unset($_POST['update_option']);
	break;

case 'bind_t163':
	update_option('uyan_has_binded_t163', 1);
	update_option('UYAN_T163_ACCESS_TOKEN', $_POST['T163_ACCESS_TOKEN']);
	update_option('UYAN_T163_OAUTH_TOKEN_SECRET', $_POST['T163_OAUTH_TOKEN_SECRET']);
	unset($_POST['update_option']);
	break;

case 'unbind_t163':
	update_option('uyan_has_binded_t163', 0);
	update_option('UYAN_T163_ACCESS_TOKEN', '');
	update_option('UYAN_T163_OAUTH_TOKEN_SECRET', '');
	unset($_POST['update_option']);
	break;

case 'key_tsohu':
    update_option('UYAN_TSOHU_APP_KEY', $_POST['TSOHU_APP_KEY']);
    update_option('UYAN_TSOHU_APP_SECRET', $_POST['TSOHU_APP_SECRETE']);
	unset($_POST['update_option']);
	break;

case 'bind_tsohu':
	update_option('uyan_has_binded_tsohu', 1);
	update_option('UYAN_TSOHU_ACCESS_TOKEN', $_POST['TSOHU_ACCESS_TOKEN']);
	update_option('UYAN_TSOHU_OAUTH_TOKEN_SECRET', $_POST['TSOHU_OAUTH_TOKEN_SECRET']);
	unset($_POST['update_option']);
	break;

case 'unbind_tsohu':
	update_option('uyan_has_binded_tsohu', 0);
	update_option('UYAN_TSOHU_ACCESS_TOKEN', '');
	update_option('UYAN_TSOHU_OAUTH_TOKEN_SECRET', '');
	unset($_POST['update_option']);
	break;

case 'use_orig':
	update_option('uyan_use_orig', $_POST['uyan_use_orig']);
	unset($_POST['update_option']);
	break;
case 'time_update':
	update_option('uyan_sync_time', $_POST['uyan_sync_time']);
	update_option('uyan_sync_token', $_POST['uyan_sync_token']);
	break;
case 'user_passport':
	update_option('uyan_user_email', $_POST['uyan_user_email']);
	update_option('uyan_user_password', $_POST['uyan_user_password']);
	unset($_POST['update_option']);
	break;
	
case 'uyan_first':
	update_option('uyan_first', '1');
	break;

case 'default':
	update_option('uyan_userid', $_POST['UYUserID']);
	unset($_POST['UYUserID']);
	update_option('uyan_username', $_POST['UYUserName']);
	unset($_POST['UYUserName']);
	update_option('uyan_src', $_POST['sourceCode']);
	unset($_POST['sourceCode']);
	break;
}
?>
