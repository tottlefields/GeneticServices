<?php
session_start ();

// Register Custom Navigation Walker
// require_once('wp_bootstrap_navwalker.php');

register_nav_menu ( 'main-menu', 'Main Menu' );

// Function to change email address
function wpb_sender_email($original_email_address) {
	return 'dennis@aht.org.uk';
}

// Function to change sender name
function wpb_sender_name($original_email_from) {
	return 'DENNIS';
}

// Editing the login page
add_filter ( 'wp_mail_from', 'wpb_sender_email' );
add_filter ( 'wp_mail_from_name', 'wpb_sender_name' );
function my_custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo ( 'stylesheet_directory' ) . '/assets/css/custom-login-styles.css" />';
}
add_action ( 'login_head', 'my_custom_login' );
function my_login_logo_url() {
	return get_bloginfo ( 'url' );
}
add_filter ( 'login_headerurl', 'my_login_logo_url' );
function my_login_logo_url_title() {
	return 'DENNIS for Genetic Services';
}
add_filter ( 'login_headertitle', 'my_login_logo_url_title' );
function admin_login_redirect($redirect_to, $request, $user) {
	global $user;
	if (isset ( $user->roles ) && is_array ( $user->roles )) {
		if (in_array ( "administrator", $user->roles )) {
			return $redirect_to;
		} else {
			return home_url ();
		}
	} else {
		return $redirect_to;
	}
}
add_filter ( "login_redirect", "admin_login_redirect", 10, 3 );

