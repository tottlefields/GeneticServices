<?php
session_start ();

// Register Custom Navigation Walker
// require_once('wp_bootstrap_navwalker.php');

register_nav_menu ( 'main-menu', 'Main Menu' );

// Function to change email address
//function wpb_sender_email($original_email_address) {
//	return 'dennis@aht.org.uk';
//}

// Function to change sender name
function wpb_sender_name($original_email_from) {
	return 'DENNIS';
}

add_theme_support('title-tag');

// Editing the login page
//add_filter ( 'wp_mail_from', 'wpb_sender_email' );
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

function login_checked_remember_me() {
	add_filter( 'login_footer', 'rememberme_checked' );
}
add_action( 'init', 'login_checked_remember_me' );
function rememberme_checked() {
	echo "<script>document.getElementById('rememberme').checked = true;</script>";
}

function add_query_vars($aVars) {
	//$aVars[] = "plate_type"; // represents the name of the plate type as shown in the URL
	//$aVars[] = "plate_id"; // represents the name of the plate id as shown in the URL
	$aVars[] = "plate"; // represents the name of the plate id as shown in the URL
	$aVars[] = "hilight"; // represents the name of the plate id as shown in the URL
	return $aVars;
}
// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');

function add_rewrite_rules($aRules) {
	$aNewRules = array(
//		'plates/add-plate/([^/]+)/([^/]+)/?$' => 'index.php?pagename=add-plate&plate_type=$matches[1]&plate_id=$matches[2]',
		'plate/([^/]+)/well/([^/]+)/?$' => 'index.php?pagename=plates&plate=$matches[1]&hilight=$matches[2]',
		'plate/([^/]+)/?$' => 'index.php?pagename=plates&plate=$matches[1]',
//		'plate/([^/]+)/?$' => 'index.php?pagename=plates&plate_type=$matches[1]',
//		'plate/([^/]+)/([^/]+)/?$' => 'index.php?pagename=plates&plate_type=$matches[1]&plate_id=$matches[2]'
	);
	$aRules = $aNewRules + $aRules;
	return $aRules;
}
// hook add_rewrite_rules function into rewrite_rules_array
add_filter('rewrite_rules_array', 'add_rewrite_rules');



// add filter
add_filter('pre_get_document_title', 'change_my_title');
// Our function
function change_my_title($title) {
    if ( is_page( 'plates' ) && get_query_var( 'plate' ) ) {
        $plate_id = get_query_var( 'plate' );
        $title = $plate_id.' - Plates - Dennis';
    }
    
    return $title;
}







