<?php get_header(); ?>
	<div id="main">
		<?php if ( is_day() ) : ?>
			<h1 class="h1"><?php printf( __( 'Daily: %s' ), '<span>' . get_the_date() . '</span>' ); ?></h1>
		<?php elseif ( is_month() ) : ?>
			<h1 class="h1"><?php printf( __( 'Monthly: %s' ), '<span>' . get_the_date( 'F Y' ) . '</span>' ); ?></h1>
		<?php elseif ( is_year() ) : ?>
			<h1 class="h1"><?php printf( __( 'Yearly: %s' ), '<span>' . get_the_date( 'Y' ) . '</span>' ); ?></h1>
		<?php elseif ( is_tag() ) : ?>
			<h1 class="h1"><?php printf( __( 'Tag: %s' ), '<span>' . single_tag_title( '', false ) . '</span>' ); ?></h1>
		<?php else : ?>
			<h1 class="h1"><?php _e( 'Blog Archives' ); ?></h1>
		<?php endif; ?>
        
        <?php while ( have_posts() ) : the_post(); ?>
		<div class="post_list">
			<h2><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
            <div class="info"><?php the_author() ?> | <?php the_category(', ') ?> | <?php the_time('Y-m-d'); ?></div>
			<div class="excerpt">
				<?php if (has_post_thumbnail()) { ?><div class="thumbnail"><a href="<?php the_permalink() ?>"><?php the_post_thumbnail(); ?></a></div><?php } ?>
            	<?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 330,"..."); ?>
            	<span class="more">[<a href="<?php the_permalink() ?>" title="详细阅读 <?php the_title(); ?>" rel="bookmark">阅读全文</a>]</span>
            </div>
            <div class="meta">
                <span class="meat_span"><i class="iconfont">&#279;</i><?php if(function_exists(the_views)) { the_views('次浏览', true);}?></span>
                <span class="meat_span"><i class="iconfont">&#54;</i><?php comments_popup_link ('没有评论','1条评论','%条评论'); ?></span>
             	<span class="meat_span meat_max"><i class="iconfont">&#48;</i><?php the_tags('', ', ', ''); ?></span>
            </div>
		</div>
        <?php endwhile; ?>
		<div class="navigation"><?php pagination($query_string); ?></div>
	</div>
	<?php get_sidebar(); ?>
<?php get_footer(); ?>