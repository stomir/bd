<?php
class UsosPlanSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'UsosPlan Settings', 
			'manage_options', 
			'usosplan-setting-admin', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'usosplan_option_name' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>UsosPlan Settings</h2>		   
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'usosplan_option_group' );   
				do_settings_sections( 'usosplan-setting-admin' );
				submit_button(); 
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{		
		register_setting(
			'usosplan_option_group', // Option group
			'usosplan_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'UsosPlan Custom Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'usosplan-setting-admin' // Page
		);  
		
		add_settings_field(
			'usosplan_api_base_url', // ID
			'Usos API base URL', // Title 
			array( $this, 'usosplan_api_base_url_callback' ), // Callback
			'usosplan-setting-admin', // Page
			'setting_section_id' // Section		   
		);	  
		
		add_settings_field(
			'usosplan_logout_url', // ID
			'Usos logout URL', // Title 
			array( $this, 'usosplan_logout_url_callback' ), // Callback
			'usosplan-setting-admin', // Page
			'setting_section_id' // Section		   
		);	  

		add_settings_field(
			'usosplan_api_key', // ID
			'Usos API Key', // Title 
			array( $this, 'usosplan_api_key_callback' ), // Callback
			'usosplan-setting-admin', // Page
			'setting_section_id' // Section		   
		);	  
		

		add_settings_field(
			'usosplan_api_secret', 
			'Usos API Secret', 
			array( $this, 'usosplan_api_secret_callback' ), 
			'usosplan-setting-admin', 
			'setting_section_id'
		);	  
		add_settings_field(
			'usosplan_course_id', 
			'Usos Course ID', 
			array( $this, 'usosplan_course_id_callback' ), 
			'usosplan-setting-admin', 
			'setting_section_id'
		);	  
		add_settings_field(
			'usosplan_term_id',
			'Usos Term ID', 
			array( $this, 'usosplan_term_id_callback' ), 
			'usosplan-setting-admin', 
			'setting_section_id'
		);	  
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['usosplan_api_base_url'] ) )
			$new_input['usosplan_api_base_url'] = sanitize_text_field ( $input['usosplan_api_base_url'] );
		
		if( isset( $input['usosplan_logout_url'] ) )
			$new_input['usosplan_logout_url'] = sanitize_text_field ( $input['usosplan_logout_url'] );
		
		if( isset( $input['usosplan_api_key'] ) )
			$new_input['usosplan_api_key'] = sanitize_text_field ( $input['usosplan_api_key'] );

		if( isset( $input['usosplan_api_secret'] ) )
			$new_input['usosplan_api_secret'] = sanitize_text_field( $input['usosplan_api_secret'] );
		
		if( isset( $input['usosplan_course_id'] ) )
			$new_input['usosplan_course_id'] = sanitize_text_field( $input['usosplan_course_id'] );
		
		if( isset( $input['usosplan_term_id'] ) )
			$new_input['usosplan_term_id'] = sanitize_text_field( $input['usosplan_term_id'] );

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		print 'Enter your settings below:';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function usosplan_api_key_callback()
	{
		printf(
			'<input type="text" id="usosplan_api_key" name="usosplan_option_name[usosplan_api_key]" value="%s" />',
			isset( $this->options['usosplan_api_key'] ) ? esc_attr( $this->options['usosplan_api_key']) : ''
		);
	}
	
	public function usosplan_api_base_url_callback()
	{
		printf(
			'<input type="text" id="usosplan_api_base_url" name="usosplan_option_name[usosplan_api_base_url]" value="%s" />',
			isset( $this->options['usosplan_api_base_url'] ) ? esc_attr( $this->options['usosplan_api_base_url']) : ''
		);
	}
	
	public function usosplan_logout_url_callback()
	{
		printf(
			'<input type="text" id="usosplan_logout_url" name="usosplan_option_name[usosplan_logout_url]" value="%s" />',
			isset( $this->options['usosplan_logout_url'] ) ? esc_attr( $this->options['usosplan_logout_url']) : ''
		);
	}
	
	public function usosplan_course_id_callback()
	{
		printf(
			'<input type="text" id="usosplan_course_id" name="usosplan_option_name[usosplan_course_id]" value="%s" />',
			isset( $this->options['usosplan_course_id'] ) ? esc_attr( $this->options['usosplan_course_id']) : ''
		);
	}
	
	public function usosplan_term_id_callback()
	{
		printf(
			'<input type="text" id="usosplan_term_id" name="usosplan_option_name[usosplan_term_id]" value="%s" />',
			isset( $this->options['usosplan_term_id'] ) ? esc_attr( $this->options['usosplan_term_id']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function usosplan_api_secret_callback()
	{
		printf(
			'<input type="text" id="usosplan_api_secret" name="usosplan_option_name[usosplan_api_secret]" value="%s" />',
			isset( $this->options['usosplan_api_secret'] ) ? esc_attr( $this->options['usosplan_api_secret']) : ''
		);
	}
}

if( is_admin() )
	$usosplan_settings_page = new UsosPlanSettingsPage();
