<?php /* Template Name: Statistics */ ?>
<?php get_header(); ?>
					<h1><?php wp_title('', true,''); ?>
						<span style="font-size:50%"><ul class="breadcrumb pull-right">
							<?php custom_breadcrumbs(); ?>
						</span>
					</h1>

					<section class="row text-center placeholders">
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Month</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-purple.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y'), date('m')); ?></div>
							</div>
							<div class="text-muted">Tests ordered this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y')); ?></div>						
							</div>
							<div class="text-muted">Tests ordered this year.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Month</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-purple.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo countOrders(date('Y'), date('m')); ?></div>	
							</div>
							<div class="text-muted">Total orders this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo countOrders(date('Y')); ?></div>	
							</div>
							<div class="text-muted">Total orders this year.</div>
						</div>
					</section>

<?php get_footer(); ?>