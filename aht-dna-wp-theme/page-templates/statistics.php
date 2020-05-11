<?php /* Template Name: Statistics */ ?>
<?php global $wpdb; ?>
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
								<div style="line-height:200px;"><i class="fas fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y'), date('m')); ?></div>
							</div>
							<div class="text-muted">Tests ordered this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fas fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y')); ?></div>						
							</div>
							<div class="text-muted">Tests ordered this year.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Month</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-purple.png'; ?>);">
								<div style="line-height:200px;"><i class="fas fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y'), date('m')); ?></div>	
							</div>
							<div class="text-muted">Total orders this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fas fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y')); ?></div>	
							</div>
							<div class="text-muted">Total orders this year.</div>
						</div>
					</section>
					
					<h2 style="text-align:left">Yearly test Statistics</h2>
					<section class="row">
						<div class="col-md-4">
							<canvas id="myChart" height="400"></canvas>
							<?php
							$sql = "select date_format(OrderDate, '%Y') as OrderYear, date_format(OrderDate, '%m') as OrderMonth, count(*) as month_count 
							from orders inner join order_tests on orders.id=order_id where cancelled_date is null group by 1,2";
						/*	+-----------+------------+----------+
							| OrderYear | OrderMonth | count(*) |
							+-----------+------------+----------+
							| 2017      | 01         |      553 |
							| 2017      | 02         |      608 |
							| 2017      | 03         |      601 |
							| 2017      | 04         |      466 |
							| 2017      | 05         |      517 |
							| 2017      | 06         |      501 |
							| 2017      | 07         |      495 |
							| 2017      | 08         |      474 |
							| 2017      | 09         |      894 |
							| 2017      | 10         |     1512 |
							| 2017      | 11         |      690 |
							| 2017      | 12         |      483 |
							| 2018      | 01         |     1384 |
							| 2018      | 02         |     1044 |
							| 2018      | 03         |     1444 |
							| 2018      | 04         |      448 |
							+-----------+------------+----------+ */
							$results = $wpdb->get_results($sql);
							$order_counts = array();
							foreach ( $results as $order_date ) {
							    if(!isset($order_counts[$order_date->OrderYear])){$order_counts[$order_date->OrderYear] = array(); }
								array_push($order_counts[$order_date->OrderYear], $order_date->month_count);
							}
							?>
							<script>
								var ctx = document.getElementById("myChart").getContext('2d');
								var myChart = new Chart(ctx, {
									type: 'line',
									data: {
										labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
										datasets: [{
											label: '2017',
											data: [<?php echo implode(',', $order_counts['2017']); ?>],
											backgroundColor: window.chartColors.purple,
											borderColor: window.chartColors.purple,
											borderWidth: 1,
											fill: false
										},{
											label: '2018',
											data: [<?php echo implode(',', $order_counts['2018']); ?>],
											backgroundColor: window.chartColors.red,
											borderColor: window.chartColors.red,
											borderWidth: 1,
											fill: false
										},{
											label: '2019',
											data: [<?php echo implode(',', $order_counts['2019']); ?>],
											backgroundColor: window.chartColors.blue,
											borderColor: window.chartColors.blue,
											borderWidth: 1,
											fill: false
										},{
											label: '2020',
											data: [<?php echo implode(',', $order_counts['2020']); ?>],
											backgroundColor: window.chartColors.orange,
											borderColor: window.chartColors.orange,
											borderWidth: 1,
											fill: false
										}]
									},
									options: {
										maintainAspectRatio: false,
										responsive: true,
										title: { display: true, text: 'Tests Sold per Month' }
										/*scales: {
											yAxes: [{
												ticks: {
													beginAtZero:true
												}
											}]
										}*/
									}
								});
								</script>
						</div>
						
						<div class="col-md-4">
							<canvas id="myChart2" height="400"></canvas>
							<?php
							$sql = " select test_code, count(*) from order_tests where cancelled_date is null group by 1 order by 2 desc limit 15";	//5210
						/*	+-----------+----------+
							| test_code | count(*) |
							+-----------+----------+
							| SPS       |      903 |
							| CKCS_ALL  |      552 |
							| CORD1     |      478 |
							| PRA4      |      390 |
							| WP        |      362 |
							| PLL       |      351 |
							| CP        |      321 |
							| TT_ALL    |      299 |
							| RET       |      282 |
							| RCD4      |      279 |
							| PRA3      |      244 |
							| POAG      |      220 |
							| POAG3     |      204 |
							| SBT_ALL   |      167 |
							| HC        |      158 |
							+-----------+----------+	*/ 
							$sql2 = "select count(*) from order_tests where cancelled_date is null";
						/*	+----------+
							| count(*) |
							+----------+
							|     6660 |
							+----------+	*/ 

							?>

							<script>
								var ctx2 = document.getElementById("myChart2").getContext('2d');
								var myChart2 = new Chart(ctx2, {
									type: 'doughnut',
									data: {
										labels: ["SPS", "CKCS_ALL", "CORD1", "PRA4", "WP", "PLL", "CP", "TT_ALL", "RET", "RCD4", "PRA3", "POAG", "POAG3", "SBT_ALL", "HC", "Others"],
										datasets: [ {
											label: '2017',
											data: [903,552,478,390,362,351,321,299,282,279,244,220,204,167,158,1450],
											backgroundColor: [
												window.chartColors.red,
												window.chartColors.orange,
												window.chartColors.yellow,
												window.chartColors.green,
												window.chartColors.blue,
												window.chartColors.purple,
												window.chartColors.pink
											],
										},{
											label: '2018',
											data: [552,478,390,362,351,321,299,282,279,244,220,204,167,158,1450],
											backgroundColor: [
												window.chartColors.red,
												window.chartColors.orange,
												window.chartColors.yellow,
												window.chartColors.green,
												window.chartColors.blue,
												window.chartColors.purple
											],
										}]
									},
									options: {
										maintainAspectRatio: false,
										responsive: true,
										title: { display: true, text: 'Top Selling Tests' },
										legend: {display: true, position: 'right'  }
										/*scales: {
											yAxes: [{
												ticks: {
													beginAtZero:true
												}
											}]
										}*/
									}
								});
								</script>
						</div>
					</section>

<?php get_footer(); ?>