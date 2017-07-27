<?php get_header(); ?>
					<h1>Welcome to DENNIS
						<span style="font-size:50%"><ul class="breadcrumb pull-right">
							<?php custom_breadcrumbs(); ?>
						</span>
					</h1>
					
					<div class="row">
						<div class="col-sm-12">
    						<div class="jumbotron text-center">
								<form id="search-form" class="form-inline" role="form" method="post" action="/search/">
									<div class="col-md-6 col-offset-3">
										<div class="input-group input-rgoup-lg">
											<input type="text" class="form-control search-form" placeholder="Search">
											<span class="input-group-btn">
												<button type="submit" class="btn btn-primary search-btn" data-target="#search-form" name="q"><i class="fa fa-search"></i></button>
											</span>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>

					<section class="row text-center placeholders">
						<div class="col-6 col-sm-3 placeholder"> <img src="https://i0.wp.com/afterimagedesigns.com/wp-bootstrap-starter/wp-content/uploads/2017/06/blue.jpg?resize=200%2C200" class="img-fluid rounded-circle" alt="Generic placeholder thumbnail" data-recalc-dims="1"> </p>
						<h4>Label</h4>
						<div class="text-muted">Something else</div>
					</div>
					<div class="col-6 col-sm-3 placeholder"> <img src="https://i1.wp.com/afterimagedesigns.com/wp-bootstrap-starter/wp-content/uploads/2017/06/green.jpg?resize=200%2C200" class="img-fluid rounded-circle" alt="Generic placeholder thumbnail" data-recalc-dims="1"> </p>
						<h4>Label</h4>
						<p> <span class="text-muted">Something else</span> </div>
					<div class="col-6 col-sm-3 placeholder"> <img src="https://i0.wp.com/afterimagedesigns.com/wp-bootstrap-starter/wp-content/uploads/2017/06/blue.jpg?resize=200%2C200" class="img-fluid rounded-circle" alt="Generic placeholder thumbnail" data-recalc-dims="1"> </p>
						<h4>Label</h4>
						<p> <span class="text-muted">Something else</span> </div>
					<div class="col-6 col-sm-3 placeholder"> <img src="https://i1.wp.com/afterimagedesigns.com/wp-bootstrap-starter/wp-content/uploads/2017/06/green.jpg?resize=200%2C200" class="img-fluid rounded-circle" alt="Generic placeholder thumbnail" data-recalc-dims="1"> </p>
						<h4>Label</h4>
						<p> <span class="text-muted">Something else</span> </div>
					</section>

<?php get_footer(); ?>