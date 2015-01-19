<?php

class Usos_Notes extends WP_Widget {

	private $formats = array( 'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery' );

	public function __construct () {
		parent::__construct('usosplan_notes', __('USOS Notes', 'usos notes'), array(
			'classname' => 'usosplan_notes',
			'description' => __("Widget for viewing/managing notes."),
		));
	}

	public function widget ($args, $instance) {
		global $post, $use_page_errors, $wpdb;

		?> <aside> <?php
			if ($instance['title'] != '') { ?>
			<h1 class="widget-title"> <?php echo $instance['title']; ?> </h1> <?php
			}
		?>
		<h2>Unfulfilled declarations</h2>
		<ul> <?php
			$result = $wpdb->get_results('SELECT decl.declaration, user.display_name, time FROM `'.$wpdb->prefix.'usos_note_decl` AS decl INNER JOIN `'.
				$wpdb->prefix."users` AS user ON user.ID = decl.user_id WHERE decl.notes_id IS NULL");
			foreach ($result as $row) {
				echo "<li>".esc_html($row->declaration)." <span class=\"note_decl_comment\">".esc_html($row->display_name)." ".date(get_option('date_format').' '.get_option('time_format'), $row->time)."</span></li>";
			}
			if (empty($result)) {
				echo "<li>none</li>";
			}
		?>
		</ul>
		<h2>Notes</h2>
		<ul> <?php
			$result = $wpdb->get_results('SELECT note.page_id, post.post_title, user.display_name, note.time, decl.declaration FROM `'.$wpdb->prefix.'usos_notes` AS note LEFT JOIN`'.
				$wpdb->prefix.'usos_note_decl` AS decl ON note.ID = decl.notes_id INNER JOIN `'.$wpdb->prefix.'users` AS user ON user.ID = note.user_id INNER JOIN `'.
				$wpdb->prefix.'posts` AS post ON post.ID = note.page_id');
			foreach ($result as $row) {
				echo "<li>";
				echo '<a href="'.get_permalink($row->page_id).'" class="note_link">';
				echo esc_html($row->post_title);
				echo '</a>';
				echo '<span class="note_comment">'.esc_html($row->display_name)." ";
				echo date(get_option('date_format').' '.get_option('time_format'), $row->time);
				echo '</span>';
				if (isset($row->declaration)) 
					echo "(declared as <span class=\"note_decl_comment\">".esc_html($row->declaration).'</span>)';
				echo '</li>';
			}
			if (empty($result)) {
				echo '<li>none</li>';
			}
		?>
		</ul>

		<h2>Make a declaration</h2>
		<div class="note_decl_form">
		<form method="post">
		<input type="text" name="usos_note_declaration" />
		<input type="submit" />
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
