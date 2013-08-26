<div id="stepTwoWrapper" style="margin-left:-20px; margin-top:-4px;"></div>

<script type="text/javascript">
<?php if($_GET['page'] == 'uyan_analysis') {?>
var pre = "analysis/user";
<?php } else if($_GET['page'] == 'uyan_setting') { ?>
var pre = "setting";
<?php }else{?>
var pre = 'comment';
<?php }?>
var url = "http://www.uyan.cc/"+pre+"?iframe=1&dm="+document.domain
var iframe = document.createElement('iframe');
iframe.src=url;
document.getElementById("contentWrapper").innerHTML='';
document.getElementById("contentWrapper").appendChild(iframe);
</script>