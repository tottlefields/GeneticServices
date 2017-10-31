<?php

$referer = (wp_get_referer()) ? wp_get_referer : $_SERVER['HTTP_REFERER'];
 
if(empty($_REQUEST)){
	wp_safe_redirect($referer);
	exit;	
}

global $wpdb;

if (isset($_REQUEST['client-submitted'])){
	
	$data = array();
	$where = array();
	foreach ($_REQUEST as $key => $value){
		if (preg_match('/^client_/', $key)){
			$new_key = str_replace('client_', '', $key);
			if ($new_key === 'id'){ $where['id'] = $value;}
			elseif (preg_match('/Address/', $new_key)){
				$address = array_pad(explode(', ', $value), 3, '');
				$data[$new_key."3"] = array_pop($address);
				$data[$new_key."2"] = array_pop($address);
				$data[$new_key] = implode(', ', $address);
			}
			else{
				$data[$new_key] = $value;
			}
		}
	}
	if ($_REQUEST['client_id'] > 0){ $wpdb->update('client', $data, $where); }
	else{
		$wpdb->insert('client', $data);
		$client_id = $wpdb->insert_id;
		$wpdb->update('orders', array('client_id' => $client_id), array('id' => $_REQUEST['id']));
	}
	wp_safe_redirect($referer);
	//wp_safe_redirect(get_site_url().'/orders/view/?id='.$_REQUEST['id']);
	exit;	
}

if (isset($_REQUEST['animal-submitted'])){
	
	$data = array();
	$where = array();
	foreach ($_REQUEST as $key => $value){
		if (preg_match('/^animal_/', $key)){
			$new_key = str_replace('animal_', '', $key);
			if ($new_key === 'id'){ $where['id'] = $value;}
			elseif (preg_match('/Sex/', $new_key)){
				$value = substr($value, 0, 1);
				$data['Sex'] = $value;
			}
			elseif (preg_match('/BirthDate/', $new_key)){
				$data[$new_key] = dateToSQL($value);
			}
			else{
				$data[$new_key] = $value;
			}
		}
	}
	$wpdb->update('animal', $data, $where);
	wp_safe_redirect($referer);
	//wp_safe_redirect(get_site_url().'/orders/view/?id='.$_REQUEST['id']);
	exit;	
	
}
?>
