<?php /* Template Name: Samples */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row text-center placeholders">
						<?php $count = countUnextracted(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox blue">
								<div class="number"><i class="fas fa-vial"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Awaiting Extraction</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/samples/unextracted/"> View list</a></div><?php } ?>
							</div>
						</div>
						<?php $count = countUntested(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox yellow">
								<div class="number"><i class="fas fa-dna"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Awaiting Testing</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/samples/untested/"> View list</a></div><?php } ?>
							</div>
						</div>
						<?php $count = countUnanalysed(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox red">
								<div class="number"><i class="fas fa-diagnoses"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Awaiting Analysis</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/samples//"> View list</a></div><?php } ?>
							</div>
						</div>
						<?php $count = countUnreported(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox green">
								<div class="number"><i class="fas fa-stamp"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Awaiting Authorisation</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/samples//"> View list</a></div><?php } ?>
							</div>
						</div>
					</section>
					
<?php get_footer(); ?>