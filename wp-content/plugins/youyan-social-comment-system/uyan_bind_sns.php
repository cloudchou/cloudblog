<?php 
@include '../wp-content/plugins/youyan-social-comment-system/link.php';
?>
<?php
if($_COOKIE['UYEmail'] == '' && $_COOKIE['UYPassword'] == ''){
	@include UYAN_SOURCE.'/uyan_plugin_admin.php';
}else{
?>

<div style="height:550px;  width:auto;overflow-x:hidden;">
<div style="margin:10px 15px 0; border-bottom:1px solid #cdcdcd; padding-bottom:5px;"><a href="http://www.uyan.cc" target="_blank"><img src="<?php echo UYAN_SOURCE;?>/images/uyancc.png"></a>推荐IE8或更高级别浏览器管理</div>
<div id="stepTwoWrapper" style="width:900px;  padding-left:101px;">
<!-- sns part-->
<div>
<style>
.media_name{
	width:90%;
	height:30px;
	line-height:30px;
	border:1px solid #f4f4f4;
	margin:0;
	padding:0;
	list-style:none;
	padding-left:25px;
	background:#f4f4f4;
	border-bottom:none;
}
.media_name li.curr{
	float:left;	
	width:90px;
	height:29px;
	background:#fff;
	text-align:center;
	margin-top:2px;
	font-weight:bold;
	cursor:pointer;
}
.media_name li.normal{
	float:left;	
	width:90px;
	margin-top:2px;
	height:30px;
	text-align:center;
	cursor:pointer;
}
</style>
<!--     
<ul class="media_name"> 
        <li class="curr"  id="nav_sina" onclick="show_media('sina')">新浪微博</li>
        <li class="normal"  id="nav_tencent" onclick="show_media('tencent')">腾讯微博</li>
        <li class="normal"  id="nav_t163" onclick="show_media('t163')">网易微博</li>
        <li class="normal"  id="nav_tsohu" onclick="show_media('tsohu')">搜狐微博</li>
    </ul> -->

<script>
	var media_arr = ['sina','tencent','t163','tsohu']
	function show_media(media_name){
		for(k in media_arr){
			if(media_arr[k] == media_name){
				$("#media_"+media_name).css({"display":"block"});
				$("#nav_"+media_name).addClass("curr");
			}else{
				$("#media_"+media_arr[k]).css({"display":"none"});
				$("#nav_"+media_arr[k]).removeClass().addClass("normal");
			}
		}
		$(this).addClass('curr');
	}
</script>
    <div style="clear:both;"></div>
        <div class="sinaBindContainer" id="media_sina">
        <p >
        <span style="color: #BC0B0B;">新发布的文章将同步到SNS中，并且SNS评论信息也能及时同步到评论系统中</span>，绑定SNS后，您将在文章发布页右侧看到同步选项：
        </p>
         	<div style="margin-top:15px;">1、填写你的微博应用密钥<div style="margin-top:10px;"></div>如何获取APP KEY与SECRET？点击
         	<a href="http://open.weibo.com/development" target="_blank">新浪</a>
         	<a href="http://dev.open.t.qq.com/developer" target="_blank">腾讯</a>
         	<a href="http://open.t.163.com" target="_blank">网易</a>
         	<a href="http://open.t.sohu.com" target="_blank">搜狐</a>
         	开发平台，然后创建应用，请在审核通过后使用APPKEY与SECRET。</div><br>
         	<div >2、绑定/解绑你需要同步博客文章到微博的账户</div><br />
         	<br />
         	<!-- tsina -->
          <div class="clear"></div>
        	<div style="width:550px;">
         	 <div style=" width:430px; float:left;"> 
            <div style=" font-size:15px; font-weight:bold; float:left; margin-right:25px;">新浪微博</div>
            <div style=" float:left;">
            <div class="inputAPPWrapper">
                <div class="inputAPPTitle">APP Key</div>
                <input type="text" name="appkey" value="<?php echo get_option('UYAN_SINA_APP_KEY')?>" id="appkey" class="APPInput" />
                <div class="clear"></div>
            </div>
           
            <div class="inputAPPWrapper">
                <div class="inputAPPTitle">Secret</div>
                <input type="text" name="secret" value="<?php echo get_option('UYAN_SINA_APP_SECRET')?>"id="secret" class="APPInput" />
                <div class="clear"></div>
            </div>
            <div class="submitAPPWrapper">
                <input type="submit" name="submitAPP" id="submitAPP" value="保&nbsp;&nbsp;存" onclick="saveSinaAPPKEY('<?php echo get_option('UYAN_SINA_APP_KEY')?>','<?php echo get_option('UYAN_SINA_APP_SECRET')?>');"/>
            </div>
            <div id='sina_expire' style=" margin-top:-3px; margin-left:25px;float:left; color:#f00;<?php if(get_option('SINA_EXPIRES_iN') && time() > get_option('SINA_EXPIRES_iN') ){echo 'display:block;';}else{echo 'display:none;';}?>">新浪微博授权已过期</div>
          </div></div>
            </div>
          <div id="connectWrapperConnected" class="sina_connected" style="float:left; margin-top:5px;">
              <a class="connectBTN unconnectSINA" onclick="unBindMasterSinaToDomain()" title="取消绑定新浪微博"></a>
              <div class="clear"></div>
          </div>
  <style>
  #connectWrapperConnected{<?php if(time() > get_option('SINA_EXPIRES_iN')){echo 'display:none;';}else{echo 'display:block;';}?>}
  .sina_connected{<?php if(time() > get_option('SINA_EXPIRES_iN')){echo 'display:none;';}else{echo 'display:block;';}?>}
  </style>          
          
          <div class="connectWrapper" id="connectWrapper" style="float: left; margin-top:5px;<?php if(get_option('SINA_EXPIRES_iN')){echo 'display:none;';}else{echo 'display:block;';}?>  ">
              <a class="connectBTN connectSINA" onclick="bindMasterSinaToDomain('<?php echo get_option('UYAN_SINA_APP_KEY')?>','<?php echo get_option('UYAN_SINA_APP_SECRET')?>')" title="绑定新浪微博"></a>
		  <div class="clear"></div>
		</div>
				<div class="connectWrapper" id="connectWrapper_expire" style="float: left; margin-top:5px;<?php if(get_option('SINA_EXPIRES_iN') && time() > get_option('SINA_EXPIRES_iN') ){echo 'display:block;';}else{echo 'display:none;';}?> ">
              <a class="connectBTN re_connectSINA" onclick="bindMasterSinaToDomain('<?php echo get_option('UYAN_SINA_APP_KEY')?>','<?php echo get_option('UYAN_SINA_APP_SECRET')?>')" title="重新绑定"></a>
		  <div class="clear"></div>
		</div>
       </div>
       
</div>
               
		<!-- tencent-->
         <div class="clear"></div>
         <div class="tencentBindContainer" id="media_tencent" style="margin-top:45px;" >

         	<div style="width:550px;">
         	 <div style=" width:430px; float:left;">
         <div style=" font-size:15px; font-weight:bold; float:left; margin-right:25px;">腾讯微博</div>
            <div style=" float:left;">

             <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">APP Key</div>
                  <input type="text" name="appkey" id="tencent_appkey" value="<?php echo get_option('UYAN_TENCENT_APP_KEY')?>" class="APPInput" />
                  
                  <div class="clear"></div>
              </div>

              <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">Secret</div>
                  <input type="text" name="secret" value="<?php echo get_option('UYAN_TENCENT_APP_SECRET')?>" id="tencent_secret" class="APPInput" />
                  
                  <div class="clear"></div>
              </div>

              <div class="submitAPPWrapper">
                  <input type="submit" name="submitAPP" id="submitAPPTencent" value="保&nbsp;&nbsp;存" onclick="saveTencentAPPKEY('<?php echo get_option('UYAN_TENCENT_APP_KEY')?>','<?php echo get_option('UYAN_TENCENT_APP_SECRET')?>');"/>
                  <div class="clear"></div>
              </div>
              </div></div></div>
              <div id="connectWrapperConnectedTencent" style="float:left; margin-top:5px;">
                  <a class="connectBTN unconnectTencent" onclick="unBindMasterTencentToDomain()" title="取消绑定腾讯微博"></a>
                  <div class="clear"></div>
              </div>

              <div class="connectWrapper" id="connectWrapperTencent" style="float:left; margin-top:5px;">
                  <a class="connectBTN connectTencent" onclick="bindMasterTencentToDomain('<?php echo get_option('UYAN_TENCENT_APP_KEY')?>','<?php echo get_option('UYAN_TENCENT_APP_SECRET')?>')" title="绑定腾讯微博"></a>
                  <div class="clear"></div>
              </div>
       </div>
          
          <!-- t163 -->
       <div class="clear"></div>    
     <div class="t163BindContainer" id="media_t163" style="margin-top:45px;">
          	<div style="width:550px;">
         	 <div style=" width:430px; float:left;">
         <div style=" font-size:15px; font-weight:bold; float:left; margin-right:25px;">网易微博</div>
            <div style=" float:left;">
              <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">APP Key</div>
                  <input type="text" name="appkey" id="t163_appkey" value="<?php echo get_option('UYAN_T163_APP_KEY')?>" class="APPInput" />
                  <div class="clear"></div>
              </div>

              <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">Secret</div>
                  <input type="text" name="secret" value="<?php echo get_option('UYAN_T163_APP_SECRET')?>" id="t163_secret" class="APPInput" />
                  
                  <div class="clear"></div>
              </div>

              <div class="submitAPPWrapper">
                  <input type="submit" name="submitAPP" id="submitAPPT163" value="保&nbsp;&nbsp;存" onclick="saveT163APPKEY('<?php echo get_option('UYAN_T163_APP_KEY')?>','<?php echo get_option('UYAN_T163_APP_SECRET')?>');"/>
                  <div class="clear"></div>
              </div>
              </div>  </div> </div>
              <div id="connectWrapperConnectedT163" style="float:left; margin-top:5px;">
                  <a class="connectBTN unconnectT163" onclick="unBindMasterT163ToDomain()" title="取消绑定网易微博"></a>
                  <div class="clear"></div>
              </div>

              <div class="connectWrapper" id="connectWrapperT163" style="float:left; margin-top:5px;">
                  <a class="connectBTN connectT163" onclick="bindMasterT163ToDomain('<?php echo get_option('UYAN_T163_APP_KEY')?>','<?php echo get_option('UYAN_T163_APP_SECRET')?>')" title="绑定网易微博"></a>
                  <div class="clear"></div>
              </div>
          </div>
       <!--tsohu-->
     <div class="clear"></div>    
    <div class="t163BindContainer" id="media_tsohu" style="margin-top:45px;" >
          	<div style="width:550px;">
         	 <div style=" width:430px; float:left;">
         <div style=" font-size:15px; font-weight:bold; float:left; margin-right:25px;">搜狐微博</div>
            <div style=" float:left;">
              <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">APP Key</div>
                  <input type="text" name="appkey" id="tsohu_appkey" value="<?php echo get_option('UYAN_TSOHU_APP_KEY')?>" class="APPInput" />
                  
                  <div class="clear"></div>
              </div>

              <div class="inputAPPWrapper">
                  <div class="inputAPPTitle">Secret</div>
                  <input type="text" name="secret" id="tsohu_secret" value="<?php echo get_option('UYAN_TSOHU_APP_SECRET')?>"  class="APPInput" />
                  
                  <div class="clear"></div>
              </div>
              <div class="submitAPPWrapper">
                  <input type="submit" name="submitAPP" id="submitAPPTSOHU" value="保&nbsp;&nbsp;存" onclick="saveTSOHUAPPKEY('<?php echo get_option('UYAN_TSOHU_APP_KEY')?>','<?php echo get_option('UYAN_TSOHU_APP_SECRET')?>');"/>
                  <div class="clear"></div>
              </div>
              </div>
              </div>
              </div>
              <div class="connectWrapper"  id="connectWrapperConnectedTSOHU" style="float:left; margin-top:5px; display:none;">
                  <a class="connectBTN connectTSOHU" onclick="unBindMasterTSOHUToDomain()" title="取消绑定搜狐微博"></a>
               <div class="clear"></div>
              </div>

              <div class="connectWrapper" id="connectWrapperTSOHU" style="float:left; display:block; margin-top:5px;">
                  <a class="connectBTN unconnectTSOHU" onclick="bindMasterTSOHUToDomain('<?php echo get_option('UYAN_TSOHU_APP_KEY')?>','<?php echo get_option('UYAN_TSOHU_APP_SECRET')?>')" title="绑定搜狐微博"></a>
                  <div class="clear"></div>
              </div>
              </div>
          </div>
          <div style="height:100px; "></div>
         <div id="changeToConnected"></div> 
     <input type="hidden" value="" name="UYUserID" id="UYUserID" />
    <div class="clear"></div>
</div>
</div>
<script type="text/javascript">
var time = '<?php echo time(); ?>'
var SINA_EXPIRES_iN = '<?php echo get_option('SINA_EXPIRES_iN');?>'
if(time > SINA_EXPIRES_iN){
	 document.getElementById("connectWrapperConnected").style.display="none";
	 document.getElementById("connectWrapperConnected").style.border="1px"
}


if('<?php echo get_option('uyan_has_binded_sina')?>' == 1){
  document.getElementById("changeToConnected").style.display="none";
  document.getElementById("connectWrapper").display="none";
  document.getElementById("connectWrapperConnected").style.display="block";
}

if('<?php echo get_option('uyan_has_binded_tencent')?>' == 1){
  document.getElementById("changeToConnected").style.display="none";
  document.getElementById("connectWrapperTencent").style.display="none";
  document.getElementById("connectWrapperConnectedTencent").style.display="block";
}

if('<?php echo get_option('uyan_has_binded_t163')?>' == 1){
  document.getElementById("changeToConnected").style.display="none";
  document.getElementById("connectWrapperT163").style.display="none";
  document.getElementById("connectWrapperConnectedT163").style.display="block";
}

if('<?php echo get_option('uyan_has_binded_tsohu')?>' == 1){
  document.getElementById("changeToConnected").style.display="none";
  document.getElementById("connectWrapperTSOHU").style.display="none";
  document.getElementById("connectWrapperConnectedTSOHU").style.display="block";
}

</script>
<?php }?>
</div>