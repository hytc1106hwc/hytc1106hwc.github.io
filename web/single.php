<?php get_header(); ?>
<div id="zan-bodyer" style="padding-top: 30px;">
	<div class="container">
		<div class="row">
			<div class="col-lg-8">								
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
						<p>
							<?php the_content(); ?>
						</p>

					</div>				
				</div>
				<?php endwhile; else: ?>
				<p><?php _e('Sorry, this page does not exist.'); ?></p>
				<?php endif; ?>
				
				<?php pagination($query_string); ?>
				<?php comments_template(); ?> 									
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
</body>
</html>