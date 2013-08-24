</div>
<div id="totop" class="totop"><i class="iconfont">&#404;</i>回顶部</div>
<script type="text/javascript">
	$(window).scroll(function () {
        var dt = $(document).scrollTop();
        var wt = $(window).height();
        if (dt <= 0) {
            $("#totop").hide();
            return;
        }
        $("#totop").show();
        if ($.browser.msie && $.browser.version == 6.0) {//IE6返回顶部
            $("#totop").css("top", wt + dt - 110 + "px");
        }
    });
    $("#totop").click(function () { $("html,body").animate({ scrollTop: 0 }, 200) });
</script>
</div>

<div id="footer">
Copyright &copy; <?php echo get_option('tang_years'); ?> <?php bloginfo('name'); ?> | <a href="http://www.miibeian.gov.cn/" title="网站ICP备案" rel="external nofollow" target="_blank"><?php echo stripslashes(get_option('tang_icp')); ?></a> | <a href="http://www.penghui74.cn/sitemap.html" title="百度地图" target="_blank">百度地图</a> | <a href="http://www.penghui74.cn/sitemap.xml" title="谷歌地图" target="_blank">谷歌地图</a> | Theme by TangStyle | Powered by WordPress.
    <p>本网站资源来自网络和原创，如果侵犯了你的权益请联系我们，将在24小时内删除！</p>
    <p>
    <div class="bottomcopyright">
<center>今天是:<span><script language=Javascript type=text/Javascript> 
var day=""; 
var month=""; 
var ampm=""; 
var ampmhour=""; 
var myweekday=""; 
var year=""; 
mydate=new Date(); 
myweekday=mydate.getDay(); 
mymonth=mydate.getMonth()+1; 
myday= mydate.getDate(); 
myyear= mydate.getYear(); 
year=(myyear > 200) ? myyear : 1900 + myyear; 
if(myweekday == 0) 
weekday=" 星期日 "; 
else if(myweekday == 1) 
weekday=" 星期一 "; 
else if(myweekday == 2) 
weekday=" 星期二 "; 
else if(myweekday == 3) 
weekday=" 星期三 "; 
else if(myweekday == 4) 
weekday=" 星期四 "; 
else if(myweekday == 5) 
weekday=" 星期五 "; 
else if(myweekday == 6) 
weekday=" 星期六 "; 
document.write(year+"年"+mymonth+"月"+myday+"日 "+weekday); 
</script>
| <FONT COLOR="#FF0000">本站已经安全运行:</FONT></b> 
<span id="span_dt_dt"></span><b> 
<strong><script language="JavaScript" type="text/javascript">
var urodz= new Date("5/12/2013");
var now = new Date();
var ile = now.getTime() - urodz.getTime();
var dni = Math.floor(ile / (1000 * 60 * 60 * 24));
document.write(+dni)
</script>
</strong></b> 天
    </p>
    <p>
    <?php echo stripslashes(get_option('tang_tongji')); ?>
    </p>
</div>

<?php wp_footer(); ?>
</body>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jquery.orbit-1.2.3.min.js"></script>
<script type="text/javascript">
	$(window).load(function() {
		$('#featured').orbit();
	});
</script>
</html>