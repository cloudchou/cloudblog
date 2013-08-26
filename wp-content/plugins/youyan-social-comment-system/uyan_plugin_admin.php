<div style="margin:10px 15px 0;  background:#FFFFFF; border-bottom:1px solid #cdcdcd; padding-bottom:5px;"><a href="http://www.uyan.cc" target="_blank"><img src="../wp-content/plugins/youyan-social-comment-system/images/uyancc.png"></a>推荐IE8或更高级别浏览器管理</div>
<?php
@include '../wp-content/plugins/youyan-social-comment-system/link.php';
if($_COOKIE['UYEmail'] == '' || $_COOKIE['UYPassword'] == '') {
?>
    <div class="innerContainer">
           <div class="contentTitle"><a class="unselectedTab currentTab" id="loginUyan" onclick="$('#loginUyan').addClass('currentTab');$('#signupUyan').removeClass('currentTab');$('#loginpart').css({'display':'block'});$('#signuppart').css({'display':'none'});">登录友言</a><a class="unselectedTab" onclick="$('#signupUyan').addClass('currentTab');$('#loginUyan').removeClass('currentTab');$('#loginpart').css({'display':'none'});$('#signuppart').css({'display':'block'});" id="signupUyan">注册</a><div class="clear"></div></div>
           <div id="loginpart">
                <div class="detailTitle">Email</div>
                <div class="inputURLWrapper inputURLWS">
                <input type="text" id="email" name="email" />
                <div class="clear"></div></div>
                <div class="detailTitle">密码</div>
                <div class="inputURLWrapper inputURLWS">
                <input type="password" id="password" name="password" />  
                <div class="clear"></div></div>
                <div class="submitWrapper">
                    <span id="alertLogin"></span><a class="loginBTNPane" onclick="submitLogin('<?php echo $_GET['page'];?>')">登录</a>
                    <div class="clear"></div>
                </div>        
                <div class="clear"></div>  
           </div>
           <div id="signuppart">
        <!--<div class="inputUpTag">用户名</div>-->
        <div style="display:none;" class="inputURLWrapper inputURLWS"><input type="text" id="inputUserName" name="inputUserName" class="inputURLS" onblur="checkUserName()"  /><div class="clear"></div></div>
        <div class="inputUpTag">Email</div>
        <div class="inputURLWrapper inputURLWS"><input type="text" id="inputEmail" name="inputEmail" class="inputURLS" onblur="checkEmail()"  /><div class="clear"></div></div>     
        <div class="inputUpTag">密码<span class="psAlert">(至少6位字符)</span></div>
        <div class="inputURLWrapper inputURLWS"><input type="password" id="inputPassword" name="inputPassword" class="inputURLS" onblur="checkPassword()"/><div class="clear"></div></div>       
        <script language="javascript">$("#inputUserName").val("");$("#inputEmail").val("");</script>
        <a id='signupBTNPane' onClick="submitSignup('<?php echo $_GET['page'];?>')">确定</a><div class="clear"></div>
                </div>
           <div class="introWhat">友言，为您的博客量身打造社会化评论系统。</div>
           <div class="introWhy">(您也可以到http://www.uyan.cc注册与管理数据)</div>
                <div class="clear"></div>  
           </div>
       </div>        
<?php
}else{
?>
<div class="contentWrapper" id="contentWrapper">
 <div style="position:absolute; top:240px; left:500px;">
  <img src="../wp-content/plugins/youyan-social-comment-system/images/uy_loading.gif" />
 </div>
</div>
    <script>
    UYAutoLogin('<?php echo $_COOKIE['UYEmail']?>','<?php echo $_COOKIE['UYPassword']?>', '<?php echo $_GET['page']?>');
    </script>
<?php
}
?>