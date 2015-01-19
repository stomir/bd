<?php

function usosplan_database_install () {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;

	$usos_user = $wpdb->prefix."usos_user";
	$usos_notes = $wpdb->prefix."usos_notes";
	$usos_note_decl = $wpdb->prefix."usos_note_decl";
	$usos_homework = $wpdb->prefix."usos_homework";
	$usos_hand_in = $wpdb->prefix."usos_hand_in";
	$usos_badge = $wpdb->prefix."usos_badge";
	$usos_test = $wpdb->prefix."usos_test";

	$sql = "CREATE TABLE `$usos_user` (
		user_id INT UNIQUE NOT NULL,
		user_usos_id INT UNIQUE NOT NULL,
		group_number INT NOT NULL
	)";
	
	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_notes` (
		ID INT PRIMARY KEY AUTO_INCREMENT,
		time INT NOT NULL,
		page_id INT NOT NULL,
		user_id INT NOT NULL
	)";

	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_note_decl` (
		ID INT PRIMARY KEY AUTO_INCREMENT,
		declaration TEXT NOT NULL,
		notes_id INT,
		user_id INT NOT NULL,
		time INT NOT NULL
	)";

	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_homework` (
		ID INT PRIMARY KEY AUTO_INCREMENT,
		page_id INT NOT NULL,
		group_number INT,
		time INT NOT NULL,
		user_id INT NOT NULL
	)";

	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_hand_in` (
		ID INT PRIMARY KEY AUTO_INCREMENT,
		user_id INT NOT NULL,
		page_id INT NOT NULL,
		homework_id INT NOT NULL,
		time INT NOT NULL
	)";
	
	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_badge` (
		user_id INT NOT NULL,
		description VARCHAR(255) NOT NULL
	)";

	$wpdb->query($sql);

	$sql = "CREATE TABLE `$usos_test` (
		user_id INT NOT NULL,
		page_id INT NOT NULL,
		group_number INT,
		time INT NOT NULL
	)";

	$wpdb->query($sql);
}

function usosplan_database_uninstall () {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;

	$usos_user = $wpdb->prefix."usos_user";
	$usos_notes = $wpdb->prefix."usos_notes";
	$usos_note_decl = $wpdb->prefix."usos_note_decl";
	$usos_homework = $wpdb->prefix."usos_homework";
	$usos_hand_in = $wpdb->prefix."usos_hand_in";
	$usos_badge = $wpdb->prefix."usos_badge";
	$usos_test = $wpdb->prefix."usos_test";

	$rows = $wpdb->get_results("SELECT user_id FROM `".$wpdb->prefix."usos_user`");
	foreach ($rows as $row) {
		print_r($row);
		if ($row->user_id != 1) {
			wp_delete_user($row->user_id); 
			system("touch /tmp/delete".$row->user_id);
		}
	}

	$wpdb->query ("DROP TABLE `$usos_user`");
	$wpdb->query ("DROP TABLE `$usos_notes`");
	$wpdb->query ("DROP TABLE `$usos_note_decl`");
	$wpdb->query ("DROP TABLE `$usos_homework`");
	$wpdb->query ("DROP TABLE `$usos_hand_in`");
	$wpdb->query ("DROP TABLE `$usos_badge`");
	$wpdb->query ("DROP TABLE `$usos_test`");
}

function get_user_by_usos_user_id ($usos_id) {
	global $wpdb;
	$usos_id = intval($usos_id);
	$result = $wpdb->get_var ("SELECT user_id FROM `".$wpdb->prefix."usos_user` WHERE user_usos_id='".esc_sql($usos_id)."'");

	if ($result == 0) {
		require_once("usosapi.php");
		$data = get_usos_user_info($usos_id);
		if ($wpdb->query("SELECT user_id FROM `".$wpdb->prefix."usos_user` LIMIT 1") != 0) {
			//create new user
			
			$mail = ($data->email == '') ? 'nomail@example.com' : $data->email;

			$id = (register_new_user("usos".$usos_id, $mail));

			if (intval($id) != $id) {
				echo "errors creating user: ";
				print_r($id);
				exit;
			}

			$wpdb->query("UPDATE `".$wpdb->prefix."users` SET display_name='".esc_sql($data->first_name.' '.$data->last_name)."' WHERE ID = $id");

			$wpdb->query("INSERT INTO `".$wpdb->prefix."usos_user` VALUES ($id, $usos_id, '".$data->group_number."')");

			return $id;
		}
		//first login by USOS ever - this is administator
		$wpdb->query("INSERT INTO `".$wpdb->prefix."usos_user` VALUES(1, '".esc_sql($usos_id)."', '".$data->group_number."')");
		return 1;
	}
	return $result;
}

?>
