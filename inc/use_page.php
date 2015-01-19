<?php

function use_page_form_apply () {
	global $_REQUEST, $use_page_errors, $post, $wpdb;
	if (get_current_user_id() == 0)
		return;
	$use_page_errors = new WP_Error();
	if (isset($_REQUEST['use_page'])) {
		if ($post->post_author != get_current_user_id()) {
			$use_page_errors->add(1, 'You are not the author of this post');
			return;
		}
		switch ($_REQUEST['use_page']) {
			case 'publish_notes':
				$decl_id = intval($_REQUEST['note_decl']);
				$wpdb->insert($wpdb->prefix.'usos_notes', array(
					'time' => time(),
					'page_id' => $post->ID,
					'user_id' => get_current_user_id(),
				));
				if ($decl_id != -1) {
					$wpdb->query('UPDATE `'.$wpdb->prefix.'usos_note_decl` SET notes_id='.$wpdb->insert_id." WHERE ID=$decl_id");
				}
				$use_page_errors->add(2, 'Notes published');
			break;
			case 'add_homework':
				$group_id = ($_REQUEST['group_number'] == '') ? 'NULL' : "'".intval($_REQUEST['group_number'])."'";
				$wpdb->query('INSERT INTO `'.$wpdb->prefix.'usos_homework` VALUES (NULL, '.$post->ID.", $group_id, ".strtotime($_REQUEST['homework_time']).", ".get_current_user_id().')');
				$use_page_errors->add(4, 'Added homework');
			break;
			case 'add_test':
				$group_id = ($_REQUEST['test_group_number'] == '') ? 'NULL' : "'".intval($_REQUEST['test_group_number'])."'";
				$time = strtotime($_REQUEST['test_date'].' '.$_REQUEST['test_time']);
				$wpdb->query('INSERT INTO `'.$wpdb->prefix.'usos_test` VALUES ('.get_current_user_id().', '.$post->ID.", $group_id, ".intval($time).")");
				$use_page_errors->add(5, 'Announced a test');
			break;
			case 'hand_in_homework':
				$homework_id = intval($_REQUEST['homework_id']);
				$wpdb->query('INSERT INTO `'.$wpdb->prefix.'usos_hand_in` VALUES (NULL, '.get_current_user_id().', '.$post->ID.", $homework_id, ".time().")");
				$wpdb->query('UPDATE `'.$wpdb->prefix.'posts` SET post_status=\'private\' WHERE ID='.$post->ID);
				$use_page_errors->add(6, 'Homework handed in');
			break;
			default: break;
		}	
	}
}

function note_decl_form_apply () {
	global $_REQUEST, $wpdb;
	if (get_current_user_id() == 0)
		return;
	if (isset($_REQUEST['usos_note_declaration'])) {
		$wpdb->query('INSERT INTO `'.$wpdb->prefix.'usos_note_decl` VALUES (NULL, \''.esc_sql($_REQUEST['usos_note_declaration']).'\', NULL, '.get_current_user_id().', '.time().')');
		header("Location: ".$_SERVER['REQUEST_URI']);
	}
}
?>
