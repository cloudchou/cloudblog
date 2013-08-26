<?PHP 
/*
Plugin Name: 友言
Plugin URI: http://www.uyan.cc
Description: 友言 - 国内最专业的社会化评论系统，它将替换您网站中默认的wordpress评论系统，一键同步评论留言信息到新浪微博，人人网，腾讯微薄，QQ空间，搜狐微博，网易微博，开心网，实时采集社交网络相关评论留言内容，从而帮助您的网站实现社交网络优化(SMO)，提高流量。
Version: 3.2.2
Author: JiathisTeam (kefu@jiathis.com)
Author URI: http://www.uyan.cc
 */

@date_default_timezone_set('PRC');
@session_start();
$domain = $_SERVER['HTTP_HOST'];
if(!class_exists('SaeTOAuthV2') && !class_exists('SaeTClientV2')){
	require_once('tsina/sinaoauth.php');
}

require_once 'tqq/Weibo.php';
require_once 't163/t163oauth.php';
require_once 'tsohu/tsohuoauth.php';

$uyan_has_binded_tencent = get_option('uyan_has_binded_tencent');
$uyan_has_binded_tsina = get_option('uyan_has_binded_sina');
$uyan_has_binded_t163 = get_option('uyan_has_binded_t163');
$uyan_has_binded_tsohu = get_option('uyan_has_binded_tsohu');

function uyan_wp_head(){
  if (is_page() or is_single()){
    echo "<link rel='shortlink' href='" . get_bloginfo('url') . '/?p='. get_the_ID() . "'/>";
  }
}

add_action('wp_head', 'uyan_wp_head');
add_action('wp_ajax_uyan_export', 'uyan_export');
add_action('admin_head', 'uyan_menu_admin_head');

function uyan_num_comments(){
  global $domain;
  $url = get_bloginfo('url');
  $url = substr($url, 7);
  $page_url = $url . '/?p=';
  //$page_url .= the_ID();
  $page = $domain . '_' . $page_url;
  
  $post_data = array(
    'page' => $page,
    'id' => get_the_ID()
  );

  $url = "http://www.uyan.cc/index.php/youyan_wp_content/get_num_comments";
  $ret = curl($post_data, $url);
  return $ret;
}

add_filter('plugin_action_links_youyan-social-comment-system/uyan_plugin.php','uyanActionLinks', 10, 2);
function uyanActionLinks($links, $file) {
		array_unshift($links, '<a href="' . admin_url('admin.php?page=uyan') . '">'.__('Settings').'</a>');
	    return $links;
}

//add_filter('get_comments_number', 'uyan_num_comments');

// 评论页提示
add_action('admin_head-edit-comments.php', 'uyan_comment_notice', 10);
function uyan_comment_notice(){
	echo '<div class="updated" style="border:1px solid #E6DB55; background-color:#FFFBCC;">'
			. '<p>友言正在为您提供专业的社会化评论服务，Wordpress自带评论与友言评论将实时同步到本地数据库中。</p>'
			. '<p>评论管理请在友言中进行操作，您在本页对评论进行的任何操作，将不会同步到友言数据库中，但是在友言评论管理中进行的操作将会同步到Wordpress中。</p>'
			. '<p><a href="'.get_settings('home').'/wp-admin/admin.php?page=uyan">点击这里到友言进行管理</a>'
			. '</p></div>';
}
// 绑定新浪微博，腾讯微博，入库操作。
function uyan_bind(){
  include('uyan_bind.php');
}
// 管理页面 （初始登录页面）
function uyan_admin() {
  include('uyan_plugin_admin.php');
}
// 评论框设置
function uyan_db_sync(){
  include('uyan_db_sync.php');
}
// 绑定(两大微博) 输入信息页面
function uyan_bind_sns(){
  include('uyan_bind_sns.php');
}