// Hooking up our functions to WordPress filters
function mytheme_enqueue_scripts() {
	wp_deregister_script ( 'jquery' );
	wp_register_script ( 'jquery', ("//code.jquery.com/jquery-2.2.4.min.js"), false, '2.2.4', true );
	wp_enqueue_script ( 'jquery' );
	
	// Bootstrap JS
	wp_register_script ( 'bootstrap-js', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', '', '3.3.7', true );
	wp_enqueue_script ( 'bootstrap-js' );
	
	// BS DatePicker
	wp_register_script ( 'datepicker-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', array (
			'jquery',
			'bootstrap-js' 
	), '1.6.4', true );
	wp_enqueue_script ( 'datepicker-js' );
	
	// PDF Make
	wp_register_script ( 'pdfmake-js', get_template_directory_uri () . '/assets/js/pdfmake.min.js', false, '0.1.31', true );
	wp_register_script ( 'pdfmake-fonts-js', get_template_directory_uri () . '/assets/js/vfs_fonts.js', array (
			'pdfmake-js' 
	), '0.1.31', true );
	wp_enqueue_script ( 'pdfmake-js' );
	wp_enqueue_script ( 'pdfmake-fonts-js' );
	
	// DataTables
	wp_register_script ( 'datatables-js', '//cdn.datatables.net/v/bs/dt-1.10.15/b-1.3.1/b-html5-1.3.1/b-print-1.3.1/r-2.1.1/se-1.2.2/datatables.min.js', array (
			'jquery',
			'bootstrap-js',
			'pdfmake-js' 
	), '1.10.15', true );
	wp_enqueue_script ( 'datatables-js' );
	
	// Bootstrap Toggle
	wp_register_script ( 'bs-toggle-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', array (
			'jquery' 
	), '2.2.2', true );
	wp_enqueue_script ( 'bs-toggle-js' );
	
	// Barcode JS
	wp_register_script ( 'jquery-barcode-js', get_template_directory_uri () . '/assets/js/jquery-barcode.min.js', array (
			'jquery' 
	), '2.0.3', true );
	wp_enqueue_script ( 'jquery-barcode-js' );
	
	// Editable
	// wp_register_script('editable-js', '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js', array('jquery', 'bootstrap-js'), '1.5.0', true);
	// wp_enqueue_script('editable-js');
	
	// jQuery Validate
	wp_register_script ( 'jquery-validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js', array (
			'jquery' 
	), '1.17.0', true );
	wp_enqueue_script ( 'jquery-validate' );
	
	// Bootbox.js
	wp_register_script ( 'bootbox-js', '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js', array (
			'jquery',
			'bootstrap-js' 
	), '4.4.0', true );
	wp_enqueue_script ( 'bootbox-js' );
	
	// Main functions js file
	wp_register_script ( 'js-functions', get_template_directory_uri () . '/assets/js/functions.js', array (
			'jquery',
			'datatables-js' 
	), '0.1', true );
	wp_enqueue_script ( 'js-functions' );
	
	// register template-specific scripts
	wp_register_script ( 'js-orders', get_template_directory_uri () . '/assets/js/orders.js', array (
			'jquery',
			'datatables-js' 
	), '0.1', true );
	wp_register_script ( 'js-new-order', get_template_directory_uri () . '/assets/js/new-order.js', array (
			'jquery' 
	), '0.1', true );
	wp_register_script ( 'js-portal', get_template_directory_uri () . '/assets/js/portal.js', array (
			'jquery' 
	), '0.1', true );
	
	// conditional load
	if (is_page ( array (
			'orders' 
	) )) {
		wp_enqueue_script ( 'js-orders' );
		// wp_localize_script('js-orders', 'DennisAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
	if (is_page ( array (
			'add-manual-order' 
	) )) {
		wp_enqueue_script ( 'js-new-order' );
		// wp_localize_script('js-new-order', 'DennisAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
	if (is_page ( array (
			'portal' 
	) )) {
		wp_enqueue_script ( 'js-portal' );
	}
}
add_action ( 'wp_enqueue_scripts', 'mytheme_enqueue_scripts' );

add_filter ( 'wp_nav_menu', 'add_menuclass' );
add_filter ( 'nav_menu_css_class', 'special_nav_class', 10, 2 );

// define the wp_nav_menu_items callback
function filter_wp_nav_menu_items($items, $args) {
	// make filter magic happen here...
	$items_array = explode ( "\n", $items );
	$new_items = array ();
	foreach ( $items_array as $item ) {
		if (preg_match ( "/fa-\S+/i", $item, $matches )) {
			$item = preg_replace ( '/' . $matches [0] . ' /', '', $item );
			$item = preg_replace ( '/<i class="fa">/', '<i class="fa ' . $matches [0] . '">', $item );
		}
		array_push ( $new_items, $item );
	}
	return implode ( "\n", $new_items );
}
;

// add the filter
add_filter ( 'wp_nav_menu_items', 'filter_wp_nav_menu_items', 10, 2 );
function add_menuclass($ulclass) {
	// var_dump($ulclass);
	return preg_replace ( '/<a /', '<a class="nav-link"', $ulclass );
}
function special_nav_class($classes, $item) {
	if (in_array ( 'current-menu-item', $classes )) {
		$classes [] = 'active';
	}
	if (in_array ( 'menu-item', $classes )) {
		$classes [] = 'nav-item';
	}
	return $classes;
}
function custom_breadcrumbs() {
	
	// Settings
	$separator = '&gt;';
	$home_title = 'Home';
	
	$slug_fa_lookup = array (
			'orders' => 'fa-shopping-basket' 
	);
	
	// Get the query & post information
	global $post, $wp_query;
	
	// Do not display on the homepage
	if (! is_front_page ()) {
		echo '
							<li>
								<i class="fa fa-home"></i>
								<a href="/">' . $home_title . '</a> 
							</li>';
		if (is_page ()) {
			if ($post->post_parent) {
				$anc = get_post_ancestors ( $post->ID );
				$anc_link = get_page_link ( $post->post_parent );
				
				foreach ( $anc as $ancestor ) {
					$output .= '<li><a href="' . $anc_link . '">' . get_the_title ( $ancestor ) . '</a></li>';
				}
				echo $output;
			}
			echo '<li>' . the_title ( '', '', false ) . '</li>';
		}
		echo '
						</ul>';
	}
}

require_once 'ajax.php';
require_once 'general.php';

?>
