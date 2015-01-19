<?php

class Usos_Use_Page extends WP_Widget {

	private $formats = array( 'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery' );

	public function __construct () {
		parent::__construct('usosplan_use_page', __('USOS Use page...', 'usos use page'), array(
			'classname' => 'usosplan_use_page',
			'description' => __("Widget for using the post in UsosPlan system."),
		));
	}

	public function widget ($args, $instance) {
		global $post, $use_page_errors, $wpdb;

		require_once(dirname(__FILE__).'/../inc/calendar.php');
		?> <aside> <h1 class="widget-title"> <?php echo $instance['title']; ?> </h1> <?php
		if (isset($use_page_errors)) {
			foreach ($use_page_errors->get_error_codes() as $code) {
				echo "<p class=\"use_page_error\">".$use_page_errors->get_error_message($code)."</p>";
			}
		}
		?> 
		<form method="post" id="use_page_form">
		<select name="use_page" id="use_page_select">
			<option value="add_homework">Add homework</option>
			<option value="hand_in_homework">Hand in homework</option>
			<option value="publish_notes">Publish notes</option>
			<option value="add_test">Announce a test</option>
		</select>
		<input type="submit" value="Use" />

		<div class="use_page_form_part" id="publish_notes_part">
		Fulfill declaration:
		<select name="note_decl">
			<option value="-1">none</option>
			<?php
				$result = $wpdb->get_results('SELECT ID, declaration FROM `'.$wpdb->prefix.'usos_note_decl` WHERE notes_id IS NULL AND user_id='.get_current_user_id());
				foreach ($result as $row) {
					echo '<option value="'.$row->ID.'">'.$row->declaration."</option>";
				}
			?>
		</select>
		</div>

		<div class="use_page_form_part" id="hand_in_homework_part">
		<select name="homework_id">
			<?php
				$sql = 'SELECT home.ID, page.post_title FROM `'.$wpdb->prefix.'usos_homework` AS home INNER JOIN `'.
					$wpdb->prefix.'posts` AS page ON page.ID = page_id';
				$group_number = get_group_number();
				if ($group_number != -1 && $group_number != '') {
					$sql .= " WHERE group_number = $group_number OR group_number IS NULL";
				}
				$result = $wpdb->get_results($sql);
				foreach ($result as $row) {
					echo '<option value="'.$row->ID.'">'.esc_html($row->post_title).'</option>';
				}
			?>
		</select>
		</div>
		
		<div class="use_page_form_part" id="add_homework_part">
		Group number (leave empty if for all): <input type="text" name="group_number" /><br/><br/>
		Completion time: <input type="text" name="homework_time" id="homework_time" />
		</div>
		
		<div class="use_page_form_part" id="add_test_part">
		Group number (leave empty if for all): <input type="text" name="test_group_number" /><br/><br/>
		Test date: <input type="text" name="test_date" id="test_date" /><br/><br/>
		Test time: <input type="text" name="test_time" id="test_time" />
		</form>
		
		</aside> <?php
	}

	public function update ($new_instance, $instance) {
		$instance['title']  = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function form ($instance) {
		$title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
	?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'twentyfourteen' ); ?></label>
		                        <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>
<?php
	}
}

?>
