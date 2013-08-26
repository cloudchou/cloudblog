<?php

/*
 * 
 * send_data.php 
 * 响应友言的请求，把评论数据发送到友言接收评论的接口
 */
header("Content-type:text/html;charset=utf8");
// wordpress 导入到友言
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

$uyan_token = empty($_POST['token']) ? '' : $_POST['token'];
$site_token = get_option('uyan_sync_token');
if (!$uyan_token) {
	echo 2;
	exit;
}
if (!$site_token) {
	echo 3;
	exit;
}
if ($uyan_token != $site_token) {
	echo 4;
	exit;
}

require dirname(__FILE__) . '/uyan_func.php';
$domain = $_SERVER['HTTP_HOST'];

$nowpage = isset($_POST['nowpage']) ? intval($_POST['nowpage']) : 0;
$pagesize = isset($_POST['pagesize']) ? intval($_POST['pagesize']) : 3;
$pagestart = $nowpage * $pagesize;
$comment = $wpdb->get_results('select comment_ID, comment_approved, comment_content, comment_post_ID, comment_author, comment_author_url, comment_author_email, comment_author_IP, comment_date, comment_parent from  ' . $wpdb->prefix . 'comments where comment_agent!="YouYan Social Comment System" order by comment_date ASC limit ' . $pagestart . ', ' . $pagesize, 'ARRAY_A');

foreach ($comment as $key => $comments) {
	$status = 0;
	if($comments['comment_approved'] == '0'){
		$status = 1;
	}elseif($comments['comment_approved'] == '1'){
		$status = 0;
	}elseif($comments['comment_approved'] == 'spam'){
		$status = 2;
	}elseif($comments['comment_approved'] == 'trash'){
		$status = 3;
	}
	
	$post_title = $wpdb->get_row('SELECT post_title from '.$wpdb->prefix .'posts WHERE ID='.$comments['comment_post_ID'],ARRAY_A);
	$url = get_bloginfo('url') . "/?p=" . $comments['comment_post_ID'];
    $send_comments[] = array(
        'du' => $domain,
        'cnt' => $comments['comment_content'],
        'time' => $comments['comment_date'],
        'uname' => $comments['comment_author'],
        'email' => $comments['comment_author_email'],
        'ulink' => $comments['comment_author_url'],
        'ip' => $comments['comment_author_IP'],
        'status' => $status,
        'pid' => $comments['comment_parent'],
        'cid' => $comments['comment_ID'],
        'su' => $url,
		'url' => $url,
        'fm' => 'wordpress',
    	'title' => $post_title['post_title'], 
        'nowpage' => $nowpage,
    );
}
$post_data = array(
    'comments' => $send_comments,
    'nowpage' => $nowpage,
    'token' => $site_token
);
echo json_encode($post_data);
?>