
</div>
<p id="back-top" style="">
	<a href="#top"> <span></span> 回到顶部
	</a>
</p>
<script type="text/javascript">
$(document).ready(function(){
	if($(window).scrollTop()<=0){
		$('#back-top').hide();
	}	
	// fade in #back-top
	$(function () {		 
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#back-top').fadeIn();
			} else {
				$('#back-top').fadeOut();
			}
		});
		// scroll body to 0px on click
		$('#back-top a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});
});
</script>
</div>

<div id="footer">
	<div style="display: none">
		<script type="text/javascript">
			var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
			document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F50761bb5ef303074c39d254b24132503' type='text/javascript'%3E%3C/script%3E"));
		</script>
	</div>
	<div style="display: none;">
		<script type="text/javascript">
	 	var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000385810'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "v1.cnzz.com/z_stat.php%3Fid%3D1000385810' type='text/javascript'%3E%3C/script%3E"));
		</script>
	</div>
	<div style="display: none;">
   <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-51653888-1', 'cloudchou.com');
  ga('send', 'pageview');
</script> 
    </div>
		Copyright &copy; <?php bloginfo('name'); ?> |  Theme modifyed by Cloud  | Theme by TangStyle | Powered by WordPress.
	</div>

<?php wp_footer(); ?>
</body>

</html>
