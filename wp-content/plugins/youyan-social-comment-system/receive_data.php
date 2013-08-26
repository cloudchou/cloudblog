<?php
/*
 *
 * receive_data.php
 * 接收从友言发过来的评论数据
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
$_POST = json_decode(stripslashes($_POST['data']), true);
$uyan_sync_token = empty($_POST['token']) ? '' : $_POST['token'];
$wp_token = get_option('uyan_sync_token');

if (!$uyan_sync_token) {
	echo 2;
	exit;
}
if (!$wp_token) {
	echo 3;
	exit;
}
if ($uyan_sync_token != $wp_token) {
	echo 4;
	exit;
}

require dirname(__FILE__) . '/uyan_func.php';

// 根据WP短链接得到　文章　ID
$post_id = get_postid($_POST['su']);
//判断文章是否存在，存在则不导入评论  跳过本条记录   wordpress 的处理方式 
$post_exists = $wpdb->get_var($wpdb->prepare("SELECT comment_count FROM $wpdb->posts WHERE ID = %d LIMIT 1", $post_id));
if ($post_exists === null) {
	echo 5;
	exit;
}

//判断评论是否存在，存在则导入评论   跳过本条记录  wordpress 的处理方式 
$comments_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) AS cnt FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_date = %s AND comment_content = %s", $post_id, $_POST['time'], $_POST['cnt']));
if ($comments_exists) {
	echo 6;
	exit;
}

$author = array();
$status = 0;
// 下面的判断主要去拼接 用户的URL,不必修改
if ($_POST['status'] == 0) {
	// 针对于 wordpress 的状态，WP的正常状态标识为0
	$status = 1;
} elseif ($_POST['status'] == 1) {
	$status = 0;
} elseif ($_POST['status'] == 2) {
	$status = 'spam';
} elseif ($_POST['status'] == 3) {
	$status = 'trash';
}

$comment_fields = array(
	'comment_post_ID' => $post_id,
	'comment_date' => $_POST['time'],
	'comment_date_gmt' => $_POST['time'],
	'comment_content' => $_POST['cnt'],
	'comment_agent' => 'YouYan Social Comment System',
	'comment_approved' => $status,
	'comment_author' => $_POST['uname'],
	'comment_author_email' => $_POST['email'],
	'comment_author_url' => $_POST['ulink'],
	'comment_author_IP' => $_POST['ip'],
	'comment_parent' => $_POST['pid'] ? $_POST['pid'] : 0
);
$wpdb->insert($wpdb->prefix . "comments", $comment_fields);

$post_exists += 1;
$sql = "UPDATE $wpdb->posts SET comment_count=$post_exists  WHERE ID = $post_id";
$wpdb->query($sql);

echo json_encode(array('cid'=>$wpdb->insert_id, 'pid'=> $_POST['pid']));
?>
