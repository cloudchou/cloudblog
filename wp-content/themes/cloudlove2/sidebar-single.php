<div id="sidebar">    
    <div class="sidebar">
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('内容页侧栏') ) : ?>
	<div id="related_post" class="widget">
		<h3><?php $category = get_the_category(); echo $category[0]->cat_name; ?> <span class="h3small">下的最新文章</span></h3>
		<?php
    		if(is_single()){
    	        $cats = get_the_category();
    	    }
    	       foreach($cats as $cat){
    	        $posts = get_posts(array(
        	        'category' => $cat->cat_ID,
            	    'exclude' => $post->ID,
                	'showposts' => 5,
             	));
			echo '<ul>';
			foreach($posts as $post){
				echo '<li><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></li>';
        	}
			echo '</ul>';
	        }
		?>
	</div>
    <div class="widget">
		<h3>随机文章</h3>
		<ul>
    		<?php $rand_posts = get_posts('numberposts=6&orderby=rand');  foreach( $rand_posts as $post ) : ?>
    		<li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php echo mb_strimwidth(get_the_title(), 0, 42, '...'); ?></a></li>
    		<?php endforeach; ?>
		</ul>
	</div>
	<!-- 
    <div class="widget">
		<h3>标签云</h3>
        <div class="tagcloud"><?php wp_tag_cloud('smallest=12&largest=18&unit=px&number=50&orderby=count&order=RAND');?></div>
	</div>
    <?php endif; ?>
     -->
    <div class="widget">
    </div>

    </div>
</div>