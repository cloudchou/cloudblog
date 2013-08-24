<?php
  /*
  Plugin Name: WP flash img show
  Plugin URI: http://xwjie.com/post/wp-flash-img-show.html
  Version: 1.4
  Author: Tojary
  Author URI: http://xwjie.com
  Description: wp-flash-img-show is a FLASH Image Slide plugin for WordPress. You can show your articles , photo,goods,product and other ad. or introduction.Just enjoy it.  [Chinese ver.]: 这是一个flash图片幻灯片轮换wordpress插件，你可以利用它展示热门日志、艺术图片、商品、产品。通过改变用户设置，还可以用来做图片广告、宣传标语等等。请发挥创意。

  */
?>
<?php
  /*
	V1.4 Build 2011-08-08
  */
?>
<?php
require dirname( __FILE__ ) . '/inc.extend.php'; // 独立的扩展

require dirname( __FILE__ ) . '/inc.function.php'; // wfis核心函数

// config name 
if ( strlen($_POST['config']) == 0 )
{
	$config_name = "default";	
}
else
{
	$config_name = $_POST['config'];
}



// Language 
	$dr_locale = get_locale();
	$dr_mofile = dirname(__FILE__) . "/languages/wp-flash-img-show-$dr_locale.mo";
	load_textdomain('wp-flash-img-show', $dr_mofile);

	
//转换旧数据	
if ( strlen(get_option("wp_flash_img_show_pic_number")) != 0 )
	{
	wfis_translate_old_ver() ;
	}

//第一次使用 The First Time
if(!get_option("wp_flash_img_show")){	//如果是第一次使用，把沙发排名数据写入数据库
 new_config_initialize("default");  //DEBUG  

}


// 恢复默认设置
if($_POST['set_wp_flash_img_show_default_option']){ 
	
 $config_name = $_POST['config']; //获得配置名
 new_config_initialize($config_name);//DEBUG  

}

	
//设置页面主函数
function wp_flash_img_show_options()
{
	
	  
	
	
	//获得配置名
	if ( strlen($_POST['config']) == 0 )
		{
			$config_name = "default";	
		}
		else
		{
			$config_name = $_POST['config'];
		}

	if($_POST['del_config'])  //删除Config
	{ 
		$message='Delete config : '.$config_name;
		if ( $config_name !=  "default" ) {
		$dele_array = get_option("wp_flash_img_show") ; 
		unset($dele_array[$config_name]); // 删除config元素
		update_option("wp_flash_img_show",$dele_array);
		
			// file path
			$wp_flash_img_filename = "wp-flash-img-show" ;
			if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
			{
			global $blog_id;
			$wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
			}
			if ($config_name == "default" ) 
			{ $config_name = ".xml"; }
			else
			{ $config_name = "-".$config_name.".xml" ; }
			$wp_flash_img_xml_path = dirname(__FILE__) ."/". $wp_flash_img_filename . $config_name ;
 
		if( is_file( $wp_flash_img_xml_path ) )
		{
		unlink($wp_flash_img_xml_path) ;
		}
		
		$config_name = "default";
		}
		else
		{$message="Can not Delete config : ".$config_name;}
	}

	
	if($_POST['debug_mode']){
		$wfis_oo_array = get_option("wp_flash_img_show_info") ;
		$wfis_oo_array['debug_mode'] = $_POST['debug_mode'];
		update_option("wp_flash_img_show_info",$wfis_oo_array);
		if($wfis_oo_array['debug_mode'] =='true'){
			$message= '<b style="color:red">'. __('Working in Debug mode. ','wp-flash-img-show') . sprintf(__('Browse the %s home page %s now, you will get some tips.','wp-flash-img-show') ,'<a href="'.home_url().'" target="_blank">' ,'</a>' ).'</b>';
		}
	}
	
	if($_POST['change_config'])  //编辑 Config
	{ 
		$message='Edit config : '.$config_name ;
	}

	if($_POST['create_config'])  //新建 Config
	{ 
		$message='Create a New Config : '.$config_name ;
	}
 
	if($_POST['update_wp_flash_img_show_option'])  //开始保存数据到数据库
	{ 
	
	$message='Settings saved . Enjoy it !';

	// 全局设置数组
	$wfis_array = get_option("wp_flash_img_show") ;
 
	// Save Munber
	$wfis_array[$config_name]["pic_number"] =  $_POST['wfis_option']['pic_number']; //save number	
	$wfis_array[$config_name]["autogetimg"] = $_POST['wfis_option']['autogetimg']; //save how to get img 
	$wfis_array[$config_name]["autogetimg_page_id"] = $_POST['wfis_option']['autogetimg_page_id']; //   页面 ID
	$wfis_array[$config_name]["autogetimg_page_descend"] = $_POST['wfis_option']['autogetimg_page_descend']; //页面（排序）
	$wfis_array[$config_name]["autogetimg_page_shortcode"] = $_POST['wfis_option']['autogetimg_page_shortcode']; //页面(转换shortcode)
	$wfis_array[$config_name]["autogetimg_post_shortcode"] = $_POST['wfis_option']['autogetimg_post_shortcode']; //文章(转换shortcode)

// if not auto get img
if ($wfis_array[$config_name]["autogetimg"] == "" )
{	
	// Save IMG   
	$store_pic_array = array();
	for ($i=1; $i<= $wfis_array[$config_name]["pic_number"] ; $i++) {
		
		$each_pic_array=array();
		$each_pic_array["url"]=$_POST['wfis_option'][$i.'_url'];
		$each_pic_array["link"]=$_POST['wfis_option'][$i.'_link'];
		$each_pic_array["description"]=$_POST['wfis_option'][$i.'_description'];

		$store_pic_array[$i]= $each_pic_array;

	}
		$wfis_array[$config_name]["pic"] =  $store_pic_array ; 
}  


	//save option 			
	$store_option_array = array();
	foreach( wfis_def_options(true, true) as $o_item=>$def_value){
		$itemnames = "wp_flash_img_show_".$o_item ;
		$store_option_array[$itemnames] = $_POST["wfis_option"][$o_item];
	}
		
	$wfis_array[$config_name]["option"] =  $store_option_array ;
	  // $wfis_array = array(); //debug 清空所有设置
	 update_option("wp_flash_img_show",$wfis_array);
 

	wp_flash_img_show_save_to_xml($config_name);	//更新 XML
	
	
if ($wfis_array[$config_name]["autogetimg"] == "frompost" )
{
// Auto Get IMG
$message = get_img_from_post($config_name) ." , ". $message  ;
}	
	
if ($wfis_array[$config_name]["autogetimg"] == "frompage" )
{
// Auto Get IMG
$message = get_img_from_page($config_name) ." , ". $message  ;
}		

	} //保存完毕

 
		if ( $message ==  'Save failed' ){
		echo '<div class="error"><strong><p>'.$message.'</p></strong></div>';
		}
		elseif ( strlen($message) >  0 ) {
		echo '<div class="updated"><strong><p>'.$message.'</p></strong></div>';
		};


// ===================================================	
?>
<div class="wrap">
<?php
 // echo  wfis_file_ver( 'wp-flash-img-show.xml' );
 // echo '<pre>';
  // print_r(wfis_def_options_store());

$thisurl = get_this_url();
  echo  '<link rel="stylesheet" media="screen" type="text/css" href="'.$thisurl.'css/admin-layout.css" /> ';
  echo  '<script type="text/javascript" src="'.$thisurl.'js/layout.js"></script>';
  //echo  '<script type="text/javascript" src="'.$thisurl.'js/wfis.js"></script>';
?>
 <link rel='stylesheet' id='thickbox-css'  href='<?php echo includes_url("js/thickbox/thickbox.css?ver=20090514");?>' type='text/css' media='all' />
 <script type='text/javascript'>
/* <![CDATA[ */
var thickboxL10n = {
	<?php
	$thickboxL10n = array(
			'next' => __('Next &gt;'),
			'prev' => __('&lt; Prev'),
			'image' => __('Image'),
			'of' => __('of'),
			'close' => __('Close'),
			'noiframes' => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
			'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
			'closeImage' => includes_url('js/thickbox/tb-close.png') 
	);
	foreach($thickboxL10n as $tk=>$tv){
		echo "$tk: \"$tv\"";
		if($tk != 'closeImage') echo ",";
		echo "\n";
	}
	?>
};
try{convertEntities(thickboxL10n);}catch(e){};
/* ]]> */
</script>
<script type='text/javascript' src='load-scripts.php?c=1&amp;load=thickbox'></script>
<style type="text/css">
.err{color:red;}
.wfis_ul li{height:20px;line-height:20px;padding:0;margin:0;}

#imgPreviewWithStyles {
    background: #222;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    padding: 15px;
    z-index: 999;
    border: none;
}

 
</style>
<script type="text/javascript">

