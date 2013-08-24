<?php

    add_filter('protected_title_format', 'no_title_prefix');
    add_filter('private_title_format', 'no_title_prefix');
    function no_title_prefix( $prefix ) {
        return '%s';
    }

//WordPress文章关键字自动描文本内链
//连接数量
$match_num_from = 1;  //一篇文章中同一个关键字少于多少不描文本（这个直接填1就好了）
$match_num_to = 1; //一篇文章中同一个关键字最多出现多少次描文本（建议不超过2次）
//连接到WordPress的模块
add_filter('the_content','tag_link',1);
//按长度排序
function tag_sort($a, $b){
	if ( $a->name == $b->name ) return 0;
	return ( strlen($a->name) > strlen($b->name) ) ? -1 : 1;
}
//改变标签关键字
function tag_link($content){
global $match_num_from,$match_num_to;
	 $posttags = get_the_tags();
	 if ($posttags) {
		 usort($posttags, "tag_sort");
		 foreach($posttags as $tag) {
			 $link = get_tag_link($tag->term_id);
			 $keyword = $tag->name;
			 //连接代码
			 $cleankeyword = stripslashes($keyword);
			 $url = "<a href=\"$link\" title=\"".str_replace('%s',addcslashes($cleankeyword, '$'),__('View all posts in %s'))."\"";
			 $url .= ' target="_blank" class="tags"';
			 $url .= ">".addcslashes($cleankeyword, '$')."</a>";
			 $limit = rand($match_num_from,$match_num_to);

			//不连接的 代码
             $content = preg_replace( '|(<a[^>]+>)(.*)('.$ex_word.')(.*)(</a[^>]*>)|U'.$case, '$1$2%&&&&&%$4$5', $content);
			 $content = preg_replace( '|(<img)(.*?)('.$ex_word.')(.*?)(>)|U'.$case, '$1$2%&&&&&%$4$5', $content);

				$cleankeyword = preg_quote($cleankeyword,'\'');

					$regEx = '\'(?!((<.*?)|(<a.*?)))('. $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;

				$content = preg_replace($regEx,$url,$content,$limit);

	$content = str_replace( '%&&&&&%', stripslashes($ex_word), $content);

		 }
	 }
    return $content;
}


function tangstyle_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'tangstyle_page_menu_args' );

