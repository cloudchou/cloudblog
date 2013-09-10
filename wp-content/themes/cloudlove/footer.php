
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
<div style="display:none">
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F50761bb5ef303074c39d254b24132503' type='text/javascript'%3E%3C/script%3E"));
</script>
</div>
		Copyright &copy; <?php bloginfo('name'); ?> |  Theme modifyed by Cloud  | Theme by TangStyle | Powered by WordPress.
	</div>

<?php wp_footer(); ?>
</body>

</html>
