function userLogout(){
	 $.ajax({
		type:"POST",
		url:"http://www.uyan.cc/index.php/youyan_login/userLogout",
		data:{
		},
		dataType:"json",
		cache:false,
		success: function(data){
			location.href="http://www.uyan.cc";
		},
		error:function(){
			alert("由于网络不稳定,登录失败,请稍候再试。");
		}
	  });	
}
