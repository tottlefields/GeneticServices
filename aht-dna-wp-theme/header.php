<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php wp_title(''); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' />
		<meta name="description" content="">
		<meta name="author" content="Ellen Schofield">
				
		<!-- start: Favicon -->
		<link rel="shortcut icon" href="<?php echo get_template_directory_uri() . '/assets/ico/favicon.ico'; ?>">
		<link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-57x57.png'; ?>">
		<link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-60x60.png'; ?>">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-72x72.png'; ?>">
		<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-76x76.png'; ?>">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-114x114.png'; ?>">
		<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-120x120.png'; ?>">
		<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-144x144.png'; ?>">
		<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-152x152.png'; ?>">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri() . '/assets/ico/apple-icon-180x180.png'; ?>">
		<link rel="icon" type="image/png" sizes="192x192"  href="<?php echo get_template_directory_uri() . '/assets/ico/android-icon-192x192.png'; ?>">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri() . '/assets/ico/favicon-32x32.png'; ?>">
		<link rel="icon" type="image/png" sizes="96x96" href="<?php echo get_template_directory_uri() . '/assets/ico/favicon-96x96.png'; ?>">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri() . '/assets/ico/favicon-16x16.png'; ?>">
		<meta name="msapplication-TileColor" content="#FF850D">
		<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri() . '/assets/ico/ms-icon-144x144.png'; ?>">
		<meta name="theme-color" content="#FF850D">
		<!-- end: Favicon -->
		
		<?php
		//JavaScript
		wp_enqueue_script('jquery');
		wp_enqueue_script('bootstrap-js');
		wp_enqueue_script('datepicker-js');
		?>
		
		<?php wp_head(); ?>
	
		<!-- start: CSS -->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800&subset=latin,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>
		<link href="<?php bloginfo('stylesheet_url');?>" rel="stylesheet" type='text/css'>
		<link id="base-style-responsive" href="<?php echo get_template_directory_uri() . '/assets/css/style-responsive.css'; ?>" rel="stylesheet">
		<link rel="stylesheet" href="<?php echo get_template_directory_uri() . '/assets/css/extra.css'; ?>" type='text/css'>
		<!-- end: CSS -->
       

		<!--[if gte IE 9]>
		<style type="text/css">
			.gradient { filter: none; }
		</style>
		<![endif]-->
		<!--[if lt IE 9]>
		<style type="text/css">
			.account-bar { width:510px!important;}
		</style>
		<![endif]-->
	</head>
	<body>
		<!-- start: Header -->
		<nav class="navbar navbar-default navbar-static-top navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="<?php echo site_url('/'); ?>"><img src="<?php echo get_template_directory_uri() . '/assets/img/logo.png'; ?>" alt="" class="navbar-brand top-logo" /></a>
				</div>
				
				<ul class="nav navbar navbar-top-links navbar-right mbn">
				<?php if(is_user_logged_in()) { 
					global $current_user;
					get_currentuserinfo();
					?>
                    <!-- <li class="dropdown"><a data-hover="dropdown" href="#" class="dropdown-toggle"><i class="fa fa-bell fa-fw"></i><span class="badge badge-green">3</span></a></li>
                    <li class="dropdown"><a data-hover="dropdown" href="#" class="dropdown-toggle"><i class="fa fa-envelope fa-fw"></i><span class="badge badge-orange">7</span></a> </li>
                    <li class="dropdown"><a data-hover="dropdown" href="#" class="dropdown-toggle"><i class="fa fa-tasks fa-fw"></i><span class="badge badge-yellow">8</span></a></li> -->
                    <li class="topbar-user"><span style="padding:15px;">Hi, <?php echo $current_user->user_firstname; ?></span></li>
                    <li><a href="<?php echo wp_logout_url(); ?>"><i class="fa fa-sign-out"></i>Log Out</a></li>
				<?php } else { ?>
					<li><a href="<?php echo wp_login_url(); ?>">Log In</a></li>
					<?php } ?>
                </ul>					
			</div>
		</nav>
		<!-- end: Header -->
		
		<div class="container-fluid">
			<div class="row">
			
				<nav class="col-sm-3 col-md-2 hidden-xs-down bg-faded sidebar">
				<?php
					wp_nav_menu(
						array(
							'container' => 'ul',
							'theme_location' => 'main-menu',
							'menu_class' => 'nav nav-pills nav-stacked',
							'link_before' => '<i class="fa"></i>&nbsp;'
							)
						);
					?>
				</nav>
				
				<noscript>
					<div class="alert alert-block col-sm-10">
						<h4 class="alert-heading">Warning!</h4>
						<p>You need to have <a href="http://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> enabled to use this site.</p>
					</div>
				</noscript>
				
				<!-- start: Content -->
				<main class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 pt-3">