<?php
define('UYAN_SOURCE','../wp-content/plugins/youyan-social-comment-system');
?>
<script>
var source_src = '<?php echo UYAN_SOURCE;?>';
var uyan_first = '<?php echo get_option('uyan_first')?>';
var domain = '<?php echo get_settings('home');?>';
var uyan_userid = '<?php echo get_settings('uyan_userid');?>';
var uyan_src = '<?php echo strpos(get_settings('uyan_src'),'uyan.cc')?>';
var uyan_first = '<?php echo get_settings('uyan_first');?>';
</script>
<script language="javascript" src="<?php echo plugin_dir_url(__FILE__);?>js/jquery-1.4.2.min.js"></script>
<script language="javascript" src="<?php echo plugin_dir_url(__FILE__);?>js/uyan_plugin.js"></script>
<script language="javascript" src="<?php echo plugin_dir_url(__FILE__);?>js/easyXDM.min.js"></script>
<script language="javascript" src="<?php echo plugin_dir_url(__FILE__);?>js/youyan_admin_view.js"></script>
<link href="<?php echo plugin_dir_url(__FILE__);?>css/admin_edit.css" rel="stylesheet" type="text/css" />
<link href="<?php echo plugin_dir_url(__FILE__);?>css/login.css" rel="stylesheet" type="text/css" />
<style type="text/css"> 
html, body {
	margin: 0px;
	padding: 0px;
	width: 100%;
	height: 100%;
}
iframe {
	width: 100%;
	height: 550px;
	border: 0px;
}
</style> 
