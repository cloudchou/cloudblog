<?php get_header(); ?>
	<div id="main">
		<?php while ( have_posts() ) : the_post(); ?>
		<div id="article">
			<h1><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h1>
			<div class="info">
            	<span class="meat_span">作者: <?php the_author() ?></span>
                <span class="meat_span">分类: <?php the_category(', ') ?></span>
                <span class="meat_span">发布时间: <?php the_time('Y-m-d H:i') ?></span>
                <span class="meat_span"><i class="iconfont">&#279;</i><?php if(function_exists(the_views)) { the_views('次浏览', true);}?></span>
                <span class="meat_span"><i class="iconfont">&#54;</i><?php comments_popup_link ('没有评论','1条评论','%条评论'); ?></span>
                <?php edit_post_link('编辑', '<span class="meat_span">', '</span>'); ?>
            </div>
			 <div class="text">
                             <?php the_content(); ?>
<!--cnzz tui-->
	<script  type="text/javascript" c=hl charset="utf-8"  src="http://tui.cnzz.net/cs.php?id=1000051820"></script>
<!--cnzz tui-->

                            </div>
            <div class="text_add">
                        <div class="share"><?php echo stripslashes(get_option('tang_share')); ?></div>
                </div>
			<div class="meta"><i class="iconfont">&#48;</i><?php the_tags('', ', ', ''); ?></div>
                        
		</div>
		<?php endwhile; ?>
        <div id="wumiiLikeRecBtnDiv" class="wumii"></div>
        <?php single_fenye();?>
        <?php echo feed_copyright();?>
        <div class="feed-tip">
        <div>上篇文章：<?php if (get_previous_post()) { previous_post_link('%link','%title');} else { echo "没有了，已经是最后文章";} ?></div>
        <div>下篇文章：<?php if (get_next_post()) { next_post_link('%link','%title');} else { echo "木有了，已经是最新文章";} ?></div>
        </div>
        <div class="articles">
        <div class="post-author"><div class="avatar"><?php echo get_avatar( get_the_author_email(), '70' ); ?></div>
        <div class="post-author-desc">
        <a class="post-author-name" target="_blank" title="去看看他/她的专栏" href="#"><span><?php  echo the_author_meta( 'nickname' ); ?></span></a><br>
        <div class="post-author-description"><?php  echo the_author_meta( 'description' ); ?></div>
        <div class="post-author-links"><a rel="nofollow" target="_blank" href="#">查看Ta的专栏</a><?php if (get_the_author_meta('weibo_sina')!=""){ ?><?php echo "<a href='" . get_the_author_meta('weibo_sina') . "' target='_blank'> | 新浪微博</a>"; ?><?php } ?><?php if (get_the_author_meta('weibo_tx')!=""){ ?><?php echo "<a href='" . get_the_author_meta('weibo_tx') . "' target='_blank'> | 腾讯微博</a>"; ?><?php } ?><?php if (get_the_author_meta('renren')!=""){ ?><?php echo "<a href='" . get_the_author_meta('renren') . "' target='_blank'> | 人人</a>"; ?><?php } ?></div>
<div class="clear" style="clear both"></div>
<div class="post-author-title">关于本文小编</div>
        </div>
        </div>
        </div>
        <div id="wumiiDisplayDiv" class="wumiidisplay"></div>
        <div id="comments"><?php comments_template(); ?></div>
	</div>
	<?php get_sidebar('single'); ?>
<?php get_footer(); ?>