<?php

function is_logged_in() {
	require_once('OAuth/OAuth.php');
	require_once('OAuth/OAuth1Client.php');
	
	if ($_SESSION['state'] == 0) {
		wp_clear_auth_cookie();
		return false;
	}

	$opts = get_option('usosplan_option_name');
	$consumer_key = $opts['usosplan_api_key'];
	$consumer_secret = $opts['usosplan_api_secret'];
	$usosapi_base_url = $opts['usosplan_api_base_url'];
			
	$oauth = new OAuth1Client($consumer_key, $consumer_secret);
	$oauth->api_base_url      = $usosapi_base_url;			
	$oauth->token = new OAuthToken ($_SESSION['token'],$_SESSION['secret']);

	try {
		$oauth->get("services/users/user?fields=id");
	} catch (Exception $e) {
		$_SESSION['state'] = 0;
		wp_clear_auth_cookie();
		return false;
	}
	return true;
}

function get_usos_user_info($id) {
	
	require_once('OAuth/OAuth.php');
	require_once('OAuth/OAuth1Client.php');
	
	$opts = get_option('usosplan_option_name');
	$consumer_key = $opts['usosplan_api_key'];
	$consumer_secret = $opts['usosplan_api_secret'];
	$usosapi_base_url = $opts['usosplan_api_base_url'];
			
	$oauth = new OAuth1Client($consumer_key, $consumer_secret);
	$oauth->api_base_url      = $usosapi_base_url;			
	$oauth->token = new OAuthToken ($_SESSION['token'],$_SESSION['secret']);
	
	try {
		$result =  $oauth->get("services/users/user?fields=first_name|last_name|email|id");
		$groups = $oauth->get("services/groups/user?fields=group_number|course_id|term_id");
		$term = $opts['usosplan_term_id'];
		foreach ($groups->groups->$term as $group) {
			if ($group->course_id == $opts['usosplan_course_id'] && $group->term_id == $opts['usosplan_term_id'])
				$result->group_number = $group->group_number;
		}
		if (!isset($result->group_number))
			$result->group_number = -1;
	} catch (Exception $e) {
		echo "Error getting user info: ".$e->getMessage();
		exit;
	}
	return $result;
}

function get_group_number () {
	global $wpdb;
	return $wpdb->get_var('SELECT group_number FROM `'.$wpdb->prefix.'usos_user` WHERE user_id=\''.get_current_user_id()."'");
}

function get_usos_timetable () {
	$result = array();
	$opts = get_option('usosplan_option_name');
	$usosapi_base_url = $opts['usosplan_api_base_url'];
//	if (!isset($_SESSION))
//		session_start();
	if (isset($_SESSION) && isset($_SESSION['state']) && $_SESSION['state'] == 200) {
		require_once('OAuth/OAuth.php');
		require_once('OAuth/OAuth1Client.php');
		
		$consumer_key = $opts['usosplan_api_key'];
		$consumer_secret = $opts['usosplan_api_secret'];
				
		$oauth = new OAuth1Client($consumer_key, $consumer_secret);
		$oauth->api_base_url      = $usosapi_base_url;			
		$oauth->token = new OAuthToken ($_SESSION['token'],$_SESSION['secret']);
		
		try {
			$json = $oauth->get("services/tt/user?fields=start_time|classtype_name|course_id");
			foreach ($json as $row) {
				if ($row->course_id == $opts['usosplan_course_id'])
					$result[] = $row;
			}
		} catch  (Exception $e) {
			//nothing special
		}
	}

	if ($result == array()) {
		$result = json_decode(file_get_contents($usosapi_base_url.'services/tt/course_edition?course_id='.$opts['usosplan_course_id'].'&term_id='.$opts['usosplan_term_id'].'&fields=start_time|classtype_name'));
	}
	$lang = substr(get_locale(), 0, 2);
	foreach ($result as $row) {
		$row->text = $row->classtype_name->$lang;
		$row->time = $row->start_time;
	}

	return $result;
}

?>
