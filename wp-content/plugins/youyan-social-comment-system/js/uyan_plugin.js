var UYUserName;
var UYUserID;
function regenerateCode(UYUserID){
  var retCode = '<!-- UYAN BEGIN -->\n<div id=uyan_frame></div>\n<script type=text/javascript id="UYScript" async="" src="http://v1.uyan.cc/js/iframe.js?UYUserId='+UYUserID + '"></script>\n<!-- UYAN END -->';
  return retCode;
}

function changeTag($state,$node){
  if($state===null){
    if($node.next().attr("class")!="errorInput"){
      if($node.next().attr("class")=="correctInput"){
        $node.next().remove(); 
      }
      $node.after('<div class="errorInput"></div>');
    }	
  }else{
    if($node.next().attr("class")!="correctInput"){
      if($node.next().attr("class")=="errorInput"){
        $node.next().remove();
      }
      $node.after('<div class="correctInput"></div>');
    }	  
  }	
}

function checkUserName(){
  // var $targetName = $("#inputUserName").val();
  var $targetName = 'wordpress';
  var length = $targetName.replace(/[^\x00-\xff]/g,"**").length;  
  if(length<=0||length>20){
    var $state = null;
  }else{
    var $state = true;
  }
  changeTag($state,$("#inputUserName"));
  return $state;
}


function checkPassword(){
  var $targetPS = $("#inputPassword").val();
  var length =$targetPS.length;  
  if(length<6||length>32){
    var $state = null;
  }else{
    var $state = true;
  }
  changeTag($state,$("#inputPassword"));	
  return $state;
}

function checkEmail(){
  var $targetEmail = $("#inputEmail").val();
  var $state =  $targetEmail.match(/^[a-zA-Z0-9\_\-\.]+@([a-zA-Z0-9\-\.])*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/);  
  changeTag($state,$("#inputEmail"));
  return $state;
}