// 添加一级菜单及子菜单
function uyan_add_pages() {
add_object_page(
				'友言评论',
				'友言评论',
				'manage_options',
				'uyan',
				'uyan_admin',
				plugin_dir_url(__FILE__) . 'images/uyan_plug_log.png' 
			);
			add_submenu_page(
			'uyan',      // 一级菜单名称
			'安装设置', //页面标题
			'安装设置',  // 选项名
			'manage_options',
			'uyan_setting', // 访问的页面
			'uyan_admin' // 绑定的函数
			);
			add_submenu_page(
		         'uyan',      // 一级菜单名称
		         '统计分析', //页面标题
		         '统计分析',  // 选项名
		         'manage_options',
		         'uyan_analysis', // 访问的页面
		         'uyan_admin' // 绑定的函数
		    );
			add_submenu_page(
		         'uyan',    // 父级菜单名称
		         '绑定SNS', //页面标题
		         '绑定SNS', // 选项名
		         'manage_options', // 权限
		         'uyan_bind_sns',// 访问的页面
		         'uyan_bind_sns' // 绑定的函数
		    );
			add_submenu_page(
		         'uyan',     // 父级菜单名称
		         '数据双向同步', //页面标题
		         '数据双向同步', // 选项名
		         'manage_options', // 权限
		         'uyan_db_sync',// 访问的页面
		         'uyan_db_sync' // 绑定的函数
		    );
			add_submenu_page(
		         'uyan',     // 父级菜单名称
		         '', //页面标题
		         '', // 选项名
		         'manage_options', // 权限
		         'uyan_bind',// 访问的页面
		         'uyan_bind' // 绑定的函数
		    );
			// 下面三个子菜单，不会显示，在这里定义，是为了绑定，留言导入，导出  能访问到定义的PHP页面。
}

add_action('admin_menu', 'uyan_add_pages', 10);

// 隐藏上面说的三个选项
function uyan_menu_admin_head() {
?>
  <script type="text/javascript">
  jQuery(function($) {
    // fix menu
    var mc = $('#toplevel_page_uyan');
		mc.find("li").find("a:empty()").hide();
	});
  </script>
<?php
}

// 原有评论是否保留
function uyan_comment($post_ID){
	if(comments_open()){
		return  dirname(__FILE__) . '/comment.php';
	}
}


add_filter('comments_template', 'uyan_comment');


//provided to post article
if (1==1) { // 是否开启微博同步功能
  add_action('admin_menu', 'uyan_wp_connect_add_sidebox');
  add_action('publish_post', 'uyan_wp_connect_publish', 1);
  add_action('publish_page', 'uyan_wp_connect_publish', 1);
}

//prepared for publish
function uyan_wp_replace($str) {
  $a = array('&#160;', '&#038;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt', '&ldquo;', '&rdquo;', '&nbsp;', 'Posted by Wordmobi');
  $b = array(' ', '&', '-', '\'', '\'', '"', '"', '&', '<', '>', '"', '"', ' ', '');
  $str = str_replace($a, $b, strip_tags($str));
  $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
  $str = preg_replace($reg_exUrl, '', $str);
  return trim($str);
}

//get photo
function uyan_wp_multi_media_url($content) {
  preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/isU', $content, $image);
  $p_sum = count($image[1]);
  if ($p_sum > 0) {
    $url = array("image", $image[1][0]);
  } 
  return $url;
}

