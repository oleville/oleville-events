<?php
if(!class_exists('Oleville_Events_Type'))
{
	class Oleville_Events_Type
	{
		const POST_TYPE = "event";
		// This list matches the field values in templates/event_metabox.php
		private $_meta = array(
			'description',
			'location',
			'start_date',
			'start_time',
			'end_date',
			'end_time',
			'facebook_link',
			'youtube_link',
			'movie_type'
		);

		/**
		 * Constructor
		 */
		public function __construct()
		{
			// Register Action Hooks
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
		}

		/**
		 * Function hooked to WP's init action
		 */
		public function init()
		{
			// Initialize the Post Type
			$this->create_post_type();
			add_action('save_post', array(&$this, 'save_post'));
			add_action('wp_trash_post', array(&$this, 'delete_post'));

			// Add a filter for the SWT plugin
			add_filter('sitewide_tags_allowed_post_types', array(&$this, 'sitewide_tags_filter_events'));
		}

		/**
		 * Create the post type
		 */
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Events', 'post type general name' ),
				'singular_name'      => _x( 'Event', 'post type singular name' ),
				'add_new'            => _x( 'Add New Event', 'event' ),
				'add_new_item'       => __( 'Add New Event' ),
				'edit_item'          => __( 'Edit Event' ),
				'new_item'           => __( 'New Event' ),
				'all_items'          => __( 'All Events' ),
				'view_item'          => __( 'View Event' ),
				'search_items'       => __( 'Search Events' ),
				'not_found'          => __( 'No events found' ),
				'not_found_in_trash' => __( 'No events found in the Trash' ),
				'parent_item_colon'  => '',
				'menu_name'          => 'Events & Newsletters'
			);
			$args = array(
				'labels'        => $labels,
				'description'   => 'Holds Branch Events',
				'public'        => true,
				'menu_position' => 5,
				'supports'      => array( 'title', 'thumbnail'),
				'has_archive'   => true,
				'menu_icon'     => 'dashicons-calendar-alt',
				'taxonomies'    => array('category'),
			);
			register_post_type(self::POST_TYPE, $args );
		}

		/**
		 * Save the metaboxes for this custom post type
		 */
		public function save_post($post_id)
		{
			// Check if this is an auto save routine
			if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			{
				return;
			}

			if(isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
			{
				foreach($this->_meta as $field_name)
				{
					if($field_name == 'end_date' || $field_name == 'start_date')
					{
						update_post_meta($post_id, $field_name, isset($_POST[$field_name]) ? $_POST[$field_name]  : '0');
					}
					else
					{
						// Update the post's meta field
						update_post_meta($post_id, $field_name, isset($_POST[$field_name]) ? $_POST[$field_name] : '0');
					}
				}

				// Store recurrances of events by building an array of the repeating events,
				// then updating the post meta with the updated list of events
				$max_repeats = (integer)$_POST['max_repeats'];

				// Build the array of repeating events
				$repeat_list = array();
				for($i = 0; $i <= $max_repeats; $i++)
				{
					if(isset($_POST['sd'.$i], $_POST['st'.$i], $_POST['et'.$i], $_POST['ed'.$i]) &&
						$_POST['sd'.$i] != '' && $_POST['st'.$i] != '' && $_POST['et'.$i] != '' && $_POST['ed'.$i] != '')
					{
						// Save the repeating event
						array_push($repeat_list, array(
							$_POST['sd'.$i],
							$_POST['st'.$i],
							$_POST['et'.$i],
							$_POST['ed'.$i]
						));
					}
				}
				update_post_meta($post_id, 'repeat', serialize($repeat_list));


				// Only update the branch name once
				if(get_post_meta($post_id, 'branch', true) == '')
				{
					update_post_meta($post_id, 'branch', get_bloginfo('name'));
				}
				$latest_cat = get_term_by('slug', 'latest', 'category');

				wp_set_post_terms( $post_id, $latest_cat->term_id , 'category', true );

				// Call the function to update the calendar with the new information
				//require_once(sprintf("%s/google-calendar/save-event.php", dirname(__FILE__)));
			}
			else
			{
				return;
			}
		}

		/**
		 * Delete the metaboxes for this custom post type
		 */
		public function delete_post($post_id)
		{
			// Perhaps some sort of authentication should be performed here

			// Authentication is valid; delete the event from the google calendar
			require_once(sprintf("%s/google-calendar/delete-event.php", dirname(__FILE__)));
		}

		/**
		 * Helper function for adding SWT support for this plugin
		 */
		public function sitewide_tags_filter_events($posts) {
			$posts[self::POST_TYPE] = true;
			return $posts;
		}

		/**
		 * Function hooked to WP's admin_init action
		 */
		public function admin_init()
		{
			add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
			add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		}

		/**
		 * Function hooked to WP's add_meta_boxes action
		 */
		public function add_meta_boxes()
		{
			add_meta_box(
				sprintf('oleville_events_%s_section', self::POST_TYPE),
				sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
				array(&$this, 'add_inner_meta_boxes'),
				self::POST_TYPE,
				'normal',
				'high'
			);
		}

		/**
		 * Function called by add_meta_boxes
		 */
		public function add_inner_meta_boxes($post)
		{
			wp_nonce_field( plugin_basename(__FILE__), sprintf('oleville_events_%s_section_nonce', self::POST_TYPE));
			// Include the template for the metabox
			include(sprintf("%s/templates/event_metabox.php", dirname(__FILE__)));
		}

		/**
		 * Function hooked to WP's admin_enqueue_scripts action
		 */
		public function admin_enqueue_scripts($hook)
		{
			global $post;
			// Check to see that these scripts are only loaded for post-new.php
			// and the type is eventmail
			if('post-new.php' != $hook && 'post.php' != $hook)
				return;
			if($_GET['post_type'] != self::POST_TYPE && 'post.php' != $hook)
				return;

			// Register the JS and CSS files with WordPress
			wp_register_style(
				'oleville-events-event-css',
				plugins_url('templates/css/event_metabox.css', __FILE__)
			);
			wp_register_script(
				'oleville-events-event-js',
				plugins_url('templates/js/event_metabox.js', __FILE__),
				array('jquery', 'jquery-ui-datepicker')
			);
			wp_register_script(
				'oleville-events-timepicker-js',
				plugins_url('templates/js/jquery.timepicker.min.js', __FILE__),
				array('jquery')
			);

			// Localize some values so that the JavaScript knows what's up
			wp_localize_script(
				'oleville-events-event-js',
				'repeat',
				array(
					'events' => json_encode(unserialize(get_post_meta($post->ID, 'repeat', TRUE))),
				)
			);

			// Enqueue the styles and scripts
			wp_enqueue_style('oleville-events-event-css');
			wp_enqueue_script('oleville-events-event-js');
			wp_enqueue_script('oleville-events-timepicker-js');
		}
	}
}
