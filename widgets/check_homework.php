<?php

require_once(get_template_directory().'/inc/database.php');

class Usos_Check_Homework extends WP_Widget {

	private $formats = array( 'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery' );

	public function __construct () {
		parent::__construct('widget_usosplan_check_homework', __('USOS Check Homework', 'usos check_homework'), array(
			'classname' => 'widget_usosplan_check_homework',
			'description' => __("Display handed in homework."),
		));
	}

	public function widget ($args, $instance) {
		global $wpdb;
		?> <aside> <h1 class="widget-title"> <?php echo $instance['title']; ?> </h1> 
		<h2>Homework added by you</h2>
		<ul>
		<?php
		$result = $wpdb->get_results('SELECT page.post_title, homework.ID, homework.time FROM `'.$wpdb->prefix.'usos_homework` AS homework'.
			' INNER JOIN `'.$wpdb->prefix.'posts` AS page ON page.ID = homework.page_id'.
			' WHERE user_id = '.get_current_user_id());

		foreach ($result as $row) {
			echo '<li>'.$row->post_title.' (';
			if ($row->time > time())
				echo '<span style="color: red;">';
			echo date(get_option('date_format').' '.get_option('time_format'), $row->time);
			if ($row->time > time())
				echo '</span>';
			echo ')';
			$hand_ins = $wpdb->get_results('SELECT hand.page_id, page.post_title, hand.time, user.display_name, hand.user_id FROM `'.$wpdb->prefix.'usos_hand_in` AS hand INNER JOIN `'.
				$wpdb->prefix.'posts` AS page ON page.ID = hand.page_id INNER JOIN `'.$wpdb->prefix.'users` AS user ON user.ID = hand.user_id '.
				'WHERE homework_id = '.$row->ID. ' ORDER BY hand.time ASC'
				);
			echo '<ul>';
			foreach ($hand_ins as $hand_in) {
				echo '<li><a href="'.get_permalink($hand_in->page_id).'">'.esc_html($hand_in->post_title).' by '.$hand_in->display_name.'</a> (<span';
				if ($row->time < $hand_in->time)
					echo ' style="color: red"';
				echo '>';
				echo date(get_option('date_format').' '.get_option('time_format'), $hand_in->time);
				echo '</span>)</li>';
			}
			if (empty($hand_ins)) {
				echo "<li>none</li>";
			}
			echo '</ul></li>';			
		}
		if (empty($result)) {
			echo "<li>none</li>";
		}
		?>
			</ul>
			<h2>Homework handed in by you</h2>
			<ul>
		<?php	
			$hand_ins = $wpdb->get_results('SELECT hand.page_id, page.post_title, hand.time, hand.homework_id FROM `'.$wpdb->prefix.'usos_hand_in` AS hand INNER JOIN `'.
				$wpdb->prefix.'posts` AS page ON page.ID = hand.page_id '.
				'WHERE hand.user_id = '.get_current_user_id());
			foreach ($hand_ins as $row) {
				echo '<li><a href="'.get_permalink($row->page_id).'">'.esc_html($row->post_title).'</a> (<span';
				$home = $wpdb->get_results('SELECT home.time, home.page_id, page.post_title, user.display_name, home.user_id FROM `'.$wpdb->prefix.'usos_homework` AS home INNER JOIN `'.
					$wpdb->prefix.'posts` AS page ON page.ID = home.page_id INNER JOIN `'.$wpdb->prefix.'users` AS user ON user.ID = home.user_id '.
					'WHERE home.ID = '.$row->homework_id);
				$home = $home[0];
				if ($row->time > $home->time)
					echo ' style="color: red"';
				echo '>';
				echo date(get_option('date_format').' '.get_option('time_format'), $row->time);
				echo '</span>) for <a href="'.get_permalink($home->page_id).'">'.esc_html($home->post_title).'</a> by '.$home->display_name;
				echo '</li>';
			}
			if (empty($hand_ins)) {
				echo '<li>none</li>';
			}
		?>
			</ul></aside>
		<?php
	}

	public function update ($new_instance, $instance) {
		$instance['title']  = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function form ($instance) {
		$title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
	?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
		                        <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>
<?php
	}
}

?>
