<?php /* Template Name: Search */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-12">
		
		<pre><?php print_r($_REQUEST); ?></pre>
		
		
		</div>
	</section>

<?php get_footer(); ?>