var manu_str = '<?php printf(__('If you want to edit this input field, you must select: %s','wp-flash-img-show'), '`'.__('Manually enter images information','wp-flash-img-show').'`' ); ?>';

<?php
include('js/wfis.js');
?>
</script> 

 <?php global $blog_id; echo "<!-- DEBUG \n config_name: \n".$config_name ."\n _POST['config']: \n". $_POST['config'] ."\n blogid: \n".$blog_id." \n  -->" ; ?>

 <h2>WP flash img show Setting</h2>
 
<fieldset name="wp_basic_options"  class="options">
 <?php
 	$thisurl = get_this_url();
	echo '<script src="'. $thisurl  .'swfobject.js" type="text/javascript"></script>' ;
	echo "\n" ;
 ?>
<h3 id="Preview"><?php _e('Preview','wp-flash-img-show'); ?>		</h3>
<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show($config_name,TRUE);} ?> 		
<br />

<span id="getcodebutt" onclick="showcode()" style="cursor:pointer;color:#21759B;text-decoration:underline;"><?php _e('Get code','wp-flash-img-show'); ?> </span>&nbsp;|&nbsp;
<a target="_blank" href="http://www.google.com/support/accounts/bin/answer.py?answer=32050">
<?php _e("If there's no change,clear your Browser's Cache and try again.",'wp-flash-img-show'); ?>
</a>
	<div id="this_config_code" style="display:none;background:#FFFFaa;width:650px">
		<b style="color:#ff0000">HTML</b> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> ) :<br />
		<span id="htmlcode" onclick="SelectText('htmlcode')" ><?php highlight_string('<div id="wp_flash_img_show_here_'.$config_name.'">wp_flash_img_show will display here (config: '.$config_name.')</div>') ?></span> 
		<br />
		<b style="color:#ff0000">PHP</b> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> ) :<br />
		<span id="phpcode" onclick="SelectText('phpcode')" ><?php highlight_string("<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('".$config_name."');} ?>") ?></span> 
	</div>
<?php
$wfis_array = get_option("wp_flash_img_show");
 
// 对于新Config的处理
 if ( strlen( $wfis_array[$config_name]["pic_number"] ) == 0 )  
{
  new_config_initialize($config_name) ; //DEBUG   
  $wfis_array = get_option("wp_flash_img_show");
} 
 
$pic_number = $wfis_array[$config_name]["pic_number"];
 
?>

 

<h3><?php _e('Manage Config','wp-flash-img-show'); ?></h3>
<table>
<tr>
<td width="150" >
	<?php _e('Choose a Config','wp-flash-img-show'); ?>
</td>
<td>
	<form method="post" action="" name="f1" id="f1" >
		<select  name="config" id="select_config" >
			<?php
			foreach ($wfis_array as $key => $theconfig )
			{
			?>
			<option value="<?php echo $key ; ?>" <?php if ( $config_name == $key  ) { echo 'selected="true"' ; } ?> ><?php echo $key ; ?> </option>
			<?php
			}
			?>
		</select>		 
		<input  type="submit"  name="change_config" value="<?php _e('Edit this Config','wp-flash-img-show'); ?>"   class="wfis_btn" />
		 <input  type="submit"  name="del_config" value="<?php _e('Delete Config','wp-flash-img-show'); ?>"   onclick="return  checkupthis()"  class="wfis_btn" />
		<script language="javascript">
			function checkupthis()
			{ 
			var obj=document.getElementById("select_config").value;
			if (
			window.confirm("<?php _e('Delete This Config','wp-flash-img-show'); ?> : [ "+obj+" ]")
			)
				{ return true; }
				else
				{ return false; }
			}
		</script>
	</form>
</td>
</tr>

<tr>
	<td>
		<?php _e('New Config Name','wp-flash-img-show'); ?>
	</td>
	<td>
	<form method="post" action="" name="f2" id="f2" >	
		<input  type="text" name="config" value="" size="19"  />		 
		<input  type="submit"  name="create_config" value="<?php _e('Create a New Config','wp-flash-img-show'); ?>"  class="wfis_btn"  />
	</form>

	</td>
</tr>
<tr>
	<td colspan="3" >
	<?php _e('Config Name Only consist of letters,numbers','wp-flash-img-show'); ?>
	</td> 
</tr>
</table>

 
<h3><?php _e('Basic Settings','wp-flash-img-show'); ?> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> )</h3>

<form method="post" action="">
<table>
			<tr>
                <td valign="top" align="right"><?php _e('Img item number','wp-flash-img-show'); ?>:</td>
				<td>
				<input size="3" type="text" name="wfis_option[pic_number]" value="<?php echo $pic_number ;  ?>" /> 
				<?php
				if( ! $pic_number>0 )
					echo ' <b style="color:red">(Must greater than zero)</b> ';
				?>
				&nbsp; 
				</td>
		</tr>
</table>