function tangstyle_widgets_init() {
	register_sidebar(array(
		'name' => '首页侧栏',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => '分类页侧栏',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => '内容页侧栏',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
}
add_action( 'widgets_init', 'tangstyle_widgets_init' );

if ( ! function_exists( 'tangstyle_content_nav' ) ) :

register_nav_menus(array('header-menu' => __( 'TangStyle导航菜单' ),));
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 200, 150 );

/** RSS Feed copyright */
function feed_copyright() {
        if(is_single() or is_feed()) {
        $custom_fields = get_post_custom_keys($post_id);
        $blogName= get_bloginfo('name');
        if (!in_array ('copyright', $custom_fields)) 
	{
        $content.= '<div class="feed-tip">';
        $content.= '<div>版权信息：原创 <a title="'.$blogName.'" href="'.get_bloginfo("url").'" target="_blank">'.$blogName.'</a></div>';
$content.='<div>本文标题：<a rel="bookmark" title="'.get_the_title().'" href="'.get_permalink().'" target="_blank">'.mb_strimwidth(get_the_title(), 0, 60,'...' ).'</a></div>';
                $content.= '<div>本文链接：</font><a title="'.get_the_title().'" href="'.get_permalink().'" target="_blank">'.get_permalink($post, true).'</a> <a title="'.$blogName.'" href=" '.get_bloginfo("url").'" target="_blank">'.$blogName.'</a></div>';
        
          $content.= '</div>';   

	}
        else{
        $custom = get_post_custom($post_id);
        $custom_value = $custom['copyright'];
        $custom_url=$custom['copyrighturl'] ;
        $content.= '<div class="feed-tip">';
        $content.= '<div>版权信息：转自 '.$custom_value[0].'  '.$custom_url[0].'</div>';
        $content.='<div>本文标题：<a rel="bookmark" title="'.get_the_title().'" href="'.get_permalink().'" target="_blank">'.mb_strimwidth(get_the_title(), 0, 60, '...').'</a></div>';
                $content.= '<div>本文链接：<a title="'.get_the_title($post_id).'" href="'.get_permalink($post, true).'" target="_blank">'.get_permalink($post, true).'</a></div>';
        
          $content.= '</div>';  }  
        }
        return $content;
}

//add_filter ('the_content', 'feed_copyright');



function single_fenye(){
     if(is_single() or is_feed()) {
         $args1=array(
	'before'=>'<div class="fenye">',
	'after'=>'</div>',
	'next_or_number'=>'next',
	'nextpagelink'=>'下一页',
	'previouspagelink'=>'上一页'
);

 $args2=array(
	'before'=>'<div class="singlePages">',
	'after'=>'</div>',
	'next_or_number'=>'number',
	'link_before'=>'<span>', 
    'link_after'=>'</span>'
);

    $fenye.=wp_link_pages($args1).wp_link_pages($args2);
     }
    return $fenye;
}

//设置个人资料相关选项
function my_profile( $contactmethods ) {
	$contactmethods['weibo_sina'] = '新浪微博';   //增加
	$contactmethods['weibo_tx'] = '腾讯微博';
      $contactmethods['renren'] = '人人';
       $contactmethods['qq'] = 'QQ空间';
	unset($contactmethods['aim']);   //删除
	unset($contactmethods['yim']);
	unset($contactmethods['jabber']);
	return $contactmethods;
}
add_filter('user_contactmethods','my_profile');

//评论模板
function tangstyle_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'tangstyle' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'tangstyle' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
    <li id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>">
    	<div class="avatar"><?php echo get_avatar( $comment, 40 );?></div>
    	<div class="comment">
        	<div class="comment_meta">
            <?php printf(__('<cite>%s</cite>'), get_comment_author_link()) ?>
            <span class="time"><?php printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></span>
            <span class="reply"><?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( '回复', 'tangstyle' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?></span>
            <?php edit_comment_link( __( '编辑', 'tangstyle' ), '<span class="edit_link">', '</span>' ); ?>
            </div>
            <?php comment_text(); ?>
            <?php if ( '0' == $comment->comment_approved ) : ?><p style="color:#F00;"><?php _e( '您的评论正在等待审核。', 'tangstyle' ); ?></p><?php endif; ?>
        </div>
    </div>
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

// 获得热评文章
function tangstyle_get_most_viewed($posts_num=10, $days=180){
    global $wpdb;
    $sql = "SELECT ID , post_title , comment_count FROM $wpdb->posts WHERE post_type = 'post' AND TO_DAYS(now()) - TO_DAYS(post_date) < $days AND ($wpdb->posts.`post_status` = 'publish' OR $wpdb->posts.`post_status` = 'inherit') ORDER BY comment_count DESC LIMIT 0 , $posts_num ";
    $posts = $wpdb->get_results($sql);
    $output = "";
    foreach ($posts as $post){
        $output .= "\n<li><a href= \"".get_permalink($post->ID)."\" title=\"".$post->post_title."\" >".$post->post_title."</a></li>";
    }
    echo $output;
}

//分页
function pagination($query_string){
global $posts_per_page, $paged;
$my_query = new WP_Query($query_string ."&posts_per_page=-1");
$total_posts = $my_query->post_count;
if(empty($paged))$paged = 1;
$prev = $paged - 1;							
$next = $paged + 1;	
$range = 5; // 分页数设置
$showitems = ($range * 2)+1;
$pages = ceil($total_posts/$posts_per_page);
if(1 != $pages){
	echo "<div class='pagination'>";
	echo ($paged > 2 && $paged+$range+1 > $pages && $showitems < $pages)? "<a href='".get_pagenum_link(1)."' class='fir_las'>最前</a>":"";
	echo ($paged > 1 && $showitems < $pages)? "<a href='".get_pagenum_link($prev)."' class='page_previous'>« 上一页</a>":"";		
	for ($i=1; $i <= $pages; $i++){
	if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
	echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>"; 
	}
	}
	echo ($paged < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($next)."' class='page_next'>下一页 »</a>" :"";
	echo ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($pages)."' class='fir_las'>最后</a>":"";
	echo "</div>\n";
	}
}

//彩色标签云
function colorCloud($text) {
$text = preg_replace_callback('|<a (.+?)>|i', 'colorCloudCallback', $text);
return $text;
}
function colorCloudCallback($matches) {
$text = $matches[1];
//$color = dechex(rand(0,16777215));
$colors=array('ffb900','f74e1e','00a4ef','7fba00');
$color=$colors[dechex(rand(0,3))];
$pattern = '/style=(\'|\")(.*)(\'|\")/i';
$text = preg_replace($pattern, "style=\"color:#{$color};$2;\"", $text);
return "<a $text>";
}
add_filter('wp_tag_cloud', 'colorCloud', 1);

//文章彩色标签
function getColorTags() {
$colors=array('ffb900','f74e1e','00a4ef','7fba00');

$posttags = get_the_tags();
if ($posttags) {
    $tagoutput = "";
  foreach($posttags as $tag) {
    $tag_link = get_tag_link($tag->term_id);
    $color=$colors[dechex(rand(0,3))];
    $tagoutput .= '<a class="colortags" style="background-color:#'.$color.'" href="'.$tag_link.'"  rel="tag">' . $tag->name . '</a>  ';
  }
  $tagoutput = substr($tagoutput,0,strlen($tagoutput)-2);
  echo $tagoutput;
}
}

//新窗口打开评论里的链接
function remove_comment_links() {
global $comment;
$url = get_comment_author_url();
$author = get_comment_author();
if ( empty( $url ) || 'http://' == $url )
$return = $author;
else
$return = "<a href='$url' rel='external nofollow' target='_blank'>$author</a>";
return $return;
}
add_filter('get_comment_author_link', 'remove_comment_links');
remove_filter('comment_text', 'make_clickable', 9);

//移除WordPress版本号
function wpbeginner_remove_version() {
return '';
}
add_filter('the_generator', 'wpbeginner_remove_version');

// 评论回应邮件通知
function comment_mail_notify($comment_id) {
  $admin_email = get_bloginfo ('admin_email'); // $admin_email 可改为你指定的 e-mail.
  $comment = get_comment($comment_id);
  $comment_author_email = trim($comment->comment_author_email);
  $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
  $to = $parent_id ? trim(get_comment($parent_id)->comment_author_email) : '';
  $spam_confirmed = $comment->comment_approved;
  if (($parent_id != '') && ($spam_confirmed != 'spam') && ($to != $admin_email) && ($comment_author_email == $admin_email)) {
    $wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])); // no-reply 可改为可用的 e-mail.
    $subject = '您在 [' . get_option("blogname") . '] 的评论有新的回复';
    $message = '
    <div style="background-color:#eef2fa; border:1px solid #d8e3e8; color:#111; padding:0 15px; -moz-border-radius:5px; -webkit-border-radius:5px; -khtml-border-radius:5px; border-radius:5px;">
      <p>' . trim(get_comment($parent_id)->comment_author) . ', 您好!</p>
      <p>您曾在 [' . get_option("blogname") . '] 的文章 《' . get_the_title($comment->comment_post_ID) . '》 上发表评论:<br />'
       . nl2br(get_comment($parent_id)->comment_content) . '</p>
      <p>' . trim($comment->comment_author) . ' 给您的回复如下:<br />'
       . nl2br($comment->comment_content) . '<br /></p>
      <p>您可以点击 <a href="' . htmlspecialchars(get_comment_link($parent_id)) . '">查看回复的完整內容</a></p>
      <p>欢迎再次光临 <a href="' . get_option('home') . '">' . get_option('blogname') . '</a></p>
      <p>(此邮件由系统自动发出,请勿直接回复.)</p>
    </div>';
	$message = convert_smilies($message);
    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    wp_mail( $to, $subject, $message, $headers );
    //echo 'mail to ', $to, '<br/> ' , $subject, $message; // for testing
  }
}
add_action('comment_post', 'comment_mail_notify');

