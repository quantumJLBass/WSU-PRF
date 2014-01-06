<?php get_header(); ?>

		<main>

			<article id="post-0" class="post error404 no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'This is somewhat embarrassing, isn&rsquo;t it?', 'twentytwelve' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'twentytwelve' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			
			<script>
				/* $(document).ready(function(){
					$('#wsu-search').clone().appendTo('main');
				}); */
			
			</script>
			
			</article><!-- #post-0 -->

		</main><!-- /main -->
	
<?php get_template_part( 'spine/body' ); ?>

<?php get_footer(); ?>