<ul class="wfis_ul">
<li>
<label><input type="radio" name="wfis_option[autogetimg]" value="" onclick="pic_area_access('')"  <?php if ($wfis_array[$config_name]["autogetimg"]=="") echo 'checked="checked"'; ?> /><?php _e('Manually enter images information','wp-flash-img-show'); ?> (<?php _e('default','wp-flash-img-show'); ?>)</label>
</li>
<li>
<label><input type="radio" name="wfis_option[autogetimg]" value="frompost"  onclick="pic_area_access('frompost')" <?php if ($wfis_array[$config_name]["autogetimg"]=="frompost") echo 'checked="checked"'; ?> /><?php _e('Get the first images from Recent post automatic.','wp-flash-img-show'); ?> </label> 
<label style="display:none;"><input type="checkbox" name="wfis_option[autogetimg_post_shortcode]"  value="true"  <?php if ($wfis_array[$config_name]["autogetimg_post_shortcode"]=="true") echo 'checked="checked"'; ?> /><?php _e('include gallery','wp-flash-img-show'); ?>
</label> 
</li>
<li>
<label><input type="radio" name="wfis_option[autogetimg]" value="frompage" onclick="pic_area_access('frompage')" <?php if ($wfis_array[$config_name]["autogetimg"]=="frompage") echo 'checked="checked"'; ?> /><?php _e('Get images from this page automatic','wp-flash-img-show'); ?>:</label> 
<?php
echo wp_dropdown_pages( array( 'name' => 'wfis_option[autogetimg_page_id]', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $wfis_array[$config_name]["autogetimg_page_id"] ) );
?> <label><input type="checkbox" name="wfis_option[autogetimg_page_descend]"  value="true"  <?php if ($wfis_array[$config_name]["autogetimg_page_descend"]=="true") echo 'checked="checked"'; ?> /><?php _e('descend','wp-flash-img-show'); ?></label>  
 | <label><input type="checkbox" name="wfis_option[autogetimg_page_shortcode]"  value="true"  <?php if ($wfis_array[$config_name]["autogetimg_page_shortcode"]=="true") echo 'checked="checked"'; ?> /><?php _e('include gallery','wp-flash-img-show'); ?>
 </label> 
 
</li>
 </ul>


<div style="padding:2px 0;margin:5px 0;" >
<table id="pic_area" style="border:1px dashed #666;padding:2px 0;margin:5px 0;">
	<tr>
		<td align="center" >#</td>
		<td align="center"><?php _e('Image URL','wp-flash-img-show'); ?></td>
		<td align="center"><?php _e('Link','wp-flash-img-show'); ?></td>
		<td align="center"><?php _e('description','wp-flash-img-show'); ?></td> 
	</tr>
	<?php
 
	$pic_array =  $wfis_array[$config_name]["pic"];
	for ($i=1; $i<= $pic_number; $i++) {

	?>
		<tr>
			<td><?php echo $i ?>: </td>
			
			<td>
				<input id="url_<?php echo $i ?>" onclick="focus_url()" class="input_url" size="50" type="text" name="wfis_option[<?php echo $i ?>_url]" value="<?php echo $pic_array[$i]['url'];  ?>" <?php if(strtolower(getdomain(home_url('/'))) != strtolower(getdomain($pic_array[$i]['url']))){ $cross_domain=TRUE; echo 'style="background: #FFCCCC;"'; }?> />
				<a href="media-upload.php?post_id=0&amp;type=image&amp;tab=library&amp;TB_iframe=1&amp;is_wp_flash_img_show=true" class="thickbox select_image" title="<?php _e('Select/Upload a image from media library','wp-flash-img-show');?>" rel="url_<?php echo $i ?>"><img src="images/media-button-image.gif"  /></a>
			</td>
			
			<td><input id="link_<?php echo $i ?>" type="text" name="wfis_option[<?php echo $i ?>_link]" value="<?php echo $pic_array[$i]['link'];  ?>" /> 
			<img src="<?php echo get_this_url()?>css/extlink.png" rel="link_<?php echo $i ?>" border="0" class="link_extlink" />
			</td>
			
			<td><input id="description_<?php echo $i ?>" type="text" name="wfis_option[<?php echo $i ?>_description]" value="<?php echo stripslashes($pic_array[$i]['description']);  ?>" /> </td> 
		</tr>
	<?php
	}
	?>	
</table>

<span  <?php if($cross_domain) echo 'style="background: #FFCCCC;font-weight:bold;"'; ?> ><?php _e('Image URL is not allow cross-domain , it must begin with:','wp-flash-img-show'); 
echo ' http://'.getdomain(home_url('/'));
?></span>
<br />
<?php _e('Do Not upload your image file to "wp-content/plugins/wp_flash_img_show/images/" ,or you may lose any custom image files when update this plugin.','wp-flash-img-show'); ?>
 
</div>

<h3><?php _e('Display option','wp-flash-img-show'); ?> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> )</h3>
<?php 
// 详细设置 

 
 $options_array = $wfis_array[$config_name]["option"] ;
 $options_array = $options_array + wfis_def_options_store(TRUE);
?>
<table class="tbl_mid">
	<tr>
		<td><?php _e('Width','wp-flash-img-show'); ?></td>
		<td><input type="text" name="wfis_option[width]" value="<?php echo $options_array["wp_flash_img_show_width"];  ?>" /></td>
		<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 400 </td>
	</tr>
	
	<tr>
		<td><?php _e('Height','wp-flash-img-show'); ?></td>
		<td><input type="text" name="wfis_option[height]" value="<?php echo $options_array["wp_flash_img_show_height"];  ?>" /></td>
		<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 200 </td>
	</tr>

	<tr>
		<td><?php _e('Round Corner','wp-flash-img-show'); ?></td>
		<td><input type="text" name="wfis_option[roundcorner]" value="<?php echo $options_array["wp_flash_img_show_roundcorner"];  ?>" /></td>
		<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 10 </td>
	</tr>		
	
	<tr>
		<td><?php _e('Auto Play Time','wp-flash-img-show'); ?></td>
		<td><input type="text" name="wfis_option[autoplaytime]" value="<?php echo $options_array["wp_flash_img_show_autoplaytime"];  ?>" /></td>
		<td>(s) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 3</td>
	</tr>


