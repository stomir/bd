<?php

function usos_login_function () {
	if (!isset($_SESSION))
		session_start();
	if (!isset($_SESSION['state']))
		$_SESSION['state'] = 0;
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	if (!in_array($action, array('login', 'logout')))
		$action = 'login';
	if (isset($_REQUEST['loggedout']))
		$action = 'logout';
	$opts = get_option('usosplan_option_name');
	switch ($action) {
		case 'login':
			$usosapi_base_url = $opts['usosplan_api_base_url'];
			$self_url = home_url('wp-login.php?action=php');

			$consumer_key = $opts['usosplan_api_key'];
			$consumer_secret = $opts['usosplan_api_secret'];
			
			$scopes = array('studies');

			$secure_base_url = str_replace("http://", "https://", $usosapi_base_url);
			$req_url = $secure_base_url.'services/oauth/request_token?scopes='.implode("|", $scopes);
			$authurl = $secure_base_url.'services/oauth/authorize';
			$acc_url = $secure_base_url.'services/oauth/access_token';
			
			require_once('OAuth/OAuth.php');
			require_once('OAuth/OAuth1Client.php');

			$oauth = new OAuth1Client($consumer_key, $consumer_secret);
			$oauth->api_base_url      = $usosapi_base_url;
			$oauth->request_token_url = $req_url;
			$oauth->access_token_url  = $acc_url;
			$oauth->authorize_url = $authurl;


			if ($_SESSION['state'] == 0) {			
				try {
					echo $self_url;
					$request_token_info = $oauth->requestToken($self_url);
				} catch (Exception $e) {
					echo "Error1: " . $e->getMessage() ."\n";
				}
				$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
				$_SESSION['token'] = $request_token_info['oauth_token'];
				//print_r($request_token_info);
				$_SESSION['state'] = 1;
				$_SESSION['redirect_to'] = (isset($_REQUEST['redirect_to'])) ? $_REQUEST['redirect_to'] : home_url('');
				header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
				exit;
			} elseif ($_SESSION['state'] == 1) {
				if (!isset($_GET['oauth_token'])) {
					$_SESSION['state'] = 0;
					echo 'FAILURE getting token';
					header("Location: ".$_SESSION['redirect_to']);
					exit;
				}
				$oauth->token = new OAuthToken ($_GET['oauth_token'],$_SESSION['secret']);
				try {
					$access_token_info = $oauth->accessToken($_GET['oauth_verifier']);
				} catch (Exception $e) {
					echo "Error getting access token\n";
					$_SESSION['state'] = 0;
					die;
				}
				$_SESSION['token'] = $access_token_info['oauth_token'];
				$_SESSION['secret'] = $access_token_info['oauth_token_secret'];	
				
				try {
					$json = $oauth->get("services/users/user?fields=id");
				} catch (Exception $e) {
					echo "Error get user_id";
					$_SESSION['state'] = 0;
					die;
				}

				$usos_user_id = $json->id;
				
				require_once('database.php');

				wp_set_auth_cookie(get_user_by_usos_user_id($usos_user_id));


				$_SESSION['state'] = 200;
				wp_safe_redirect( $_SESSION['redirect_to'] );
				exit;
			} elseif ($_SESSION['state'] == 200) {
				if (!is_user_logged_in()) {
					$_SESSION['state'] = 0;
					header("Location: $self_url");
				}
			}

			exit;
		break;
		case 'logout':
			wp_logout();
			$_SESSION['state'] = 0;
			
			$redirect_to = $opts['usosplan_logout_url'];
			header("Location: $redirect_to");
			exit;
		break;
		case 'none':
			if (isset($_REQUEST['redirect_to']))
				echo "<a href=\"?action=login&go=true&redirect_to=". urlencode($_REQUEST['redirect_to']) ."\">log in</a>";
			else
				echo "<a href=\"?action=login&go=true\">log in</a>";
		break;
		default:
			if ($action != 'none')
				exit;
	}
	if ($_SESSION['state'] == 0) {
		wp_logout();
//		exit;
	}
	$_REQUEST = array();
}

?>
