<?php get_header(); ?>
	<div class="errors_404">
    <a href="/" class="to_home">  </a>
    <a href="javascript:history.go(-1)" class="to_back">  </a>
	</div>
	<meta http-equiv="refresh" content="5;url=<?php echo get_option('home') ?>">
<?php get_footer(); ?>
