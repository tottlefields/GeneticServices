<?php /* Template Name: View Animal */ ?>
<?php
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	//wp_redirect(get_site_url().'/orders/');
	exit;	
}
$animal_id = $_REQUEST['id'];

$animals = animalSearch(array('id' => $animal_id));
$animal_details = $animals[0];

$name = (isset($animal_details->RegisteredName)) ? $animal_details->RegisteredName.' <em>('.$animal_details->PetName.')</em>' : $animal_details->PetName;

?>
<?php get_header(); ?>

	<h1><?php echo $name; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>

	<section class="row">
		<div class="col-md-12">
		<?php debug_array($animal_details); ?>
		</div>
	</section>

<?php get_footer(); ?>