function setCookie(c_name,value,expiredays){
  var exdate=new Date();
  exdate.setDate(exdate.getDate()+expiredays);
  document.cookie=c_name+ "=" +escape(value)+ ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function getCookie(objName){//获取指定名称的cookie的值
	var arrStr = document.cookie.split("; ");
	for(var i = 0;i < arrStr.length;i ++){
		var temp = arrStr[i].split("=");
		if(temp[0] == objName) return unescape(temp[1]);
	}
}

function UYAutoLogin(email,loginPassword,page){
	var rem = 1;
	if(email!=''&&loginPassword!=''){
    $.getJSON(
		"http://www.uyan.cc/index.php/youyan_login/userAutoLoginCrossDomain?callback=?",{
		email: email,
		loginPassword:loginPassword,
		rem:rem,
		domain: domain
        },
		
        function(data){
          if(data =='noData'){
          }else{
        	   UYUserID = data.uid;
			   UYUserName = data.uname;
			   if(!uyan_src){
				   $.post("?page=uyan_bind", {
						update_option:'default',
						UYUserID: UYUserID,
						UYUserName:UYUserName,
						sourceCode: regenerateCode(UYUserID)
					});
			   }
			   if(!uyan_first){
				   if((!getCookie('UYEmail') && !getCookie('UYPassword') && !uyan_first) || (getCookie('UYEmail') && getCookie('UYPassword') || !uyan_first)){
						$.post("?page=uyan_bind", {
							update_option:'uyan_first'
						})
			   }
			   
			}
            UYUserID = data.uid;
            UYUserName = data.uname;
            $(".innerContainer").remove();
            $(".contentWrapper").load(source_src+"/uyan_setting.php?page="+page); 
          }
        }
    );
  }
}

function submitLogin( url ){
	var email = $("#email").val();
	var loginPassword = $("#password").val();	
	
	var rem = 1;
	var state =  email.match(/^[a-zA-Z0-9\_\-\.]+@([a-zA-Z0-9\-\.])*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/);
	$("#alertLogin").html("");
	$(".loginBTNPane").html("加载中");

	if(state&&loginPassword!=''){
    $.getJSON(
		"http://www.uyan.cc/index.php/youyan_login/userLoginCrossDomain?callback=?",{
		email: email,
		loginPassword:loginPassword,
		rem:rem,
		domain: domain
        },
        function(data){
          if(data =='noData'){
            $("#alertLogin").html("您输入的邮箱或密码有误");
            $(".loginBTNPane").html("登录");
          }
          else{
			   UYUserID = data.uid;
				UYUserName = data.uname;
				$.post("?page=uyan_bind", {
					update_option:'default',
					UYUserID: UYUserID,
					UYUserName:UYUserName,
					sourceCode: regenerateCode(UYUserID)
				});
			  if((!getCookie('UYEmail') && !getCookie('UYPassword') && !uyan_first) || (getCookie('UYEmail') && getCookie('UYPassword') || !uyan_first)){
				 setCookie('UYEmail',email,30);
				 setCookie('UYPassword',hex_md5(loginPassword),30);

				$.post("?page=uyan_bind", {
					update_option:'uyan_first'
				},function (){
					window.location.href=domain+"/wp-admin/admin.php?page=uyan_db_sync";	
				});
			  }else{
				 setCookie('UYEmail',email,30);
				 setCookie('UYPassword',hex_md5(loginPassword),30);
				  $(".innerContainer").remove();
				  $(".contentWrapper").load(source_src+"/uyan_setting.php?page="+url);
				  window.location.reload();window.location.reload();
				 $(".loginBTNPane").html("登录成功");
			  }
          }
        });
  }
  else{
    $("#alertLogin").html("请输入格式正确的邮箱及密码");
    $(".loginBTNPane").html("登录");
  }
}

function setCookie(c_name,value,expiredays){
  var exdate=new Date();
  exdate.setDate(exdate.getDate()+expiredays);
  document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function submitSignup( url ){
	if(checkUserName() !==null &&checkEmail() !==null &&checkPassword() !==null){
    var targetEmail = $("#inputEmail").val();
    var targetPS = $("#inputPassword").val();
    var targetName = $("#inputUserName").val();
    var targetURL = $("#inputURL").val();
    $("#signupBTNPane").attr("disabled","disabled");
    $("#signupBTNPane").html("提交中");		

	$.getJSON("http://www.uyan.cc/index.php/youyan_login/signupMasterCrossDomain?callback=?",{
          targetEmail:targetEmail,
		  targetPS:targetPS,
		  targetName:targetName,
		  targetURL:domain
        },
        function(data){
          if(data == '0'){
            $("#inputEmail").val("[此邮箱已经注册]");
            checkEmail();
            $("#signupBTNPane").removeAttr("disabled");
            $("#signupBTNPane").html("确定");
            $("#signupBTNPane").removeAttr("onclick");
            $("#signupBTNPane").attr("onclick",function(){return function(){submitSignup( url );}});
			return false;
          }else{
            UYUserID = data.uid;
            UYUserName = data.uname;
            
           $("#signupBTNPane").html("成功");
            $(".innerContainer").remove();
            $.post(".", {
				UYUserID: UYUserID,
				UYUserName:UYUserName,
				sourceCode: regenerateCode(UYUserID)
                });
          }
		   if((!getCookie('UYEmail') && !getCookie('UYPassword') && !uyan_first) || (getCookie('UYEmail') && getCookie('UYPassword') || !uyan_first)){
				 setCookie('UYEmail',targetEmail,30);
				 setCookie('UYPassword',hex_md5(targetPS),30);

				$.post("?page=uyan_bind", {
					update_option:'uyan_first'
				},function (){
					window.location.href=domain+"/wp-admin/admin.php?page=uyan_db_sync";	
				});
			  }else{
				 setCookie('UYEmail',targetEmail,30);
				 setCookie('UYPassword',hex_md5(targetPS),30);
				 $(".contentWrapper").load(source_src+"/uyan_setting.php?page="+url);
				 window.location.reload();
			  }
        });
  }
}
var hexcase = 0; 
var b64pad  = "";
var chrsz   = 8; 

function hex_md5(s){ return binl2hex(core_md5(str2binl(s), s.length * chrsz));}  
function b64_md5(s){ return binl2b64(core_md5(str2binl(s), s.length * chrsz));}  
function str_md5(s){ return binl2str(core_md5(str2binl(s), s.length * chrsz));}  
function hex_hmac_md5(key, data) { return binl2hex(core_hmac_md5(key, data)); }  
function b64_hmac_md5(key, data) { return binl2b64(core_hmac_md5(key, data)); }  
function str_hmac_md5(key, data) { return binl2str(core_hmac_md5(key, data)); }  


function md5_vm_test()  
{  
  return hex_md5("abc") == "900150983cd24fb0d6963f7d28e17f72";  
}  


function core_md5(x, len)  
{  

  x[len >> 5] |= 0x80 << ((len) % 32);  
  x[(((len + 64) >>> 9) << 4) + 14] = len;  

  var a =  1732584193;  
  var b = -271733879;  
  var c = -1732584194;  
  var d =  271733878;  

  for(var i = 0; i < x.length; i += 16)  
  {  
    var olda = a;  
    var oldb = b;  
    var oldc = c;  
    var oldd = d;  

    a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);  
    d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);  
    c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);  
    b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);  
    a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);  
    d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);  
    c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);  
    b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);  
    a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);  
    d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);  
    c = md5_ff(c, d, a, b, x[i+10], 17, -42063);  
    b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);  
    a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);  
    d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);  
    c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);  
    b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);  

    a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);  
    d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);  
    c = md5_gg(c, d, a, b, x[i+11], 14,  643717713);  
    b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);  
    a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);  
    d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);  
    c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);  
    b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);  
    a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);  
    d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);  
    c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);  
    b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);  
    a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);  
    d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);  
    c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);  
    b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);  

    a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);  
    d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);  
    c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562);  
    b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);  
    a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);  
    d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);  
    c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);  
    b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);  
    a = md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);  
    d = md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);  
    c = md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);  
    b = md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);  
    a = md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);  
    d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);  
    c = md5_hh(c, d, a, b, x[i+15], 16,  530742520);  
    b = md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);  

    a = md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);  
    d = md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);  
    c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);  
    b = md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);  
    a = md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);  
    d = md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);  
    c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);  
    b = md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);  
    a = md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);  
    d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);  
    c = md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);  
    b = md5_ii(b, c, d, a, x[i+13], 21,  1309151649);  
    a = md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);  
    d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);  
    c = md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);  
    b = md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);  

    a = safe_add(a, olda);  
    b = safe_add(b, oldb);  
    c = safe_add(c, oldc);  
    d = safe_add(d, oldd);  
  }  
  return Array(a, b, c, d);  

}  