<!-- 按钮 // button-->
	<tr>	
	<td> <?php _e('Is Show button','wp-flash-img-show'); ?> </td>
	<td>
	<label><input type="radio" name="wfis_option[isshowbtn]" value="true" <?php if( $options_array["wp_flash_img_show_isshowbtn"] == 'true' ) echo ' checked="checked" ' ; ?> onclick="btn_option_show(true);"><?php _e('Yes','wp-flash-img-show'); ?></label>
	 | <label><input type="radio" name="wfis_option[isshowbtn]" value="false" <?php if( $options_array["wp_flash_img_show_isshowbtn"] == 'false' ) echo ' checked="checked" ' ; ?>  onclick="btn_option_show(false);"><?php _e('No','wp-flash-img-show'); ?></label>
	 <?php if( $options_array["wp_flash_img_show_isshowbtn"] == 'false' )
		echo '<style type="text/css">.btn_option{display:none;}</style>';
	 ?>
	 
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('Yes','wp-flash-img-show'); ?> </td>
	</tr>	
	
	<tr class="btn_option">
	<td><?php _e('Button Margin','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btnsetmargin]" value="<?php echo $options_array["wp_flash_img_show_btnsetmargin"];  ?>" /></td>
	<td>(top right bottom left) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: auto 5 5 auto</td>
	</tr>

	<tr class="btn_option">
	<td><?php _e('Button Distance','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btndistance]" value="<?php echo $options_array["wp_flash_img_show_btndistance"];  ?>" /></td>
	<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 20 </td>
	</tr>

	<tr class="btn_option">
	<td><?php _e('Button FontSize','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btnfontsize]" value="<?php echo $options_array["wp_flash_img_show_btnfontsize"];  ?>" /></td>
	<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 10 </td>
	</tr>

	<tr class="btn_option">
	<td><?php _e('Button Width','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btnwidth]" value="<?php echo $options_array["wp_flash_img_show_btnwidth"];  ?>" /></td>
	<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 16 </td>
	</tr>

	<tr class="btn_option">
	<td><?php _e('Button Height','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btnheight]" value="<?php echo $options_array["wp_flash_img_show_btnheight"];  ?>" /></td>
	<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 16 </td>
	</tr>

	<tr class="btn_option">	
	<td><?php _e('Button Alpha','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[btnalpha]" value="<?php echo $options_array["wp_flash_img_show_btnalpha"];  ?>" /></td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0.7 </td>
	</tr>
		
	<tr class="btn_option">
	<td><?php _e('Button Text Color','wp-flash-img-show'); ?> </td>
	<td><input type="text" id="color3"  name="wfis_option[btntextcolor]" value="<?php echo $options_array["wp_flash_img_show_btntextcolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btntextcolor"]);  ?>"  /></td>
	<td>
		<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color3')" /> 
		&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xffffff </td>
	</tr>
		
	<tr class="btn_option">	
	<td><?php _e('Button Default Color','wp-flash-img-show'); ?> </td>
	<td><input type="text" id="color4"  name="wfis_option[btndefaultcolor]" value="<?php echo $options_array["wp_flash_img_show_btndefaultcolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btndefaultcolor"]);  ?>"  /></td>
	<td>
	<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color4')" /> 
	&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0x1B3433 </td>
	</tr>	

	<tr class="btn_option">
	<td> <?php _e('Button Hover Color','wp-flash-img-show'); ?> </td>
	<td><input type="text" id="color5"  name="wfis_option[btnhovercolor]" value="<?php echo $options_array["wp_flash_img_show_btnhovercolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btnhovercolor"]);  ?>"  /></td>
	<td>
	<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color5')" /> 
	&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff9900 </td>
	</tr>
		
	<tr class="btn_option">	
	<td> <?php _e('Button Focus Color','wp-flash-img-show'); ?> </td>
	<td><input type="text" id="color6"  name="wfis_option[btnfocuscolor]" value="<?php echo $options_array["wp_flash_img_show_btnfocuscolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btnfocuscolor"]);  ?>"  /></td>
	<td>
		<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color6')" /> 
		&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff6600 </td>
	</tr>


	<!-- 描述 // title-->
	<tr>
	<td> <?php _e('Is Show Description','wp-flash-img-show'); ?> </td>
	<td> 
	<label><input type="radio" name="wfis_option[isshowtitle]" value="true" <?php if( $options_array["wp_flash_img_show_isshowtitle"] == 'true' ) echo ' checked="checked" ' ; ?> onclick="title_option_show(true);" /><?php _e('Yes','wp-flash-img-show'); ?></label>
	 | <label><input type="radio" name="wfis_option[isshowtitle]" value="false" <?php if( $options_array["wp_flash_img_show_isshowtitle"] == 'false' ) echo ' checked="checked" ' ; ?>  onclick="title_option_show(false);" /><?php _e('No','wp-flash-img-show'); ?></label>
	 <?php if( $options_array["wp_flash_img_show_isshowtitle"] == 'false' )
		echo '<style type="text/css">.title_option{display:none;}</style>';
	 ?>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('Yes','wp-flash-img-show'); ?> </td>
	</tr>	

	<tr class="title_option">	
	<td><?php _e('Description Location','wp-flash-img-show'); ?></td>
	<td>
	<select  name="wfis_option[titlelocation]" >
	<option value="top" <?php  if ( $options_array["wp_flash_img_show_titlelocation"]   == "top" ) echo "selected";  ?> ><?php _e('top','wp-flash-img-show'); ?></option>
	<option value="bottom" <?php if ( $options_array["wp_flash_img_show_titlelocation"]  == "bottom" ) echo "selected";  ?> ><?php _e('bottom','wp-flash-img-show'); ?></option>
	<option value="inside" <?php if ( $options_array["wp_flash_img_show_titlelocation"]   == "inside" ) echo "selected";  ?> ><?php _e('inside','wp-flash-img-show'); ?></option>
	</select>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('top','wp-flash-img-show'); ?> </td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Top Position','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[titlepositiony]" value="<?php echo $options_array["wp_flash_img_show_titlepositiony"];  ?>" /></td>
	<td> (px)&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 100 (<?php _e('Take effect Only if `Description Location` is `inside`','wp-flash-img-show'); ?>)</td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Text Font','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[titlefont]" value="<?php echo $options_array["wp_flash_img_show_titlefont"];  ?>" /></td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: TAHOMA</td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Text FontSize','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[titlefontsize]" value="<?php echo $options_array["wp_flash_img_show_titlefontsize"];  ?>" /></td>
	<td> (px)&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 12</td>
	</tr>

	<tr class="title_option">
	<td><?php _e('Description Text Color','wp-flash-img-show'); ?></td>
	<td><input type="text" id="color2"  name="wfis_option[titletextcolor]" value="<?php echo $options_array["wp_flash_img_show_titletextcolor"];  ?>"  style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_titletextcolor"]);  ?>" /></td>
	<td>
	<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color2')" /> 
	&nbsp;<?php _e('default','wp-flash-img-show'); ?>:  0xffffff</td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Text Align','wp-flash-img-show'); ?></td>
	<td>
	<select  name="wfis_option[titletextalign]" >
	<option value="center" <?php  if ( $options_array["wp_flash_img_show_titletextalign"]   == "center" ) echo "selected";  ?> ><?php _e('center','wp-flash-img-show'); ?></option>
	<option value="left" <?php if ( $options_array["wp_flash_img_show_titletextalign"]  == "left" ) echo "selected";  ?> ><?php _e('left','wp-flash-img-show'); ?></option>
	<option value="right" <?php if ( $options_array["wp_flash_img_show_titletextalign"]   == "right" ) echo "selected";  ?> ><?php _e('right','wp-flash-img-show'); ?></option>
	</select>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('center','wp-flash-img-show'); ?></td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Move Duration','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[titlemoveduration]" value="<?php echo $options_array["wp_flash_img_show_titlemoveduration"];  ?>" /></td>
	<td> (s)&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 1 (<?php _e('Take effect Only if `Description Location` is `top` or `bottom`','wp-flash-img-show'); ?>)</td>
	</tr>

	<tr class="title_option">	
	<td><?php _e('Description Background Alpha','wp-flash-img-show'); ?></td>
	<td><input type="text" name="wfis_option[titlebgalpha]" value="<?php echo $options_array["wp_flash_img_show_titlebgalpha"];  ?>" /></td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0.75 </td>
	</tr>

	<tr class="title_option">
	<td><?php _e('Description Background Color','wp-flash-img-show'); ?></td>
	<td><input type="text" id="color1"  name="wfis_option[titlebgcolor]" value="<?php echo $options_array["wp_flash_img_show_titlebgcolor"];  ?>"  style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_titlebgcolor"]);  ?>"  /></td>
	<td>
		<img src="<?php echo get_this_url() ?>css/picker.png" border="0" class="picker_img"  onclick="focus_it('#color1')" /> 
		&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff6600</td>
	</tr>

	<tr class="title_option">
	<td><?php _e('Description Background Height','wp-flash-img-show'); ?></td>
	<td><input type="text" id=""  name="wfis_option[titlebgheight]" value="<?php echo $options_array["wp_flash_img_show_titlebgheight"];  ?>"  style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_titlebgheight"]);  ?>"  /></td>
	<td> (px)&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 24</td>
	</tr>

	<tr style="display:none;">
	<td><?php _e('Is Height Quality','wp-flash-img-show'); ?></td>
	<td> 
	<label><input type="radio" name="wfis_option[isheightquality]" value="true" <?php if( $options_array["wp_flash_img_show_isheightquality"] != 'false' ) echo ' checked="checked" ' ; ?> ><?php _e('Yes','wp-flash-img-show'); ?></label>
	 | <label><input type="radio" name="wfis_option[isheightquality]" value="false" <?php if( $options_array["wp_flash_img_show_isheightquality"] == 'false' ) echo ' checked="checked" ' ; ?> ><?php _e('No','wp-flash-img-show'); ?></label>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('Yes','wp-flash-img-show'); ?></td>
	</tr>

	<!--
	<tr>
	<td>Normal</td>
	<td><input type="text" name="wfis_option[normal]" value="<?php echo $options_array["wp_flash_img_show_normal"];  ?>" /></td>
	<td> &nbsp; </td>
	</tr>
	-->

	<tr>
	<td><?php _e('Window Open','wp-flash-img-show'); ?></td>
	<td>
	<label><input type="radio" name="wfis_option[windowopen]" value="_blank" <?php if( $options_array["wp_flash_img_show_windowopen"] == "_blank" ) echo ' checked="checked" ' ; ?> /><?php _e('New','wp-flash-img-show'); ?> </label>
	 | <label><input type="radio" name="wfis_option[windowopen]" value="_self" <?php if( $options_array["wp_flash_img_show_windowopen"] == "_self" ) echo ' checked="checked" ' ; ?>  /><?php _e('Current','wp-flash-img-show'); ?></label>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('New','wp-flash-img-show'); ?> </td>
	</tr>

	<tr>
	<td> <?php _e('Chang Image Mode','wp-flash-img-show'); ?> </td>
	<td>
	<label><input type="radio" name="wfis_option[changimagemode]" value="click" <?php if( $options_array["wp_flash_img_show_changimagemode"] == 'click' ) echo ' checked="checked" ' ; ?> ><?php _e('click','wp-flash-img-show'); ?> </label>
	 | <label><input type="radio" name="wfis_option[changimagemode]" value="hover" <?php if( $options_array["wp_flash_img_show_changimagemode"] == 'hover' ) echo ' checked="checked" ' ; ?> ><?php _e('hover','wp-flash-img-show'); ?> </label>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: <?php _e('click','wp-flash-img-show'); ?> </td>
	</tr>	

	<tr>
	<td> <?php _e('Scale Mode','wp-flash-img-show'); ?> </td>
	<td>
	<select  name="wfis_option[scalemode]" >
	<option value="noBorder" <?php  if ( $options_array["wp_flash_img_show_scalemode"]   == "noBorder" ) echo "selected";  ?> >No Border</option>
	<option value="showAll" <?php if ( $options_array["wp_flash_img_show_scalemode"] == "showAll" ) echo "selected";  ?> >Show All</option>
	<option value="exactFil" <?php if ( $options_array["wp_flash_img_show_scalemode"]   == "exactFil" ) echo "selected";  ?> >Exact Filte</option>
	<option value="noScale" <?php if ( $options_array["wp_flash_img_show_scalemode"]   == "noScale" ) echo "selected";  ?> >No Scale</option>
	</select>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: noBorde </td>
	</tr>
		
	<tr>
	<td> <?php _e('Transform Mode','wp-flash-img-show'); ?> </td>
	<td>
	<select  name="wfis_option[transform]" >
	<option value="alpha" <?php  if ( $options_array["wp_flash_img_show_transform"]   == "alpha" ) echo "selected";  ?> >alpha</option>
	<option value="blur" <?php if ( $options_array["wp_flash_img_show_transform"]  == "blur" ) echo "selected";  ?> >blur</option>
	<option value="left" <?php if ( $options_array["wp_flash_img_show_transform"]   == "left" ) echo "selected";  ?> >left</option>
	<option value="right" <?php if ( $options_array["wp_flash_img_show_transform"]   == "right" ) echo "selected";  ?> >right</option>
	<option value="top" <?php if ( $options_array["wp_flash_img_show_transform"]  == "top" ) echo "selected";  ?> >top</option>
	<option value="bottom" <?php if ( $options_array["wp_flash_img_show_transform"] == "bottom" ) echo "selected";  ?> >bottom</option>
	<option value="breathe" <?php if ( $options_array["wp_flash_img_show_transform"]  == "breathe" ) echo "selected";  ?> >breathe</option>
	<option value="breatheBlur" <?php if ( $options_array["wp_flash_img_show_transform"] == "breatheBlur" )  echo "selected";  ?> >breathe+Blur</option>
	</select>
	</td>
	<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>:  alpha</td>
	</tr>

	<tr style="display:none;">
	<td>   </td>
	<td>
	 <div style="display:none;" ><input type="radio" name="wfis_option[isshowabout]" value="false"  checked  >False
	</div>
	 </td>
	<td>  </td>
	</tr>
</table>
			
		</fieldset>
		<p class="submit">
		<input type="hidden" value="<?php echo $config_name ; ?>" name="config">
		<input  class="button-primary" type="submit" name="update_wp_flash_img_show_option" value="<?php _e('Update Options','wp-flash-img-show'); ?>" />
	</form>
<br />
<form method="post" action="">
	<input type="hidden" value="<?php echo $config_name ; ?>" name="config">
	<input  type="submit" name="set_wp_flash_img_show_default_option" value="<?php _e('Default option','wp-flash-img-show'); ?>" onclick="return checkup()" class="wfis_btn" />
		<script language="javascript">
			function checkup()
			{
				if(window.confirm("[ <?php echo $config_name ; ?> ] <?php _e('Load Defaults Setting! Are You Sure ?','wp-flash-img-show'); ?>"))
				{
					return true;
				}
				else
				{
				return false;
				}
			}
		</script>
		<a href="http://xwjie.com/post/wp-flash-img-show.html"  target="_blank" ><?php _e('Have trouble? Just Click here.','wp-flash-img-show'); ?> </a>
	</p>
</form>	

 <br />
 
<!-- 帮助部分 -->
 
 <hr>
 <h3><?php _e('Where is it display ?','wp-flash-img-show'); ?></h3>
<div style="padding-top:5px" >
 

<b><?php _e('Method','wp-flash-img-show'); ?>1 : </b>
<?php _e('Put this (HTML) code in your Template / Post / Widgets(text-widgets)','wp-flash-img-show'); ?>.
<br />
<span id="htmlcode2" onclick="SelectText('htmlcode2')" ><?php highlight_string('<div id="wp_flash_img_show_here_'.$config_name.'">wp_flash_img_show will display here (config: '.$config_name.')</div>') ?></span>  
<b>( <?php printf(__('Display Config: %s','wp-flash-img-show'),$config_name); ?>  )</b>
<br /><br />
<b><?php _e('Method','wp-flash-img-show'); ?>2 : </b>
<?php _e('Put this (PHP) code in your template ','wp-flash-img-show'); ?>.
<br />
<span id="phpcode2" onclick="SelectText('phpcode2')"><?php highlight_string("<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('$config_name');} ?>") ?></span> 
<b>( <?php printf(__('Display Config: %s','wp-flash-img-show'),$config_name); ?>  )</b>
<br /><br />
<?php _e('Make sure have <code>wp_head()</code> just before the closing <code>&lt;/head&gt;</code> tag of your theme AND  have <code>wp_footer()</code> just before the closing <code>&lt;/body&gt;</code> tag of your theme, or you will break many plugins.(You can edit and add those function to your theme file.)','wp-flash-img-show'); ?><br />
<a target="_blank" href="http://codex.wordpress.org/Function_Reference/wp_head">About wp_head()</a> | 
<a target="_blank" href="http://codex.wordpress.org/Function_Reference/wp_footer">About wp_footer()</a> 
<br />
<?php 
 _e('If the plugin can not work in you theme(template), `Debug mode` can help you.','wp-flash-img-show');
$wfis_oo_array = get_option("wp_flash_img_show_info") ;

 ?>
<form method="post" action="">
<input type="hidden" name="debug_mode" value="false"/>
<?php _e('Enable Debug mode','wp-flash-img-show');?>:<input type="checkbox" name="debug_mode" value="true" <?php if($wfis_oo_array['debug_mode']=='true') echo 'checked="checked"' ?>/>
<input type="submit" name="submit" value="<?php _e('Save','wp-flash-img-show');?>"/>
<b style="color:red"><?php if($wfis_oo_array['debug_mode']=='true') printf(__('Browse the %s home page %s now, you will get some tips.','wp-flash-img-show') ,'<a href="'.home_url().'" target="_blank">' ,'</a>' ); ?></b>
</form>
</div>
<br />
 
 
<?php _e('If you find my work useful and you want to encourage the development of more free resources, you can do it by donating...','wp-flash-img-show'); ?> 
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="on0" value="Choose">
<table>
<tr><td> paypal:
<select name="os0">
	<option value="2 USD">2 USD $2.00</option>
	<option value="5 USD">5 USD $5.00</option>
	<option value="10 USD">10 USD $10.00</option>
</select> </td><td>&nbsp;
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="http://file.xwjie.com/en-btn_donate_LG.gif" border="0" name="submit" alt="PayPal——最安全便捷的在线支付方式！">
<!-- <img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1"> -->
</form>
</td>

<td>
</td>
<td>
</td>

</tr>
</table>
 
<script type="text/javascript">

jQuery(document).ready(function(){
	// 设置只读
	pic_area_access('<?php echo $wfis_array[$config_name]["autogetimg"]; ?>');

	// 添加预览
	hook_img(<?php echo $pic_number ?>);
	
});
	
</script>

</div> <!--/wrap-->
<?php

} //页面主函数结束===============================================

function wp_flash_img_show_options_admin(){
	add_options_page('wp_flash_img_show', 'WP flash img show', 5,  __FILE__, 'wp_flash_img_show_options');
}
add_action('admin_menu', 'wp_flash_img_show_options_admin');
 
 
//添加到 header
function wp_flash_img_show_header(){
	$thisurl = get_this_url();
	echo '<script src="'. $thisurl  .'swfobject.js" type="text/javascript"></script>' ;
	echo "\n" ;
 }
add_action('wp_head', 'wp_flash_img_show_header');
//add_action('admin_head', 'wp_flash_img_show_header');

//添加到 footer
function wp_flash_img_show_footer(){

$thisurl = get_this_url();

 global $blog_id;
if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite())) {
  $xmlBaseFileName =  "wp-flash-img-show-ms".$blog_id ; 
  } else {
  $xmlBaseFileName =  "wp-flash-img-show" ;
  } 
  
$wfis_array = get_option("wp_flash_img_show"); 

$xmlFileName = $xmlBaseFileName;
$xmlFileName .= '.xml';
?>
<script type="text/javascript">
if (document.getElementById('wp_flash_img_show_here')!=null) {
 swfobject.embedSWF("<?php echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show_here", "<?php echo $wfis_array["default"]["option"]["wp_flash_img_show_width"];  ?>", "<?php echo $wfis_array["default"]["option"]["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl . $xmlFileName; ?>?t=<?php echo wfis_file_ver('wp-flash-img-show.xml')?>"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
 }
</script> 
<script type="text/javascript">
<?php 
foreach ($wfis_array as $key => $theconfig )
{
$configname = $key;
$options_array = $wfis_array[$configname]["option"];
  $xmlFileName = $xmlBaseFileName;
  if ($configname != "default") { $xmlFileName .=  "-".$configname; }
  $xmlFileName .= '.xml';
?>
if (document.getElementById('wp_flash_img_show_here_<?php echo $configname ;?>')!=null) {
swfobject.embedSWF("<?php echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show_here_<?php echo $configname ;?>", "<?php echo $options_array["wp_flash_img_show_width"];  ?>", "<?php echo $options_array["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl . $xmlFileName ?>?t=<?php echo wfis_file_ver($xmlFileName); ?>"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
 }
<?php
}
?>
</script>
<?php
 }
 
 
 add_action('wp_footer', 'wp_flash_img_show_footer');



//主题(PHP)调用函数
function wp_flash_img_show($return_config_name = "default", $refresh=FALSE){
	$thisurl = get_this_url();

			if ( strlen($return_config_name) == 0 ) //这里不同前面的
		{
			$config_name = "default";	//这里不同前面的
		}
		else
		{
			$config_name = $return_config_name; //这里不同前面的
		}
 $wfis_array = get_option("wp_flash_img_show");
 
 $options_array = $wfis_array[$config_name]["option"];
 
	//获得文件名  $wp_flash_img_filename . $save_config_xml_name
	$save_config_xml_name = $config_name ;
	$wp_flash_img_filename = "wp-flash-img-show" ;
	if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
	 {
	 global $blog_id;
	 $wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
	}
	if ($save_config_xml_name == "default" ) 
		{ $save_config_xml_name = ".xml"; }
	else
		{ $save_config_xml_name = "-".$save_config_xml_name.".xml" ; }
	?>
<div id="wp_flash_img_show_box" style="width:<?php echo $options_array["wp_flash_img_show_width"];  ?>px;height:<?php echo $options_array["wp_flash_img_show_height"];  ?>px;">
<div id="wp_flash_img_show<?php if ($config_name != "default") { echo "_".$config_name; } ?>">This movie requires Flash Player 9</div> 
<script type="text/javascript"> 
	swfobject.embedSWF("<?php echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show<?php if ($config_name != "default") { echo "_".$config_name; } ?>", "<?php echo $options_array["wp_flash_img_show_width"];  ?>", "<?php echo $options_array["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl . $wp_flash_img_filename . $save_config_xml_name ?><?php if($refresh) echo '?t='.time() ?>"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
</script> 
</div>
<?php
}

//保存XML函数
function wp_flash_img_show_save_to_xml($save_config_xml_name) {
 
  $doc = new DOMDocument();
  $doc->formatOutput = true;
  
  $r = $doc->createElement( "data" );
  $doc->appendChild( $r );
  

  $_channel =  $doc->createElement( "channel" ); //  channel star  , img list star

  
 
$wfis_array = get_option("wp_flash_img_show");

  $pic_array =  $wfis_array[$save_config_xml_name]["pic"];
  
for ($i=1; $i<= $wfis_array[$save_config_xml_name]["pic_number"]; $i++) {

	if(trim($pic_array[$i]["url"]) != '' )
	{
	 //item
	  $_item = $doc->createElement( "item" );
	  //list star
	  $_link = $doc->createElement( "link" );
	  $_link->appendChild( $doc->createTextNode( $pic_array[$i]["link"] ) );
	  $_item->appendChild( $_link );
	  
	  $_image = $doc->createElement( "image" );
	  $_image->appendChild(  $doc->createTextNode(  $pic_array[$i]["url"] ) );
	  $_item->appendChild( $_image );
	  
	  $_title = $doc->createElement( "title" );
	  $_title->appendChild( $doc->createTextNode(  stripslashes($pic_array[$i]["description"])  ) );
	  $_item->appendChild( $_title ); 
	  //list end
	  $_channel->appendChild( $_item );
   }
}

  $r->appendChild( $_channel ); //  channel end All img listed
  
// Config star  
 $_config =  $doc->createElement( "config" );
 
 $option_true_names = wfis_option_name();
 //array("roundCorner","autoPlayTime","isHeightQuality","windowOpen","btnSetMargin","btnDistance","titleBgColor","titleBgAlpha","titleTextColor","titleFont","titleMoveDuration","btnAlpha","btnTextColor","btnDefaultColor","btnHoverColor","btnFocusColor","changImageMode","isShowBtn","isShowTitle","scaleMode","transform","isShowAbout");
 $option_names =  wfis_option_name(TRUE);
 //array("roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");

 $options_array =  $wfis_array[$save_config_xml_name]["option"];
 
 $wfis_def_options_store = wfis_def_options_store(TRUE);
 
 $options_array = $options_array + $wfis_def_options_store;
 
 $option_number = count($option_names) - 1;
 for ($i=0; $i<= $option_number ; $i++) {
//config item  
 $itemnames = "wp_flash_img_show_".$option_names[$i] ;
 $item_true_names = "wp_flash_img_show_".$option_true_names[$i] ;
 $$itemnames = $doc->createElement( $option_true_names[$i] );
 if(trim($options_array[$itemnames])=='') 
	$options_array[$itemnames] = $wfis_def_options_store[$itemnames];
 $$itemnames->appendChild( $doc->createTextNode(   $options_array[$itemnames]    ) );
 $_config->appendChild( $$itemnames ); 
}
 
 $r->appendChild( $_config ); // Config End   
	
 
	$wp_flash_img_filename = "wp-flash-img-show" ;
	if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
	 {
	 global $blog_id;
	 $wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
	}
	if ($save_config_xml_name == "default" ) 
		{ $save_config_xml_name = ".xml"; }
	else
		{ $save_config_xml_name = "-".$save_config_xml_name.".xml" ; }
 
  $wp_flash_img_xml_path = dirname(__FILE__) ."/". $wp_flash_img_filename . $save_config_xml_name ;
    $doc->save($wp_flash_img_xml_path);
 
} // XML End

 

function new_config_initialize($config_name) {

 $wfis_array = get_option("wp_flash_img_show") ;
 $wfis_array[$config_name]["pic_number"] = "4"; //save number	
 $wfis_array[$config_name]["autogetimg"] = ""; //Auto get img
 $wfis_array[$config_name]["autogetimg_page_id"] = 0;
 $wfis_array[$config_name]["autogetimg_page_descend"] = ''; //倒叙？
 $wfis_array[$config_name]["autogetimg_page_shortcode"] = 'true'; // 转换shortcode？
// Save IMG   
$thisurl = get_this_url();
	$store_pic_array = array();
 		
 
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/01.jpg";
		$each_pic_array["link"]="http://xwjie.com";
		$each_pic_array["description"]="XWJie Home";
		$store_pic_array[1]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/02.jpg";
		$each_pic_array["link"]="http://xwjie.com/project";
		$each_pic_array["description"]="ore no imoto";
		$store_pic_array[2]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/03.jpg";
		$each_pic_array["link"]="http://xwjie.com/guestbook";
		$each_pic_array["description"]="some fruit";
		$store_pic_array[3]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/04.jpg";
		$each_pic_array["link"]="http://xwjie.com/about";
		$each_pic_array["description"]="strawberries";
		$store_pic_array[4]= $each_pic_array;
		$wfis_array[$config_name]["pic"] =  $store_pic_array ; 

//save option  			
/* 	$store_option_array = array();
	$option_names =   array("width","height","roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");
	$default_option = array( "400", "250"  ,	"10",			"3"		,	"true"		,	"_blank","auto 5 5 auto",		"20"	,"0xff6600"	,	"0.75"		,	"0xffffff"	,	"TAHOMA" ,		"1"			, "0.7"		,"0xffffff"		,"0x1B3433"		,"0xff9900"		,"0xff6600"		,"click"		,"true"		,	"true"		,"noBorde"	,"alpha"	,"true");
	$option_number = count($option_names) - 1;
for ($i=0; $i<= $option_number ; $i++) 
		{	
		$itemnames = "wp_flash_img_show_".$option_names[$i] ;
	$store_option_array[$itemnames] = $default_option[$i] ;
		}
		 */
	$store_option_array = wfis_def_options_store(TRUE,TRUE);	
	$wfis_array[$config_name]["option"] =  $store_option_array ;
	 update_option("wp_flash_img_show",$wfis_array);

  wp_flash_img_show_save_to_xml($config_name); 
}




	
/*
*
*  @function get_img_from_post since v1.3
*
*/
function get_img_from_post($config_name) 
{
	$wfis_array = get_option("wp_flash_img_show");
	$pic_number =  $wfis_array[$config_name]["pic_number"];
 	
	global $wpdb;
	$img_count=0;
	$store_pic_array = array();
	
  	//while ($img_count < $pic_number):	
	$select_post_result = $wpdb->get_results("SELECT $wpdb->posts.ID as ID, post_title, post_name,post_content  FROM $wpdb->posts  WHERE  post_status = 'publish' and post_content like '%<img%' ORDER by ID DESC"  );
	
	foreach ($select_post_result as $post) 
	{
		$post_id = (int) $post->ID;
		 // echo "$post_id<br />";
		$post_content=$post->post_content;
		//if($wfis_array[$config_name]["autogetimg_post_shortcode"]=='true') $post_content = xwjie_gallery_shortcode($post_content, $post);
		if($img_count <  $pic_number ) :
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content , $matches);
			$first_img="";
			foreach ($matches [1] as $this_matches)
			{
			if ( getdomain($this_matches) == getdomain(home_url('/')) )
				{ $first_img = $this_matches; break; }  
			}
			if ( (strlen($first_img)>0)  & (getdomain($first_img)==getdomain(home_url('/'))) ):	
				$img_count++;
				$post_permalink = get_permalink($post->ID);
				$post_title = stripslashes($post->post_title);
				$i=$img_count;
				$each_pic_array=array();
				$each_pic_array["url"]=$first_img ;
				$each_pic_array["link"]=$post_permalink;
				$each_pic_array["description"]=$post_title;
				$store_pic_array[$i]= $each_pic_array;
			endif;
		endif;
		if($img_count ==  $pic_number ) { break; } //匹配足够的图片了
	}
	//if (!$select_post_result) {break;} //搜索完毕
 	//endwhile;
 
 	$message = "Get $img_count images automatic ";
 
 	if($img_count < $pic_number ) //实在不够图片数，自动缩小
	{
		//$wfis_array[$config_name]["pic_number"] = $img_count;
		$message = $message ." <b class='err'>( There is NOT enough images which is begin with http://".getdomain(home_url('/'))." )</b> " ;
	}
	
	$wfis_array[$config_name]["pic"] =  $store_pic_array ; 
	update_option("wp_flash_img_show",$wfis_array);		//更新图片配置
	wp_flash_img_show_save_to_xml($config_name);	//更新 XML
	return $message;
}


/*
*
*  @function get_img_from_page since v1.4 
*
*/

function get_img_from_page($config_name) 
{
	$wfis_array = get_option("wp_flash_img_show");
	$autogetimg_page_id = $wfis_array[$config_name]["autogetimg_page_id"];
	if( $autogetimg_page_id + 0 == 0){
		$message ='<b class="err">You MUST Select a page ! </b>';
	} else{
	  
		global $wpdb;
		$page_info = $wpdb->get_results("SELECT $wpdb->posts.ID as ID, post_title, post_name,post_content  FROM $wpdb->posts  WHERE  ID = $autogetimg_page_id      LIMIT 1 "  );
		if ( $page_info) {
			$page_info = $page_info[0];
			$post_content = $page_info->post_content;
			if($wfis_array[$config_name]["autogetimg_page_shortcode"]=='true') $post_content = xwjie_gallery_shortcode($post_content,$page_info ) ;
			preg_match_all('/<img.+?src=[\'"]([^\'"]+?)[\'"].*?>/i', $post_content  , $matches);
			if ($wfis_array[$config_name]["autogetimg_page_descend"]=="true") $desc = TRUE;
			if($desc){
				$i = count($matches[1]); 
				$i--;
			} else {
				$i = 0;
			}
			$up = $wfis_array[$config_name]['pic_number'] ;
			$message = "Get $up images automatic ";
			if($up > count($matches[1]) ) {
				$up = count($matches[1]);
				$message = "Get $up images automatic ";
				$message = $message ." <b class='err'>( There is NOT enough images which is begin with http://".getdomain(home_url('/'))." )</b> " ;
			}
			
			for ($count = 1; $count<=$up ;$count++ ){
				$pic_url = $matches[1][$i];
				if($desc){ $i-- ; } else { $i++ ; }
				$post_permalink = get_permalink($page_info->ID);
				$post_title = stripslashes($page_info->post_title);
				$each_pic_array=array();
				$each_pic_array["url"]=$pic_url ;
				$each_pic_array["link"]=$post_permalink;
				$each_pic_array["description"]=$post_title;
				$store_pic_array[$count]= $each_pic_array;
			}
			 //$wfis_array[$config_name]["pic_number"] = $up;
			 $wfis_array[$config_name]["pic"] =  $store_pic_array ;
			 update_option("wp_flash_img_show",$wfis_array);		//更新图片配置
			wp_flash_img_show_save_to_xml($config_name);	//更新 XML 
		} else {
			$message = "Can NOT Found the Page.";
		}

	}
	return $message;
}




// function  update_xml_when_edit_Post() since v1.3
function update_xml_when_edit_Post()
{
	$wfis_array = get_option("wp_flash_img_show");
	foreach ($wfis_array as $key => $theconfig )
	{	
		$config_name = $key;
		if ($wfis_array[$config_name]["autogetimg"] == "frompost" )
		{
			get_img_from_post($config_name) ;
		}
		if ($wfis_array[$config_name]["autogetimg"] == "frompage" )
		{
			get_img_from_page($config_name) ;
		}
	}
}

add_action('save_post', 'update_xml_when_edit_Post'); //Auto Get img when save post



// function  wp_flash_img_show_deactivation() since v1.3.1
function wp_flash_img_show_activation()
{
	$wfis_array = get_option("wp_flash_img_show");
	foreach ($wfis_array as $key => $theconfig )
	{	
		$config_name = $key;
			wp_flash_img_show_save_to_xml($config_name) ;
	}
	
	$wfis_oo_array = get_option("wp_flash_img_show_info") ;
	$wfis_oo_array['version'] = '1.4';
	update_option("wp_flash_img_show_info",$wfis_oo_array);
}
register_activation_hook(__FILE__, 'wp_flash_img_show_activation');






