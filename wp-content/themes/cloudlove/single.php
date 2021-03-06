<?php get_header(); ?>
<div id="main">
		<?php while ( have_posts() ) : the_post(); ?>
		<div id="article">
		<h1>
			<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		</h1>
		<div class="info">
			<span class="meat_span">分类: <?php the_category(', ') ?></span> <span
				class="meat_span">发布时间: <?php the_time('Y-m-d H:i') ?></span> <span
				class="meat_span"><i class="iconfont">&#279;</i><?php if(function_exists(the_views)) { the_views(true,'','次浏览', true);}?></span>
			<span class="meat_span"><i class="iconfont">&#54;</i> 
							<!-- UYAN COUNT BEGIN -->
                            <a href="<?php echo get_settings('home')."/?p=".get_the_ID();?>" id="uyan_count_unit">0条评论</a>
                            <!-- UYAN COUNT END --></span>
                <?php edit_post_link('编辑', '<span class="meat_span">', '</span>'); ?>
            </div>
            
		<div class="text">
		<div class="copyright">
		 本文原创作者:<a href="http://weibo.com/muguachou" rel="external nofollow" target="_blank">Cloud Chou.</a> 欢迎转载，请注明出处和<a href="<?php the_permalink()?>" target="_blank">本文链接</a> 
		</div>
                             <?php the_content(); ?>
                            </div>
		<div class="text_add">
			<div class="share"><?php echo stripslashes(get_option('tang_share')); ?></div>
		</div>
		<div class="meta">
			<i class="iconfont">&#48;</i><?php the_tags('', ', ', ''); ?></div>

	</div>
		<?php endwhile; ?>
        <div id="wumiiLikeRecBtnDiv" class="wumii"></div>
        <?php single_fenye();?>
        
        
        <div class="feed-tip">
		<div class="tablerow">
			<div class="navprev">上篇文章：<?php if (get_previous_post()) { previous_post_link('%link','%title');} else { echo "没有了，已经是最新文章";} ?></div>
			<div class="navnext">下篇文章：<?php if (get_next_post()) { next_post_link('%link','%title');} else { echo "木有了，已经是最后文章";} ?></div>
		</div>
	</div>

	<div id="wumiiDisplayDiv" class="wumiidisplay"></div>
	<div id="comments"><?php comments_template(); ?></div>
</div>
<?php get_sidebar('single'); ?>
<?php get_footer(); ?>