function md5_cmn(q, a, b, x, s, t)  
{  
  return safe_add(bit_rol(safe_add(safe_add(a, q), safe_add(x, t)), s),b);  
}  
function md5_ff(a, b, c, d, x, s, t)  
{  
  return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);  
}  
function md5_gg(a, b, c, d, x, s, t)  
{  
  return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);  
}  
function md5_hh(a, b, c, d, x, s, t)  
{  
  return md5_cmn(b ^ c ^ d, a, b, x, s, t);  
}  
function md5_ii(a, b, c, d, x, s, t)  
{  
  return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);  
}  

function core_hmac_md5(key, data)  
{  
  var bkey = str2binl(key);  
  if(bkey.length > 16) bkey = core_md5(bkey, key.length * chrsz);  

  var ipad = Array(16), ōpad = Array(16);  
  for(var i = 0; i < 16; i++)  
  {  
    ipad[i] = bkey[i] ^ 0x36363636;  
    opad[i] = bkey[i] ^ 0x5C5C5C5C;  
  }  

  var hash = core_md5(ipad.concat(str2binl(data)), 512 + data.length * chrsz);  
  return core_md5(opad.concat(hash), 512 + 128);  
}  


function safe_add(x, y)  
{  
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);  
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);  
  return (msw << 16) | (lsw & 0xFFFF);  
}  

function bit_rol(num, cnt)  
{  
  return (num << cnt) | (num >>> (32 - cnt));  
}  


function str2binl(str)  
{  
  var bin = Array();  
  var mask = (1 << chrsz) - 1;  
  for(var i = 0; i < str.length * chrsz; i += chrsz)  
    bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (i%32);  
  return bin;  
}  

function binl2str(bin)  
{  
  var str = "";  
  var mask = (1 << chrsz) - 1;  
  for(var i = 0; i < bin.length * 32; i += chrsz)  
    str += String.fromCharCode((bin[i>>5] >>> (i % 32)) & mask);  
  return str;  
}  


function binl2hex(binarray)  
{  
  var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";  
  var str = "";  
  for(var i = 0; i < binarray.length * 4; i++)  
  {  
    str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +  
      hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);  
  }  
  return str;  
}  


function binl2b64(binarray)  
{  
  var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";  
  var str = "";  
  for(var i = 0; i < binarray.length * 4; i += 3)  
  {  
    var triplet = (((binarray[i   >> 2] >> 8 * ( i   %4)) & 0xFF) << 16)  
      | (((binarray[i+1 >> 2] >> 8 * ((i+1)%4)) & 0xFF) << 8 )  
      |  ((binarray[i+2 >> 2] >> 8 * ((i+2)%4)) & 0xFF);  
    for(var j = 0; j < 4; j++)  
    {  
      if(i * 8 + j * 6 > binarray.length * 32) str += b64pad;  
      else str += tab.charAt((triplet >> 6*(3-j)) & 0x3F);  
    }  
  }  
  return str;  
} 