// Hooking up our functions to WordPress filters
function mytheme_enqueue_scripts() {
	global $wp;
	
	wp_deregister_script ( 'jquery' );
	wp_register_script ( 'jquery', ("//code.jquery.com/jquery-2.2.4.min.js"), false, '2.2.4', true );
	wp_enqueue_script ( 'jquery' );
	
	// Bootstrap JS
	wp_register_script ( 'bootstrap-js', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', '', '3.3.7', true );
	wp_enqueue_script ( 'bootstrap-js' );
	
	// BS DatePicker
	wp_register_script ( 'datepicker-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js', array ('jquery', 'bootstrap-js' ), '1.9.0', true );
	wp_enqueue_script ( 'datepicker-js' );
	
	// PDF Make
	wp_register_script ( 'pdfmake-js', get_template_directory_uri () . '/assets/js/pdfmake.min.js', false, '0.1.60', true );
	wp_register_script ( 'pdfmake-fonts-js', get_template_directory_uri () . '/assets/js/vfs_fonts.js', array ('pdfmake-js' ), '0.1.60', true );
	wp_enqueue_script ( 'pdfmake-js' );
	wp_enqueue_script ( 'pdfmake-fonts-js' );
	
	// DataTables
	wp_register_script ( 'datatables-js', 'https://cdn.datatables.net/v/bs/dt-1.10.20/b-1.6.1/b-html5-1.6.1/b-print-1.6.1/r-2.2.3/sl-1.3.1/datatables.min.js', array ('jquery','bootstrap-js','pdfmake-js'), '1.10.20', true );
	wp_enqueue_script ( 'datatables-js' );
	
	wp_register_script ( 'dt-ukdatesort-js', '//cdn.datatables.net/plug-ins/1.10.16/sorting/date-uk.js', array ('bootstrap-js'), '1.10.16', true);
	wp_enqueue_script ( 'dt-ukdatesort-js' );
	
	wp_register_script ( 'dt-checkboxes-js', get_template_directory_uri().'/assets/js/dataTables.checkboxes.min.js', array ('bootstrap-js', 'datatables-js'), '1.2.11', true);
	wp_enqueue_script ( 'dt-checkboxes-js' );
	
	// Bootstrap Toggle
	wp_register_script ( 'bs-toggle-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', array ('jquery'), '2.2.2', true );
	wp_enqueue_script ( 'bs-toggle-js' );
	
	// Barcode JS
	wp_register_script ( 'jquery-barcode-js', get_template_directory_uri () . '/assets/js/jquery-barcode.min.js', array ('jquery'), '2.0.3', true );
	wp_enqueue_script ( 'jquery-barcode-js' );
	
	// jQuery Validate
	wp_register_script ( 'jquery-validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js', array ('jquery' ), '1.17.0', true );
	wp_enqueue_script ( 'jquery-validate' );
	
	// Bootbox.js
	wp_register_script ( 'bootbox-js', '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js', array ('jquery','bootstrap-js'), '4.4.0', true );
	wp_enqueue_script ( 'bootbox-js' );
	
	// Summernote JS
	wp_register_script ( 'summernote-js', '//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.10/summernote.min.js', array ('jquery','bootstrap-js'), '0.8.10', true);
	wp_enqueue_script ( 'summernote-js' );

	// FontAwesome
	wp_register_script( 'fontawesome-js', 'https://kit.fontawesome.com/8cef4f0a86.js', array(), '5.13.0', true );
	wp_enqueue_script( 'fontawesome-js');
	
	// Main functions js file
	wp_register_script ( 'js-functions', get_template_directory_uri () . '/assets/js/functions.js', array ('jquery','datatables-js'), '0.1.6', true );
	wp_enqueue_script ( 'js-functions' );

	// Main utils js file
	wp_register_script ( 'js-utils', get_template_directory_uri () . '/assets/js/utils.js', array (), '0.1.2', false );
	wp_enqueue_script ( 'js-utils' );
	
	// register template-specific scripts
	wp_register_script ( 'js-orders', get_template_directory_uri () . '/assets/js/orders.js', array ('jquery','datatables-js'), '0.1', true );
	wp_register_script ( 'js-samples', get_template_directory_uri () . '/assets/js/samples.js', array ('jquery','datatables-js'), '0.1', true );
	wp_register_script ( 'js-new-order', get_template_directory_uri () . '/assets/js/new-order.js', array ('jquery'), '0.1', true );
	wp_register_script ( 'js-portal', get_template_directory_uri () . '/assets/js/portal.js', array ('jquery'), '0.1', true );
	wp_register_script ( 'chart-js', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js', array (), '2.7.0', false );		//Chart JS
	wp_register_script ( 'js-plates', get_template_directory_uri () . '/assets/js/plates.js', array ('jquery'), '0.1', true );
	
	// conditional load
	if (is_page ( array ('orders') )) { wp_enqueue_script ( 'js-orders' ); }
	if (is_page ( array ('samples') ) || preg_match( '#^samples(/.+)?$#', $wp->request ) ) { wp_enqueue_script ( 'js-samples' ); }
	if (is_page ( array ('add-manual-order') )) { wp_enqueue_script ( 'js-new-order' ); }
	if (is_page ( array ('portal') )) { wp_enqueue_script ( 'js-portal' ); }
	if (is_page ( array ('statistics') )) { wp_enqueue_script ( 'chart-js' ); }
	if (is_page ( array ('plates') ) || preg_match( '#^plates(/.+)?$#', $wp->request ) ) { wp_enqueue_script ( 'js-plates' ); }
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
		if (preg_match ( "/fa\S+ fa-\S+/i", $item, $matches )) {
			$item = preg_replace ( '/' . $matches [0] . ' /', '', $item );
			$item = preg_replace ( '/<i class="fa">/', '<i class="' . $matches [0] . ' fa-fw">', $item );
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
function custom_breadcrumbs($sub_page = null) {
	
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
								<i class="fas fa-home"></i>
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
			
			if (isset($sub_page) && $sub_page != null){
				echo '<li><a href="' . get_permalink() . '">' . the_title ( '', '', false ) . '</a></li>';
				echo '<li>' . $sub_page . '</li>';
			}
			else{ echo '<li>' . the_title ( '', '', false ) . '</li>'; }
		}
		echo '
						</ul>';
	}
}

require_once 'ajax.php';
require_once 'general.php';

?>
