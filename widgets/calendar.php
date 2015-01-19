<?php

class Usos_Timetable extends WP_Widget {

	private $formats = array( 'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery' );

	public function __construct () {
		parent::__construct('widget_usosplan_timetable', __('USOS Timetable', 'usos timetable'), array(
			'classname' => 'widget_usosplan_timetable',
			'description' => __("This week\'s classes and other events"),
		));
	}

	public function widget ($args, $instance) {
		require_once(dirname(__FILE__).'/../inc/calendar.php');
		?> <aside> <h1 class="widget-title"> <?php echo $instance['title']; ?> </h1> <?php
		print_usos_timetable();
		echo "</aside>";
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
