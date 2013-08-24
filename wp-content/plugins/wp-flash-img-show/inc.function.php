<?php



/* 
 * deuault_option
 * since v1.4
 * 获取 XML option 的数组
 */
 
 function wfis_def_options($keyToLower=FALSE,$withSize=FALSE){
 
  $def_options = array(
		"roundCorner" =>  "10",
		"autoPlayTime" =>  "3",
		"isHeightQuality" =>  "true",
		"windowOpen" =>  "_blank",
		"btnSetMargin" =>  "auto 5 5 auto",
		"btnDistance" =>  "20",
		"titleBgColor" =>  "0xff6600",
		"titleBgAlpha" =>  "0.75",
		"titleTextColor" =>  "0xffffff",
		"titleFont" =>  "TAHOMA" ,
		"titleMoveDuration" =>  "1",
		"btnAlpha" =>  "0.7",
		"btnTextColor" => "0xffffff" ,
		"btnDefaultColor" =>  "0x1B3433",
		"btnHoverColor" =>  "0xff9900",
		"btnFocusColor" =>  "0xff6600",
		"changImageMode" =>  "click",
		"isShowBtn" =>  "true",
		"isShowTitle" =>  "true",
		"scaleMode" =>  "noBorde",
		"transform" =>  "alpha",
		"isShowAbout" =>  "true",
		
		"btnFontSize" =>  "10",
		"btnWidth" =>  "16",
		"btnHeight" =>  "16",
		"titleLocation" =>  "top",
		"titlePositionY" =>  "100",
		"titleBgHeight" =>  "24",
		"titleTextAlign" =>  "center",
		"titleFontSize" =>  "12"
		);
	if($withSize) 
		$def_options += array( 
						"width" => "400",
						"height" => "250"
						);
	if($keyToLower){
		$lower_def_options;
		foreach($def_options as $key=>$value){
			$lower_def_options[strtolower($key)] = $value;
		}
		return $lower_def_options;
	}
	
	return $def_options;
 }


/* 
 * wfis_def_options_store
 * since v1.4
 * 获取 存进WP的 option 数组（默认值）
 */ 
 function wfis_def_options_store($keyToLower=FALSE,$withSize=FALSE){
	$def_options = wfis_def_options($keyToLower ,$withSize);
	$def_options_store = array();
	foreach($def_options as $key=>$value){
		$def_options_store['wp_flash_img_show_'.$key] = $value;
	}
	return $def_options_store;
 }

 
/* 
 * wfis_option_name
 * since v1.4
 * 获取 xml option key 数组 
 */ 
 function wfis_option_name($keyToLower=FALSE,$withSize=FALSE){
	$def_options = wfis_def_options($keyToLower ,$withSize);
	return array_keys($def_options);
 }
 
 
/*  
 Debug mode 
 since v1.4
 */
 function wfis_debug_header(){
	echo '<script type="text/javascript">function wfis_header(){}</script>';
 }
 
 function wfis_debug_footer(){
	echo '<script type="text/javascript">function wfis_footer(){}</script>';
 }
 
 function wfis_debug_end(){
	if(is_home() || is_page() || is_single() || is_category() || is_author()){
	?>
	<script type="text/javascript" >
	window.onload=function() {
		if (typeof(wfis_header)=='undefined') 
			alert('<?php _e('Lack of `wp_head()`. More:','wp-flash-img-show');?> \n http://codex.wordpress.org/Function_Reference/wp_head');
		if (typeof(wfis_footer)=='undefined') 
			alert('<?php _e('Lack of `wp_footer()`. More:','wp-flash-img-show');?> \n http://codex.wordpress.org/Function_Reference/wp_footer');
		if(typeof(wfis_footer)!='undefined' && typeof(wfis_header)!='undefined')
			alert('<?php _e('Wp-flash-img-show can work now. Please Unchecked `Debug mode` on the Wp-flash-img-show Setting panel.','wp-flash-img-show');?>');
		}
	</script>
	<?php
	}
 }
 
  function wfis_debug_init(){
	 $wfis_oo_array = get_option("wp_flash_img_show_info") ;
	 if($wfis_oo_array['debug_mode']=='true'){
		 add_action('wp_head', 'wfis_debug_header'); 
		 add_action('wp_footer', 'wfis_debug_footer'); 
		 add_action('get_footer', 'wfis_debug_end');  
	 }
  }

  add_action('init', 'wfis_debug_init'); 
 
 
/* 
 * wfis_translate_old_ver
 * since v1.2
 * 从1.2以前的版本的设置迁移
 */
function wfis_translate_old_ver() 
	{
	//since ver.1.2
	$config_name = "default"; //debug
	 $wfis_array = get_option("wp_flash_img_show") ;
	 $wfis_array[$config_name]["pic_number"] = get_option("wp_flash_img_show_pic_number"); //save number
	// Save IMG   

	$store_pic_array = array();
 		
	for ($i=1; $i<= get_option("wp_flash_img_show_pic_number"); $i++) {
		$url = "wp_flash_img_show_".$i."_url";
		$link = "wp_flash_img_show_".$i."_link";
		$description = "wp_flash_img_show_".$i."_description";
		$each_pic_array=array();
		$each_pic_array["url"]= get_option($url);
		$each_pic_array["link"] = get_option($link);
		$each_pic_array["description"] = get_option($description);
		$store_pic_array[$i]= $each_pic_array;
		
		// del old pic setting
		delete_option($url);
		delete_option($link);
		delete_option($description);
	}
	$wfis_array[$config_name]["pic"] =  $store_pic_array ; 
	
	//save option  			
	$store_option_array = array();	
	$option_names =   array("width","height","roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");
	$option_number = count($option_names) - 1;
	for ($i=0; $i<= $option_number ; $i++)
		{
		//config item  
		$itemnames = "wp_flash_img_show_".$option_names[$i] ;
		$store_option_array[$itemnames]	=  get_option($itemnames);
		
		// del old option
		delete_option($itemnames);
		}
	delete_option("wp_flash_img_show_pic_number"); // del number 
	$wfis_array[$config_name]["option"] =  $store_option_array ;
	update_option("wp_flash_img_show",$wfis_array);	
	wp_flash_img_show_save_to_xml($config_name); 
}