<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-lg-8">
				<div class="alert alert-danger fade in">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
						欢迎使用由 <a target="_blank" href="http://www.yeahzan.com/">佚站互联</a> 提供的 <strong>Bootstrap主题</strong>
						，我们很高兴您能够使用并期待您能够喜欢它：）
				</div>
				<div class="slide">

				  <?php if(is_dynamic_sidebar()) dynamic_sidebar('幻灯片');?> 
	            </div>

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<div class="article container well">
				    <div class="data-article hidden-sm">
						<span class="month"><?php the_time(n月) ?></span>
						<span class="day"><?php the_time(d) ?></span>

					</div>
					<div class="title-article">
						<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
					</div>
					<div class="tag-article container">
						<span class="label label-success visible-sm">日期：<?php the_date('m-d'); ?></span>
						<span class="label hidden-sm">分类：<?php the_category(','); ?></span>
						<span class="label label-info hidden-sm">作者：<?php the_author_posts_link(); ?></span>
						<span class="label label-warning">浏览：<?php the_views(); ?></span>
					</div>
					<div class="centent-article">					
						<?php $thumb_img = has_post_thumbnail() ? get_the_post_thumbnail( $post->
						ID, array(730, 300), array('alt' => trim(strip_tags( $post->post_title )),'title'=> trim(strip_tags( $post->post_title ))) ) : get_post_img( 730, 300, 1);?>
						<div class="thumbnail"><?php echo $thumb_img;?></div>							
						<div class="alert alert-info">					
							<?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 250,"..."); ?>
						</div>

					</div>
						<a class="btn btn-danger pull-right read-more" href="<?php the_permalink() ?>"  title="详细阅读 <?php the_title(); ?>">阅读全文<span class="badge"><?php comments_number( '0', '1', '%' ); ?></span></a>
				</div>
				<?php endwhile; else: ?>
				<p><?php _e('非常抱歉，没有相关文章.'); ?></p>
				<?php endif; ?>
				<?php pagination($query_string); ?> 
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<!-- //zan-bodyer -->
<?php get_footer(); ?>
</body>
</html>