?>
<?php
$themename = "janefun";
$shortname = "janefun";
$options = array (
	array("name" => "标题（Title)",
	"id" => $shortname."_title",
	"type" => "text",
	"std" => "网站标题",
	"explain" => "SEO设置<br>它将显示在网站首页的title标签里，必填项。"
	),
	array("name" => "描述（Description）",
	"id" => $shortname."_description",
	"type" => "textarea",
	"css" => "class='h60px'",
	"std" => "网站描述",
	"explain" => "SEO设置<br>它将显示在网站首页的meta标签的description属性里"
	),
	array("name" => "关键字（KeyWords）",
	"id" => $shortname."_keywords",
	"type" => "textarea",
	"css" => "class='h60px'",
	"std" => "网站关键字",
	"explain" => "SEO设置<br>多个关键字请以英文逗号隔开，它将显示在网站首页的meta标签的keywords属性里"
	),
	array("name" => "是否显示新浪微博",
    "id" => $shortname."_weibo",
    "type" => "select",
    "std" => "隐藏",
    "options" => array("隐藏", "显示")),
	array("name" => "新浪微博地址",
    "id" => $shortname."_weibo_url",
    "type" => "text",
    "std" => "http://weibo.com/2521961932",
	"explain" => "请输入您的新浪微博地址"),
	array("name" => "是否显示腾讯微博",
    "id" => $shortname."_tqq",
    "type" => "select",
    "std" => "隐藏",
    "options" => array("隐藏", "显示")),
	array("name" => "腾讯微博地址",
    "id" => $shortname."_tqq_url",
    "type" => "text",
    "std" => "http://t.qq.com/ph7364484",
	"explain" => "请输入您的腾讯微博地址"),
	array("name" => "版权年份",
	"id" => $shortname."_years",
	"std" => "2012",
	"type" => "text",
	"explain" => "它将显示在页面底部"
	),
	array("name" => "ICP备案号",
	"id" => $shortname."_icp",
	"type" => "text",
	"explain" => "页面底部可以显示 ICP 备案信息，如果网站已备案，在此输入您的备案号，它将显示在页面底部，如果没有请留空"
	),
	array("name" => "分享代码",
	"id" => $shortname."_share",
	"type" => "textarea",
	"css" => "class='h80px'",
	"explain" => "请在此处输入您的分享代码，来自第三方或者您自己的代码，它将显示在文章的结尾处，如果没有请留空<br>第三方分享工具主要有：百度分享、JiaThis、BShare 等等"
	),
	array("name" => "统计代码",
	"id" => $shortname."_tongji",
	"type" => "textarea",
	"css" => "class='h80px'",
	"explain" => "页面底部可以显示第三方统计<br>您可以放一个或者多个统计代码"
	),
);
function mytheme_add_admin() {
    global $themename, $shortname, $options;
    if ( $_GET['page'] == basename(__FILE__) ) {
        if ( 'save' == $_REQUEST['action'] ) {
            foreach ($options as $value) {
            update_option( $value['id'], $_REQUEST[ $value['id'] ] ); }
            foreach ($options as $value) {
            if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); } else { delete_option( $value['id'] ); } }
            header("Location: themes.php?page=functions.php&saved=true");
            die;
        } else if( 'reset' == $_REQUEST['action'] ) {
            foreach ($options as $value) {
                delete_option( $value['id'] );
                update_option( $value['id'], $value['std'] );
            }
            header("Location: themes.php?page=functions.php&reset=true");
            die;
        }
    }
    add_theme_page($themename." 设置", "$themename 设置", 'edit_themes', basename(__FILE__), 'mytheme_admin');
}
function mytheme_admin() {
    global $themename, $shortname, $options;
    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' 设置已保存。</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' 设置已重置。</strong></p></div>';
?>

<style type="text/css">
.wrap h2 {color:#09C;}
.themeadmin {border:1px dashed #999;margin-top:20px;width:420px;position:10px;}
.options {margin-top:20px;}
.options input,.options textarea {padding:2px;border:1px solid;border-color:#666 #CCC #CCC #666;background:#F9F9F9;color:#333;resize:none;width:400px;}
.options .h80px {height:80px;}
.options .h60px {height:60px;}
.options .setup {border-top:1px dotted #CCC;padding:10px 0 10px 10px;overflow:hidden;}
.options .setup h3 {font-size:14px;margin:0;padding:0;}
.options .setup .value {float:left;width:410px;}
.options .setup .explain {float:left;}
</style>
<div class="wrap">
	<h2><b><?php echo $themename; ?>主题设置</b></h2>
    <hr />
	<div>主题修改作者：<a href="http://www.penghui74.cn" target="_blank">简趣网</a> ¦ 当前版本：<a href="http://www.penghui74.cn/TangStyleModifythe" title="TangStyle灰色修改版" target="_blank">1.0</a> ¦ 主题介绍、使用帮助请访问：<a href="http://www.penghui74.cn/TangStyleModifythe" title="简趣网" target="_blank">http://www.penghui74.cn/TangStyleModifythe</a></div>
<form method="post">
<div class="options">
<?php foreach ($options as $value) {
	if ($value['type'] == "text") { ?>
	<div class="setup">
		<h3><?php echo $value['name']; ?></h3>
    	<div class="value"><input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id']) ); } else { echo $value['std']; } ?>" /></div>
    	<div class="explain"><?php echo $value['explain']; ?></div>
	</div>
	<?php } elseif ($value['type'] == "textarea") { ?>
	<div class="setup">
    	<h3><?php echo $value['name']; ?></h3>
        <div class="value"><textarea name="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" <?php echo $value['css']; ?> ><?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id']) ); } else { echo $value['std']; } ?></textarea></div>
        <div class="explain"><?php echo $value['explain']; ?></div>
    </div>
    <?php } elseif ($value['type'] == "select") { ?>
	<div class="setup">
    	<h3><?php echo $value['name']; ?></h3>
        <div class="value">
<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"><?php foreach ($value['options'] as $option) { ?>
		<option value="<?php echo $option;?>" <?php if (get_settings( $value['id'] ) == $option) { echo 'selected="selected"'; } ?>>
		<?php
		if ((empty($option) || $option == '' ) && isset($value['option'])) {
			echo $value['option'];
		} else {
			echo $option; 
		}?></option><?php } ?>
</select>
        </div>
        <div class="explain"><?php echo $value['explain']; ?></div>
    </div>
	<?php } ?>
<?php } ?>
</div>
<div class="submit">
<input style="font-size:12px !important;" name="save" type="submit" value="保存设置" class="button-primary" />
<input type="hidden" name="action" value="save" />
</div>
</form>

<form method="post">
	<div style="margin:50px 0;border-top:1px solid #F00;padding-top:10px;">
    <input style="font-size:12px !important;" name="reset" type="submit" value="还原默认设置" />
    <input type="hidden" name="action" value="reset" />
    </div>
</form>

</div>
<?php
}
add_action('admin_menu', 'mytheme_add_admin');
?>