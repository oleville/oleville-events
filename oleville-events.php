<?php
/*
    Plugin Name: Oleville Events
    Plugin URI: http://www.oleville.com
    Description: Provides Event Functionality
    Version: 1.1
    Author: Nick Nooney
    License: GPL2
*/
/*
Copyright 2014  Nick Nooney  (email : nicholasnooney@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('Oleville_Events'))
{
	class Oleville_Events
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// Register Event Post Type
			require_once(sprintf("%s/event-type.php", dirname(__FILE__)));
			$oleville_events_type = new Oleville_Events_Type();

			// Register Email Post Type
			require_once(sprintf("%s/email-type.php", dirname(__FILE__)));
			$oleville_events_type = new Oleville_Emails_Type();

			// Register Email Post Type
			require_once(sprintf("%s/shortcodes.php", dirname(__FILE__)));
			$oleville_events_type = new Oleville_Events_Shortcode();

			// Initialize Settings
			require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			$oleville_events_settings = new Oleville_Events_Settings();
			
			// Initialize Misc
			require_once(sprintf("%s/misc.php", dirname(__FILE__)));
			$oleville_events_misc = new Oleville_Events_Misc();
			
			
		}

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			//wp_schedule_event( time(), 'three_min', 'happening_now_cache_hook' );
		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			wp_clear_scheduled_hook( 'happening_now_cache_hook' );
			
			// Remove filter for the SWT plugin
			remove_filter('sitewide_tags_allowed_post_types', 'sitewide_tags_filter_events');
		}
	}
}

if(class_exists('Oleville_Events'))
{
	// Register the plugin hooks to WordPress
	register_activation_hook(__FILE__, array('Oleville_Events', 'activate'));
    register_deactivation_hook(__FILE__, array('Oleville_Events', 'deactivate'));

	// Instatiate the plugin class
	$oleville_events = new Oleville_Events();
}

// Global PHP function for the Happening now part
function oleville_happening_now() {
	return get_site_option('happening_now_cache');
}

// PHP function for custom ordering of the SQL query results
function oleville_most_recent_post($order) {
	global $wpdb;
	return "mt1.meta_value, mt2.meta_value ASC";
}
