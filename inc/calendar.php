<?php

function usos_timetable_comparator ($a, $b) {
	return $a->time > $b->time;
}

function usos_add_calendar_events (&$data, $tab_name) {
	global $wpdb;
	$sql = 'SELECT page_id, time, post.post_title FROM `'.$wpdb->prefix."$tab_name` INNER JOIN `".$wpdb->prefix.'posts` AS post ON post.ID = page_id WHERE time > '.time();
	$group_number = get_group_number();
	if ($group_number != -1 && $group_number != '')
		$sql.= " AND (group_number = $group_number OR group_number IS NULL)";
	$result = $wpdb->get_results($sql);

	foreach ($result as $row) {
		$obj = new stdClass();
		$obj->time = date('Y-m-d H:i', $row->time);
		$obj->text = '<a href="'.get_permalink($row->page_id).'">'.$row->post_title.'</a>';
		$data[] = $obj;
	}
}

function print_usos_timetable () {
	global $wpdb;
	require_once("usosapi.php");
	

	$data = get_usos_timetable();

	usos_add_calendar_events($data, 'usos_homework');
	usos_add_calendar_events($data, 'usos_test');

	usort($data, "usos_timetable_comparator");
	echo "<ul class=\"usos_timetable\">\n";
	foreach ($data as $row) {
		echo "<li>".$row->text.' ('.date(get_option('date_format').' '.get_option('time_format'), strtotime($row->time)).")</li>\n";
	}
	echo "</ul>\n";
}

?>
