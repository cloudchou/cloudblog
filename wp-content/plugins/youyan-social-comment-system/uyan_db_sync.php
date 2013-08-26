<?php 
@include '../wp-content/plugins/youyan-social-comment-system/link.php';
?>
<?php
if ( ($_COOKIE['UYEmail'] == '' && $_COOKIE['UYPassword'] == '' ) || !get_option('uyan_first')) {
	@include UYAN_SOURCE.'/uyan_plugin_admin.php';
} else {
	?>
<div style="width:auto; overflow-x:hidden;">
<div style="margin:10px 15px 0; border-bottom:1px solid #cdcdcd; padding-bottom:5px;"><a href="http://www.uyan.cc" target="_blank"><img src="<?php echo UYAN_SOURCE;?>/images/uyancc.png"></a>推荐IE8或更高级别浏览器管理</div>

	<div id="stepTwoWrapper" style=" width:900px; padding-left:101px;padding-top:10px; ">
		<div id="wpEdit" style="display:block;">
			<div class="imoportIntro">1. 数据同步设置</div>
			请自定义密匙，以便用于和友言数据同步时的数据对接密匙，同时以防第三方利用同步接口对您网站进行恶意操作。
			<br />
			<br />
			<div class="clear"></div>
			同步密钥：<input type="text" name="uyan_sync_token" id="uyan_sync_token" value='<?php echo get_option('uyan_sync_token'); ?>' style='width:125px;'>
			<div class="clear"></div>
			<input class="showCodeBTNApply" id="showCodeBTNApply_time_update" type="button" name="Submit" style="position: inherit; left:0px; top: 0; margin-top:10px;" value="保存设置" onclick="save();">
			<div class="clear"></div>
			<input type="hidden" value="<?php echo get_settings('home') ?>" id="url_base" name="url_base"><br>
			<hr>
			<div class="imoportIntro">2. 数据同步</div>
			友言为您提供Wordpress评论和友言评论数据双向同步，我们会将评论及时同步到本地数据库中，最大化的避免了评论数据的丢失。
			<br />
			提交数据同步申请后，友言服务器将在后端同步您的评论信息，其同步速度根据网络交互情况。
			<br />
			<br />
			<div class="clear"></div>
			<div class="importBTNWrapper" style="width:200px;float:left;">
				<a class='importBTN'  id="importBTN" onclick="sync(this)">同步评论数据</a>
			</div>
            <div class="clear"></div>
            
			<hr>
			<br />
			<div class="imoportIntro" style="color: red;">如果以上同步失败，比如由于网络通信原因，导致无法把友言评论导入到你的博客中，那么采用以下json数据导入方式：</div>
			<br />
			json文件请使用<span style="color: blue;">友言管理后台提供的数据备份功能</span>，下载导出的zip文件，解压后就是需要的json文件，<br />请您将uyan评论json数据文件放到/wp-content/plugins/youyan-social-comment-system/data/目录下命名为data.json即可。
			<br />
			然后点击下面的评论导入按钮，将会把指定数据导入到您的数据库中。<br /><br />
			因为导入的数据会严格的认证，避免产生垃圾数据，所以我们采用了一系列的控制措施：<br />
			1、json文件中域名和url要和你的博客域名一致，如果不一样，可以手动编辑json文件<br />
			2、json文件中的评论全部均来自wordpress系统嵌入uyan代码后产生的uyan评论，才允许导入到你的博客<br />
			<br />
			如果一下执行时间过长，建议你可以直接在服务器中运行脚本，方法如下：
			<br />
			先到uyan插件根目录，然后运行类似 php ./uyan_import.php 这样的命令，通过脚本方式导入，避免浏览器超时。
			<br />
			友情提示：注意导入前先备份好数据库comments表。
			<br />
			<br />
			<div class="clear"></div>
			<div class="importBTNWrapper" style="width:200px;float:left;">
				<a class='importBTN'  id="json_importBTN" onclick="json_import(this)">评论数据导入</a>
			</div>
            <div class="clear"></div>
            <div id="import_status" style="display:none;"></div>
		</div>
	</div>
	<script>
		function sync(node){
			var uyan_sync_token = encodeURIComponent($("#uyan_sync_token").val());
			$node = $(node);
			var exportNoti = $("#exportNoti");
			var text = '';
			$.ajax({
				url: "http://api.uyan.cc",
				dataType: 'jsonp',
				data: "mode=sync&act=sync&token="+uyan_sync_token+"&domain=<?php echo $_SERVER['HTTP_HOST'] ?>&uname=<?php echo $_COOKIE['UYEmail']; ?>&upass=<?php echo $_COOKIE['UYPassword'] ?>",
				jsonp: 'jsonp_callback',
				success: function(json) {
					json = json || {};
					$node.removeAttr('onclick');
					if (json.msg=='1'){
						$node.css({'background-image':'url(<?php echo UYAN_SOURCE?>/images/importDataPressed.png)','cursor':'default'});$node.html('请求已提交');
					}else if(json.msg=='2'){
						$node.css({'background-image':'url(<?php echo UYAN_SOURCE?>/images/importDataPressed.png)','cursor':'default','width':'179px','text-align:':'center'});$node.html('两小时内只能请求一次');
					}else if(json.msg == '3'){
						$node.css({'background-image':'url(<?php echo UYAN_SOURCE?>/images/importDataPressed.png)','cursor':'default'});$node.html('请输入密钥');
					}else{
						$node.css({'background-image':'url(<?php echo UYAN_SOURCE?>/images/importDataPressed.png)','cursor':'default'});$node.html('请求失败，请重试！');
					}
					setTimeout('$(".importBTN").css({"background-image":"url(<?php echo UYAN_SOURCE;?>/images/importData.png)","cursor":"pointer"}).html("同步评论数据").attr()',3000);
			document.getElementById("importBTN").setAttribute("onclick","sync(this)");

				},
				timeout: 3000
			});	
		}
																												
		function save(){
			$.ajax({
				url: "http://api.uyan.cc",
				dataType: 'jsonp',
				data: "mode=sync&act=setting&token="+encodeURIComponent($("#uyan_sync_token").val())+"&domain=<?php echo $_SERVER['HTTP_HOST'] ?>&uname=<?php echo $_COOKIE['UYEmail']; ?>&upass=<?php echo $_COOKIE['UYPassword'] ?>",
				jsonp: 'jsonp_callback',
				success: function(json) {
					json = json || {};
					if (json.msg == 3 && $("#uyan_sync_token").val() == '' ){
						$("#showCodeBTNApply_time_update").val("请输入密钥");
					}else if(json.msg == 1){
						$("#showCodeBTNApply_time_update").val("保存成功");
						saveSettings('time_update');
					}else if(json.msg == 0){
						$("#showCodeBTNApply_time_update").val("保存失败");
					}else if(json.msg == 4){
						$("#showCodeBTNApply_time_update").val("域名未验证");
					}
				},
				timeout: 3000
			});	
			setTimeout('$("#showCodeBTNApply_time_update").val("保存设置")',3000)
		}
		function json_import(obj){
			$node = $(obj);
			$node.css({'background-image':'url(<?php echo UYAN_SOURCE?>/images/importDataPressed.png)','cursor':'default'});
			$node.html("数据导入中……请稍后！");
			$.ajax({
			   type: "POST",
			   dataType: "json",
			   url: "../wp-content/plugins/youyan-social-comment-system/uyan_import.php",
			   success: function(data){
				 if(data.noFile == 1){
					 showhtml = "<span style='color:red'>打开数据文件失败，请上传json评论数据文件！</span>";
				 }else if(data.errArr == 1){
					 showhtml = "<span style='color:red'>不是有效的json评论数据文件！</span>";
				 }else{
					 showhtml = "<div style='float:left;'>数据成功导入<span style='color:red'>"+data.sucNum+"</span>条</div>";
					 if(data.errNum1 != 0){
						showhtml+="<div style='color:#000099;float:left;'>，域名不一致错误<span style='color:red'>"+data.errNum1+"</span>条</div>"
					 }
					 if(data.errNum2 != 0){
						showhtml+="<div style='color:#000099;float:left;'>，没有文章ID错误<span style='color:red'>"+data.errNum2+"</span>条</div>"
					 }
					 if(data.errNum3 != 0){
						showhtml+="<div style='color:#000099;float:left;'>，重复导入错误<span style='color:red'>"+data.errNum3+"</span>条</div>"
					 }
					 showhtml+='<div class="clear"></div>';					 
				 }
				 $("#import_status").show().html(showhtml).css({'color':'#009900'});
				 $node.css({"background-image":"url(<?php echo UYAN_SOURCE;?>/images/importData.png)","cursor":"pointer"}).html("评论数据导入")
			   }
			}); 
		}
																									
		function sel_it(obj){
			$("#"+obj).attr({"checked":true});
		}
	<?php if (get_option('uyan_use_orig') == 0 || get_option('uyan_use_orig') == '') { ?>
			$("input[name='UYUseOriginalChoose'][value='0']").attr("checked",true);

	<?php } else { ?>
			$("input[name='UYUseOriginalChoose'][value='1']").attr("checked",true);
	<?php } ?>		
	</script>

	<?php
}?>
</div>