// 发布
function uyan_wp_connect_publish($post_ID){
	
	global $uyan_has_binded_tencent,$uyan_has_binded_tsina,$uyan_has_binded_t163,$uyan_has_binded_tsohu;
	
	$title = uyan_wp_replace(get_the_title($post_ID));
	$postlink = get_permalink($post_ID);
	$shortlink = get_bloginfo('url') . "/?p=" . $post_ID;
	$thePost = get_post($post_ID);	
	$content = $thePost -> post_content;
	$excerpt = $thePost -> post_excerpt;
	$post_author_ID = $thePost -> post_author;
	$post_date = strtotime($thePost -> post_date);
	$post_modified = strtotime($thePost -> post_modified);
	$post_content = uyan_wp_replace($content);
	// 匹配视频、图片
	if(!$wptm_options['disable_pic'])
		$pic = uyan_wp_multi_media_url($content);
	// 是否有摘要
	if ($excerpt) {
		$post_content = uyan_wp_replace($excerpt);
	}
	
	if($_POST['publish_no_sync_sina'] and $_POST['publish_no_sync_tencent'] and $_POST['publish_no_sync_t163']){
		return;
	}
	
	//print_r("start sync\n");
	$title = trim('#' . $title . '#'. ' - '. $post_content);
	//$title = trim($title); 
	$title = preg_replace("'([\r\n])[\s]+'", " ", $title);
	$page_part = explode('http://',$shortlink);
	
	$page = $_SERVER['HTTP_HOST'].'_'.$page_part[1];
	// 同步到新浪
	if(!$_POST['publish_no_sync_sina'] && $uyan_has_binded_tsina){
		$at_str = trim($_POST['publish_and_at_sina']);
		if($at_str == '')
			$ats = array();
		else
			$ats = preg_split("/[\s,]+/", trim($at_str));
		
		$trace_link ="http://s.uyan.cc/?u=" .   urlencode( $postlink ) . "&t=tsina";
		if(!$_POST['publish_update_no_sync_sina']){
			sychronize_post_to_sina($title, $page, $trace_link, $pic, $ats);
			unset($_POST['publish_no_sync_sina']);
		}
	}
	// 同步到腾讯
	if(!$_POST['publish_no_sync_tencent'] && $uyan_has_binded_tencent){
		$at_str = trim($_POST['publish_and_at_tencent']);
		if($at_str == '')
			$ats = array();
		else
			$ats = preg_split("/[\s,]+/", trim($at_str));
		
		//$trace_link = urlencode("http://s.uyan.cc/?u=" . urlencode( $postlink ) . "&t=tqq");
		$trace_link = "http://s.uyan.cc/?u=" . urlencode( $postlink ) . "&t=tqq";
		if(!$_POST['publish_update_no_sync_tencent']){
		  synchronize_post_to_tencent($title, $page, $trace_link, $pic, $ats);
		  unset($_POST['publish_no_sync_tencent']);
		}
	}
	// 同步到网易
	if(!$_POST['publish_no_sync_t163'] && $uyan_has_binded_t163){
		$at_str = trim($_POST['publish_and_at_t163']);
		if($at_str == '')
		  $ats = array();
		else
		  $ats = preg_split("/[\s,]+/", trim($at_str));
	
		// $trace_link = urlencode("http://s.uyan.cc/?u=" . urlencode($postlink). "&t=t163");
		$trace_link = "http://s.uyan.cc/?u=" . urlencode($postlink). "&t=t163";
		if(!$_POST['publish_update_no_sync_t163']){
			
		  synchronize_post_to_t163($title, $page, $trace_link, $pic, $ats);
		  unset($_POST['publish_no_sync_t163']);
		}
	}
		// 同步到搜狐
	if(!$_POST['publish_no_sync_tsohu'] && $uyan_has_binded_tsohu){
		$at_str = trim($_POST['publish_and_at_tsohu']);
		if($at_str == '')
		  $ats = array();
		else
		  $ats = preg_split("/[\s,]+/", trim($at_str));
	
		$trace_link = urlencode("http://s.uyan.cc/?u=" . urlencode($postlink). "&t=tsohu");
		if(!$_POST['publish_update_no_sync_tsohu']){
		  synchronize_post_to_tsohu($title, $page, $trace_link, $pic, $ats);
		  unset($_POST['publish_no_sync_tsohu']);
		}
	}

}

function correctNumWords($str){
  return (mb_strlen($str, 'utf8') + strlen($str))/2;
}

function getSinaShortURL($longURL){

  $post_data = '';
  $url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=507593302&url_long=' . $longURL;
  $short_url = curl($post_data, $url);
  return $short_url;
}

function synchronize_post_to_tencent($title, $page, $trace_link, $pic, &$ats) {
	global $uyan_has_binded_tencent;
  if($ret == 'yes')
    return;

  // $short_url = getSinaShortURL($trace_link);
  $short_url = $trace_link;

  $at_str = '';
  if(count($ats) != 0){
    $at_str = ' by ';
    foreach($ats as $at){
      $at_str .= '@' . $at;
    }
  }

  $content = $title . ' ' . $short_url . $at_str;
  if(correctNumWords($content) > 280){
    $content = mb_substr($title, 0, 138- strlen($short_url)/2 - mb_strlen($at_str, 'utf8'), 'utf8') . '... '  . $short_url . $at_str;
  }

  if($pic != null){
  	$api_name = 't/add_pic';
  	$pic_arr = array('pic'=>array('type' => 'image/jpg','name' => 'pic.jpg','data' => file_get_contents($pic[1])));
  }else{
  	$api_name = 't/add';
    $pic_arr = null;
  }
  if($uyan_has_binded_tencent == 1){
		OpenSDK_Tencent_Weibo::init(get_option('UYAN_TENCENT_APP_KEY'), get_option('UYAN_TENCENT_APP_SECRET'));
		$_SESSION = get_tencent_app_info();
		$add_result = OpenSDK_Tencent_Weibo::call($api_name,array('content' => $content),'post',$pic_arr);
  }

  $post_data = array(
  		'page' => $page,
  		'mid' => $add_result['data']['id']
  );
  $url = "http://www.uyan.cc/youyan_wp_content/post_wordpress_tencent";
  curl($post_data, $url);
}

