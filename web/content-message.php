<?php
/*
Template Name: content-message
*/
?>

<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-lg-8">
				<div class="alert alert-info fade in">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
				<h2 class="gbook-title">欢迎在这里留下您对Zanblog的看法</h2>

				<p>非常感谢您对Zanblog主题的使用与支持，如果您在使用主题的过程中遇到任何问题，都可以在这里留言探讨。</p>
				<p>我们会尽快为您答复。</p>
				<p>也欢迎提出您的宝贵意见与建议，我们会根据反馈情况来进行下一个版本的开发与完善。</p>
				<strong>谢谢！</strong>
				</div>
				<?php comments_template(); ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
</body>
</html>