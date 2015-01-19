<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

/* USOS API Base URL, trailing slash included. */
$opts = get_option('usosplan_option_name');
$usosapi_base_url = $opts['usosplan_api_base_url'];
/* URL of THIS script. Required for callback support. */
$self_url = 'http://localhost/wordpress/';
/* Your USOS API Consumer Key and Secret. Visit developers page to get one. */
$consumer_key = $opts['usosplan_api_key'];
$consumer_secret = $opts['usosplan_api_secret'];
/* Required scopes. The only functionality of this application is to say hello,
* so it does not really require any. But, if you want, you may access user's
* email, just do the following:
* - put array('email') here,
* - append 'email' to the 'fields' argument of 'services/users/user' method,
* you will find it below in this script.
*/
$scopes = array('studies');
/*
* This application stores User's Access Token in $_SESSION. This means
* that it is allowed to act on User's behalf until the session OR
* Access Token is expired. When session expires, it will redo the
* authorization process. If the User is logged in at that time, he
* won't see the authorization screen, because USOS API remembers that
* he already authorized this application (the authorization notice
* will be skipped).
*/

/* Some USOS API methods require secure connection. Usually you'll
* want to use SSL only for these methods which require you to do so
* - most notably, the authorization dance. Using it for all other
* methods will probably degrade performance of your application. */
$secure_base_url = str_replace("http://", "https://", $usosapi_base_url);
$req_url = $secure_base_url.'services/oauth/request_token?scopes='.implode("|", $scopes);
$authurl = $secure_base_url.'services/oauth/authorize';
$acc_url = $secure_base_url.'services/oauth/access_token';
class States
{
const BEFORE_AUTH = 1;
const AUTH_IN_PROGRESS = 2;
const AFTER_AUTH = 3;
}
/* Determine session state and page to be displayed. */
if (!isset($_SESSION['state']))
	$_SESSION['state'] = States::BEFORE_AUTH;
if (isset($_GET['reset']))
	$_SESSION['state'] = States::BEFORE_AUTH;
print_r($_SESSION['state']."\n".$_SESSION['token']."\n".$_SESSION['secret']."\n");
$page = isset($_GET['page']) ? $_GET['page'] : 'welcome';
if (!in_array($page, array('welcome', 'protected')))
	$page = 'welcome';
if ($page == 'welcome')
{
	print "<a href='?page=protected'>Click here to access a protected resource</a>";
}

require('inc/OAuth/OAuth.php');
require('inc/OAuth/OAuth1Client.php');

$oauth = new OAuth1Client($consumer_key, $consumer_secret);
$oauth->api_base_url      = "https://usosapps.uw.edu.pl/";
$oauth->request_token_url = "https://usosapps.uw.edu.pl/services/oauth/request_token?scopes=".implode("|", $scopes);
$oauth->access_token_url  = "https://usosapps.uw.edu.pl/services/oauth/access_token";
$oauth->authorize_url = "https://usosapps.uw.edu.pl/services/oauth/authorize";

if ($page == 'protected')
{


	//$oauth->enableDebug();
	if ($_SESSION['state'] == States::BEFORE_AUTH) {
		try {
			$request_token_info = $oauth->requestToken($self_url.'?page=protected');
		} catch (Exception $e) {
			echo "Error1: " . $e->getMessage() ."\n";
		}
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		$_SESSION['token'] = $request_token_info['oauth_token'];
		$_SESSION['state'] = States::AUTH_IN_PROGRESS;
		print_r($request_token_info);
		header('Location: '.$authurl.((strpos($authurl, '?') === false) ? '?' : '&').'oauth_token='.$request_token_info['oauth_token']);
	} else if ($_SESSION['state'] == States::AUTH_IN_PROGRESS) {
		if (!isset($_GET['oauth_token'])) {
			print "Failure <a href='?page=welcome&reset=true'>reset</a>";
		}
		$oauth->token = new OAuthToken ($_GET['oauth_token'],$_SESSION['secret']);
		echo "sending seret " . $_SESSION['secret']. "\n";
		try {
			$access_token_info = $oauth->accessToken($_GET['oauth_verifier']);
			$_SESSION['state'] = States::AFTER_AUTH;
			$_SESSION['token'] = $access_token_info['oauth_token'];
			$_SESSION['secret'] = $access_token_info['oauth_token_secret'];
			
			echo "got access token: " . $_SESSION['secret'];
			
			header('Location: '.$self_url.'?page=protected');
		} catch (Exception $e) {
			echo "Exception: ". $e->getMessage() ."\n";
		}
	} else if ($_SESSION['state'] == States::AFTER_AUTH) {
		$oauth->token = new OAuthToken($_SESSION['token'], $_SESSION['secret']);
		try {
		//$json = $result = $oauth->get($usosapi_base_url."services/users/user?fields=id|first_name|last_name|sex|homepage_url|profile_url|employment_positions|student_programmes");
//		$json = $result = $oauth->get($usosapi_base_url."services/groups/user?active_terms=true&fields=course_name|course_id&lang=".substr(get_locale(), 0, 2)."&dupa=true");
//		print_r($json);
		/*$result = file_get_contents($usosapi_base_url."services/tt/course_edition?course_id=".$opts["usosplan_course_id"]."&term_id=".$opts["usosplan_term_id"]);
		$json = json_decode($result);
		$lang = substr(get_locale(), 0, 2);
		foreach ($json as $val) {
			$val->name = $val->name->$lang;
		}*/


		

		print "Success";
		} catch (Exception $error) {
			echo "Error2: ". $error->getMessage()."\n";
		}
		//print_r($result);
	}
}
print_r(get_timetable());

function get_timetable () {
	$opts = get_option('usosplan_option_name');
	$lang = substr(get_locale(), 0, 2);
	if ($_SESSION['state'] == States::AFTER_AUTH) {
		echo "inside\n";
		$oauth = new OAuth1Client($opts['usosplan_api_key'], $opts['usosplan_api_secret']);
		$oauth->api_base_url      = $opts['usosplan_api_base_url'];
		$oauth->token = new OAuthToken($_SESSION['token'], $_SESSION['secret']);
		try {
			$json = $oauth->get("services/tt/user?fields=start_time|course_id|name|course_name|classtype_name");
			$copy = array();
			foreach ($json as $elem) {
				$elem->name= $elem->name->$lang;
				if ($elem->course_id == $opts['usosplan_course_id'])
					$copy[]= $elem;
			}
			return $copy;
		} catch (Exception $e) {
			echo "Error: ".$e->getMessage()."\n";
			return $data;
		}
	}
	$data = json_decode(file_get_contents($opts['usosplan_api_base_url']."services/tt/course_edition?course_id=".$opts["usosplan_course_id"]."&term_id=".$opts["usosplan_term_id"]."&fields=group_number|start_time|name|course_name|classtype_name"));
	foreach ($data as $e) {
		$e->name = $e->name->$lang;
	}
	return $data;
}
?> 