function sychronize_post_to_sina($title, $page, $trace_link, $pic, &$ats) {
	global $uyan_has_binded_tsina;
  $at_str = '';
  if(count($ats) != 0){
    $at_str = ' by ';
    foreach($ats as $at){
      $at_str .= '@' . $at;
    }
  }

  // $short_url = getSinaShortURL($trace_link);
  $short_url = $trace_link;
  $content = $title . ' ' . $short_url . $at_str;
  if(correctNumWords($content) > 280){
    $content = mb_substr($title, 0, 138- strlen($short_url)/2 - mb_strlen($at_str, 'utf8'), 'utf8') . '... '  . $short_url . $at_str;
  }
  if($uyan_has_binded_tsina == 1){
    $arr = get_sina_app_info();
    $c = new SaeTClientV2($arr['UYAN_SINA_APP_KEY'], $arr['UYAN_SINA_APP_SECRET'], $arr['UYAN_SINA_ACCESS_TOKEN']);
	if($pic[1] == ''){
	    $ret = $c->update($content);
    }else{
	    $ret = $c->upload($content, $pic[1]);
	 }
  }
 $post_data = array(
    'page' => $page,
    'sinaurl' => $short_url,
    'mid' => $ret['mid']
  );
  $url = "http://www.uyan.cc/youyan_wp_content/post_wordpress_sina";
  curl($post_data, $url);
}

function synchronize_post_to_t163($title, $page, $trace_link, $pic, &$ats) {
	global $uyan_has_binded_t163;
  if($ret == 'yes')
    return;

  $short_url = $trace_link;

  $at_str = '';
  if(count($ats) != 0){
    $at_str = ' by ';
    foreach($ats as $at){
      $at_str .= '@' . $at;
    }
  }

  $content = $title . ' ' . $short_url . $at_str;
  if(correctNumWords($content) > 326){
    $content = mb_substr($title, 0, 138- strlen($short_url)/2 - mb_strlen($at_str, 'utf8'), 'utf8') . '... '  . $short_url . $at_str;
  }

	if($uyan_has_binded_t163 == 1){
		$arr = get_t163_app_info();
		$tblog = new TBlog($arr['UYAN_T163_APP_KEY'],$arr['UYAN_T163_APP_SECRET'],$arr['UYAN_T163_ACCESS_TOKEN'],$arr['UYAN_T163_OAUTH_TOKEN_SECRET']);
		if($pic != null){
			$add_result = $tblog->upload($content, $pic[1]); 
		}else{
			$add_result = $tblog->update($content);
		}
	}
	
 $post_data = array(
    'page' => $page,
    'mid' => $add_result['id'],
  );
  $url = "http://www.uyan.cc/youyan_wp_content/post_wordpress_t163";
  curl($post_data, $url);
}

function synchronize_post_to_tsohu($title, $page, $trace_link, $pic, &$ats) {
	global $uyan_has_binded_tsohu;
  if($ret == 'yes')
    return;

  $short_url = $trace_link;

  $at_str = '';
  if(count($ats) != 0){
    $at_str = ' by ';
    foreach($ats as $at){
      $at_str .= '@' . $at;
    }
  }

  $content = $title . ' ' . $short_url . $at_str;
  if(correctNumWords($content) > 280){
    $content = mb_substr($title, 0, 138- strlen($short_url)/2 - mb_strlen($at_str, 'utf8'), 'utf8') . '... '  . $short_url . $at_str;
  }

	if($uyan_has_binded_tsohu == 1){
		$arr = get_tsohu_app_info();
		@$sohu = weibo_api_sohu::instance();
		@$sohu->init($arr['UYAN_TSOHU_APP_KEY'],$arr['UYAN_TSOHU_APP_SECRET'],$arr['UYAN_TSOHU_ACCESS_TOKEN'],$arr['UYAN_TSOHU_OAUTH_TOKEN_SECRET']);
		if($pic != null){
			$add_result  = $sohu->addPic(array('content' => $content, 'pic' => $pic[1]));
		}else{
			$add_result = $sohu->add(array('content' => $content));
		}
	}
	
 $post_data = array(
    'page' => $page,
    'mid' => $add_result['id']
  );
  $url = "http://www.uyan.cc/youyan_wp_content/post_wordpress_tsohu";
  curl($post_data, $url);
}

