<?php get_header(); ?>


		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) : ?>
					<h1><?php single_post_title(); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
					

			<?php
			endif;

			/* Start the Loop */
			while ( have_posts() ) : the_post();
				echo '<section>';
				get_template_part( 'template-parts/content', get_post_format() );
				echo '</section>';

			endwhile;

			the_posts_navigation();

		else :
			echo '<section>';
			get_template_part( 'template-parts/content', 'none' );
			echo '</section>';

		endif; ?>

<?php get_footer(); ?>