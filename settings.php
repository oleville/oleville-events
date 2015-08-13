<?php
if(!class_exists('Oleville_Events_Settings'))
{
	class Oleville_Events_Settings
	{
	
		private $_settings = array (
			'from_email',
			'to_email'
		);
		
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// Register action hooks
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
		}
		
		/**
		 * Function hooked to WP's admin_init action
		 */
		public function admin_init()
		{
			// Register settings here
			foreach($this->_settings as $setting_name)
			{
				register_setting('oleville_events_settings', $setting_name);
			}
			
			// Add Settings Sections here
			add_settings_section(
				'oleville_events_settings_section',
				'Settings',
				array(&$this, 'settings_section_email_callback'),
				'oleville_events'
			);
			
			// Add Settings Fields here
			add_settings_field(
				'from_email',
				'From Email',
				array(&$this, 'settings_field_input_text'),
				'oleville_events',
				'oleville_events_settings_section',
				array( // $args array
					'field' => 'from_email',
					'default' => 'olethelion@stolaf.edu',
				)
			);
			add_settings_field(
				'to_email',
				'To Email',
				array(&$this, 'settings_field_input_text'),
				'oleville_events',
				'oleville_events_settings_section',
				array( // $args array
					'field' => 'to_email',
					'default' => 'olethelion@stolaf.edu',
				)
			);
		}
		
		/**
		 * Add Helper Text to each settings field
		 */
		public function settings_field_input_text($args)
		{
			// Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field, $args['default']);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" placeholder="%s"/>', $field, $field, $value, $args['default']);
		}
		
		/**
		 * Add the settings page to the event tab in the admin menu
		 */
		public function add_menu()
		{
			remove_submenu_page('edit.php?post_type=event', 'edit-tags.php?taxonomy=category&amp;post_type=event');
			add_submenu_page(
				'edit.php?post_type=event',
				'Configuration Settings',
				'Settings',
				'manage_options',
				'oleville_events',
				array(&$this, 'generate_settings_form')
			);

    		remove_meta_box( 'categorydiv', 'event', 'side' );
		}
		
		/**
		 * This function generates the form that the user can see
		 */
		public function generate_settings_form()
		{
			if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
			
			// Render the template
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		}
	}
}