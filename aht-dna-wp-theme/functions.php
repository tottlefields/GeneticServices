<?php
session_start();

// Register Custom Navigation Walker
//require_once('wp_bootstrap_navwalker.php');

register_nav_menu(
	'main-menu',
	'Main Menu'
);



function mytheme_enqueue_scripts() {
	wp_deregister_script('jquery');
	wp_register_script('jquery', ("//code.jquery.com/jquery-2.2.4.min.js"), false, '2.2.4', true);
	wp_enqueue_script('jquery');
	
	//Bootstarp JS
	wp_register_script('bootstrap-js', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', '', '3.3.7', true);
	wp_enqueue_script('bootstrap-js');
	
	//BS DatePicker
	wp_register_script('datepicker-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', array('jquery', 'bootstrap-js'), '1.6.4', true);
	wp_enqueue_script('datepicker-js');
	
	//PDF Make
	wp_register_script('pdfmake-js', get_template_directory_uri().'/assets/js/pdfmake.min.js', false, '', true);
	wp_register_script('pdfmake-fonts-js', get_template_directory_uri().'/assets/js/vfs_fonts.js', array('pdfmake-js'), '', true);
	wp_enqueue_script('pdfmake-js');
	wp_enqueue_script('pdfmake-fonts-js');
	
	//DataTables
	//wp_register_script('datatables-js', '//cdn.datatables.net/v/bs/dt-1.10.15/b-1.3.1/b-html5-1.3.1/b-print-1.3.1/r-2.1.1/se-1.2.2/datatables.js', array('jquery', 'bootstrap-js', 'pdfmake-js'), '1.10.15', true);
	wp_register_script('datatables-js', '//cdn.datatables.net/v/bs/dt-1.10.15/b-1.3.1/b-html5-1.3.1/b-print-1.3.1/r-2.1.1/se-1.2.2/datatables.min.js', array('jquery', 'bootstrap-js', 'pdfmake-js'), '1.10.15', true);
	wp_enqueue_script('datatables-js');
	
	// register template-specific scripts
    wp_register_script('js-orders', get_template_directory_uri().'/assets/js/orders.js', array('jquery', 'datatables-js'), '0.1', true); 
    // conditional load
    if (is_page(array('orders'))){
    	wp_enqueue_script('js-orders');
    	wp_localize_script('js-orders', 'DennisAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');


add_filter('wp_nav_menu','add_menuclass');
add_filter('nav_menu_css_class', 'special_nav_class', 10 , 2);

// define the wp_nav_menu_items callback 
function filter_wp_nav_menu_items( $items, $args ) { 
    // make filter magic happen here... 
    $items_array = explode("\n", $items);
    $new_items = array();
    foreach ($items_array as $item){
    	if (preg_match("/fa-\S+/i", $item, $matches)) {
			$item = preg_replace('/'.$matches[0].' /', '', $item);
			$item = preg_replace('/<i class="fa">/', '<i class="fa '.$matches[0].'">', $item);
		}
		array_push($new_items, $item);
    }
    return implode("\n", $new_items); 
}; 
         
// add the filter 
add_filter( 'wp_nav_menu_items', 'filter_wp_nav_menu_items', 10, 2 ); 

function add_menuclass($ulclass) {
	//var_dump($ulclass);
	return preg_replace('/<a /', '<a class="nav-link"', $ulclass);
}

function special_nav_class ($classes, $item) {
	if (in_array('current-menu-item', $classes) ){
		$classes[] = 'active';
	}
	if (in_array('menu-item', $classes) ){
		$classes[] = 'nav-item';
	}
	return $classes;
}

function custom_breadcrumbs(){
	
    // Settings
    $separator          = '&gt;';
    $home_title         = 'Home';
    
    $slug_fa_lookup = array('orders' => 'fa-shopping-basket');
    
    // Get the query & post information
    global $post,$wp_query;
       
    // Do not display on the homepage
    if ( !is_front_page() ) {
    	echo '
							<li>
								<i class="fa fa-home"></i>
								<a href="/">'.$home_title.'</a> 
							</li>';
    	if(is_page()){
    		if($post->post_parent){
    			$anc = get_post_ancestors($post->ID);
    			$anc_link = get_page_link($post->post_parent);
    			
    			foreach ($anc as $ancestor){
    				$output .= '<li><a href="'.$anc_link.'">'.get_the_title($ancestor).'</a></li>';
    			}
    			echo $output;
    		}
    		echo '<li>'.the_title('', '', false).'</li>';
    	}
		echo '
						</ul>';
	}
}


require_once 'ajax.php';

?>