function uyan_wp_connect_add_sidebox() {
	global $uyan_has_binded_tencent,$uyan_has_binded_tsina,$uyan_has_binded_t163,$uyan_has_binded_tsohu;
  if($uyan_has_binded_tsina == 1 or $uyan_has_binded_tencent == 1 or $uyan_has_binded_t163 == 1 or $uyan_has_binded_tsohu == 1 ){
    if (function_exists('add_meta_box')) {
      add_meta_box('uyan_wp-connect-sidebox', '"友言"文章微博同步设置 [只对本页面有效]', 'uyan_wp_connect_sidebox', 'post', 'side', 'high');
      add_meta_box('uyan_wp-connect-sidebox', '"友言"文章微博同步设置 [只对本页面有效]', 'uyan_wp_connect_sidebox', 'page', 'side', 'high');
    } 
  }
}

function uyan_wp_connect_sidebox() {
	global $uyan_has_binded_tencent,$uyan_has_binded_tsina,$uyan_has_binded_t163,$uyan_has_binded_tsohu;
  global $post,$wpdb;
  if ($post -> post_status != 'publish') {
    if($uyan_has_binded_tsina == 1){
		if(get_option('SINA_EXPIRES_iN') && time() > get_option('SINA_EXPIRES_iN')){
			echo '<p><label><input type="checkbox" name="publish_no_sync_sina" value="1" checked="checked" /> 不同步到新浪微博[<span style="color:red;">授权已过期</span>]</label></p>';
		}else{
		   echo '<p><label><input type="checkbox" name="publish_no_sync_sina" value="1" /> 不同步到新浪微博[已与站长账号绑定]</label></p>';
		   echo '<p><label>新浪微博发布时@<input type="text" name="publish_and_at_sina" style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
		}
    }
	if($uyan_has_binded_t163 == 1){
      echo '<p><label><input type="checkbox" name="publish_no_sync_t163" value="1" /> 不同步到网易微博[已与站长账号绑定]</label></p>';
      echo '<p><label>网易微博发布时@<input type="text" name="publish_and_at_t163" style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }
    if($uyan_has_binded_tencent == 1){
      echo '<p><label><input type="checkbox" name="publish_no_sync_tencent" value="1" /> 不同步到腾讯微博[已与站长账号绑定]</label></p>';
      echo '<p><label>腾讯微博发布时@<input type="text" name="publish_and_at_tencent" style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }
	if($uyan_has_binded_tsohu == 1){
      echo '<p><label><input type="checkbox" name="publish_no_sync_tsohu" value="1" /> 不同步到搜狐微博[已与站长账号绑定]</label></p>';
      echo '<p><label>搜狐微博发布时@<input type="text" name="publish_and_at_tsohu" style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }

  }
  else {
    if($uyan_has_binded_tsina == 1){
		if(get_option('SINA_EXPIRES_iN') && time() > get_option('SINA_EXPIRES_iN')){
			echo '<p><label><input type="checkbox" name="publish_no_sync_sina" value="1" checked="checked" /> 不同步到新浪微博[<span style="color:red;">授权已过期</span>]</label></p>';
		}else{
      		echo '<p><label><input type="checkbox" name="publish_update_no_sync_sina" value="1" checked="checked" /> 文章更新不同步到新浪微博[已绑定站长账号]</label></p>';
      		echo '<p><label>新浪微博发布时@<input type="text" name="publish_and_at_sina"  style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
      	} 
    }
    if($uyan_has_binded_tencent == 1){
      echo '<p><label><input type="checkbox" name="publish_update_no_sync_tencent" value="1" checked="checked"/> 文章更新不同步到腾讯微博[已绑定站长账号]</label></p>';
      echo '<p><label>腾讯微博发布时@<input type="text" name="publish_and_at_tencent"  style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }
	if($uyan_has_binded_t163 == 1){
      echo '<p><label><input type="checkbox" name="publish_update_no_sync_t163" value="1"  checked="checked" /> 文章更新不同步到网易微博[已绑定站长账号]</label></p>';
      echo '<p><label>网易微博发布时@<input type="text" name="publish_and_at_t163"  style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }
	if($uyan_has_binded_tsohu == 1){
      echo '<p><label><input type="checkbox" name="publish_update_no_sync_tsohu" value="1"  checked="checked"/> 文章更新不同步到搜狐微博[已绑定站长账号]</label></p>';
      echo '<p><label>搜狐微博发布时@<input type="text" name="publish_and_at_tsohu"   style="width:100px;padding-left:5px;margin-left:5px;"/></label></p>';
    }

  }
  echo '<p><label style="font-size:12px;color:#aaa;">(@多位作者时用空格分开)</label></p>';
}


function get_tencent_app_info(){
	$arr['TENCENT_APP_KEY'] = trim(get_option('UYAN_TENCENT_APP_KEY'));
	$arr['TENCENT_APP_SECRET'] = trim(get_option('UYAN_TENCENT_APP_SECRET'));
	$arr['TENCENT_ACCESS_TOKEN'] = trim(get_option('UYAN_TENCENT_ACCESS_TOKEN'));
	$arr['TENCENT_OAUTH_TOKEN_SECRET'] = trim(get_option('UYAN_TENCENT_OAUTH_TOKEN_SECRET'));
	return $arr;
}


function get_sina_app_info(){
  $arr['UYAN_SINA_APP_KEY'] = trim(get_option('UYAN_SINA_APP_KEY'));
  $arr['UYAN_SINA_APP_SECRET'] = trim(get_option('UYAN_SINA_APP_SECRET'));
  $arr['UYAN_SINA_ACCESS_TOKEN'] = trim(get_option('UYAN_SINA_ACCESS_TOKEN'));
  return $arr;
}

function get_t163_app_info(){
  $arr['UYAN_T163_APP_KEY'] = trim(get_option('UYAN_T163_APP_KEY'));
  $arr['UYAN_T163_APP_SECRET'] = trim(get_option('UYAN_T163_APP_SECRET'));
  $arr['UYAN_T163_ACCESS_TOKEN'] = trim(get_option('UYAN_T163_ACCESS_TOKEN'));
  $arr['UYAN_T163_OAUTH_TOKEN_SECRET'] = trim(get_option('UYAN_T163_OAUTH_TOKEN_SECRET'));
  return $arr;
}
function get_tsohu_app_info(){
  $arr['UYAN_TSOHU_APP_KEY'] = trim(get_option('UYAN_TSOHU_APP_KEY'));
  $arr['UYAN_TSOHU_APP_SECRET'] = trim(get_option('UYAN_TSOHU_APP_SECRET'));
  $arr['UYAN_TSOHU_ACCESS_TOKEN'] = trim(get_option('UYAN_TSOHU_ACCESS_TOKEN'));
  $arr['UYAN_TSOHU_OAUTH_TOKEN_SECRET'] = trim(get_option('UYAN_TSOHU_OAUTH_TOKEN_SECRET'));
  return $arr;
}

function curl($post_data,$url,$type=''){

	if(extension_loaded('curl')){
		if(is_array($post_data)){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			//添加变量
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$ret = curl_exec($ch);
		}else{
			@$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			$output_json = json_decode($output);
			$short_url =  $output_json[0]->url_short;
			return $short_url;
		}
	}else{
		$query = '';
		if(is_array($post_data)){
			$query = http_build_query($post_data);
		}
		$content = '';
		//获取主机地址
		$array = explode("/", $url);
		if($array[0] != "http:"){
			return false;
		}
		$host = $array[2];
		//构造页面访问请求
		$post = "POST $url HTTP/1.1\r\n";
		$post.= "Host: $host\r\n";
		$post.= "Content-type: application/x-www-form-urlencoded\r\n";
		$post.= "Content-length: ".strlen($query)."\r\n";
		$post.= "Connection: close\r\n\r\n";
		$post.= $query;
		//使用fsockopen连接页面并将请求信息提交
		$fp = fsockopen($host,80);
		$result = fwrite($fp, $post);
		
		while(!feof($fp)){
			// $content .= fgets($fp,4096); // 所有写到里面的值都泛返回
			$content .= fgets($fp,4096); // 只写入执行页面返回的结果
		}
		//关闭服务器连接并返回页面的全部数据
		fclose($fp);
		$arr = array_filter(explode("\r\n", $content));
		$content = end($arr);
		$content = json_decode($content);
		if($post_data == ''){
			$short_url =  $content[0]->url_short;
			return $short_url;
		}
	}
	return $ret;
}
?>
