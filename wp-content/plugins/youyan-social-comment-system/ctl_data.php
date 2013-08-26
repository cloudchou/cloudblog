<?php

/*
 * 
 * ctl_data.php
 * 接收友言处理评论的请求，对评论进行的 删除，审核等操作同步到WP
 */
header("Content-type:text/html;charset=utf8");
require '../../../wp-load.php';
require '../../../wp-admin/includes/admin.php';
do_action('admin_init');

//激活的插件
$active_plugins = get_option('active_plugins');
$active_list = array_values($active_plugins);
$uyan_actived = '';
foreach ($active_list as $val) {
	if (strstr($val, 'youyan-social-comment-system')) {
		$uyan_actived = 'active';
	}
}
// 如果友言处于激活状态
if (!$uyan_actived) {
	echo 1;
	exit;
}
$data = json_decode(stripslashes($_POST['data']), TRUE);
if (!$data) {
	echo 0;
	exit;
}

$wp_token = get_option('uyan_sync_token');
if (!$data['token']) {
	echo 2;
	exit;
}
if (!$wp_token) {
	echo 3;
	exit;
}
if ($data['token'] != $wp_token) {
	echo 4;
	exit;
}

if ($data['status'] == 0) {
	$sts = 1;
} else if ($data['status'] == 1) {
	$sts = 0;
} else if ($data['status'] == 2) {
	$sts = 'spam';
} else if ($data['status'] == 3) {
	$sts = 'trash';
}
$sql = "UPDATE $wpdb->comments SET comment_approved='$sts'  WHERE comment_ID = $data[cid]";
$result = $wpdb->query($sql);
echo $result;