<div id="sidebar">
	<div class="sidebar">
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('分类页侧栏') ) : ?>
		<!-- 
	    <div class="widget">
			<h3><?php $category = get_the_category();echo $category[0]->cat_name;?><font size="2">文章</font></h3>
			<ul>
			<?php
		$cat = get_the_category ();
		$cat_id = $cat [0]->cat_ID;
		query_posts ( 'order=asc&cat=' . $cat_id . '&numberposts=10&orderby=rand' );
		while ( have_posts () ) :
			the_post ();
			?>    		 
    		<li><a href="<?php the_permalink(); ?>"
					title="<?php the_title(); ?>"><?php echo mb_strimwidth(the_title(), 0, 42, '...'); ?></a>
				</li>    	 
    		<?php  endwhile;wp_reset_query(); ?>
		</ul>
		</div>
		 -->
		
		<div class="widget">
			<h3>随机文章</h3>
			<ul>
    		<?php $rand_posts = get_posts('numberposts=10&orderby=rand');  foreach( $rand_posts as $post ) : ?>
    		<li><a href="<?php the_permalink(); ?>"
					title="<?php the_title(); ?>"><?php echo mb_strimwidth(get_the_title(), 0, 42, '...'); ?></a></li>
    		<?php endforeach; ?>
		</ul>
		</div>
		<!-- 
		<div class="widget">
			<h3>标签云</h3>
			<div class="tagcloud"><?php wp_tag_cloud('smallest=12&largest=18&unit=px&number=50&orderby=count&order=RAND');?></div>
		</div>
		 -->
	<?php endif; ?>
    </div>
</div>