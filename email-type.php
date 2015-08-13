<?php
if(!class_exists('Oleville_Emails_Type'))
{
	class Oleville_Emails_Type
	{
		const POST_TYPE = "eventmail";
		// This list matches the field values in
		// templates/eventmail_metabox.php
		private $_meta = array(
			'mailfrom',
			'mailto',
			'mailcc',
			'mailbcc',
			'mailsubj',
			'mailbody',
			'mailtheme',
			'events'
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

			// Because the custom post type is in the submenu, we need
			// to manually re-add the Add New page for this type
			add_action('admin_menu', array(&$this, 'admin_menu'));

			// Add a filter for the SWT plugin
			add_filter('sitewide_tags_allowed_post_types', array(&$this, 'sitewide_tags_filter_eventmail'));

			// Register the Sending of the Email on Post Status Transistions
			$this->listen_post_status();
		}

		/**
		 * Create the post type
		 */
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Emails', 'post type general name' ),
				'singular_name'      => _x( 'Email', 'post type singular name' ),
				'add_new'            => _x( 'Add New Email', 'eventmail' ),
				'add_new_item'       => __( 'Add New Email' ),
				'edit_item'          => __( 'Edit Email' ),
				'new_item'           => __( 'New Email' ),
				'all_items'          => __( 'All Emails' ),
				'view_item'          => __( 'View Email' ),
				'search_items'       => __( 'Search Emails' ),
				'not_found'          => __( 'No emails found' ),
				'not_found_in_trash' => __( 'No emails found in the Trash' ),
				'parent_item_colon'  => '',
				'menu_name'          => 'Emails'
			);
			$args = array(
				'labels'        => $labels,
				'description'   => 'Holds Emails of Branch Events',
				'public'        => true,
				'menu_position' => 5,
				'supports'      => array('title', 'thumbnail'),
				'has_archive'   => true,
				'menu_icon'     => 'dashicons-email-alt',
				'show_in_menu'  => 'edit.php?post_type=event',

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
					// Update the post's meta field
					if($field_name != 'events')
					{
						update_post_meta($post_id, $field_name, $_POST[$field_name]);
					}
					else
					{
						update_post_meta($post_id, $field_name, serialize($_POST[$field_name]));
					}
				}
			}
			else
			{
				return;
			}
		}

		/**
		 * Function hooked to WP's admin_menu action
		 */
		public function admin_menu()
		{
			add_submenu_page(
				'edit.php?post_type=event',
				'Add New Email',
				'Add New Email',
				'manage_options',
				'post-new.php?post_type=eventmail',
				NULL
			);

			// This code changes the order of the submenus using the
			// global $submenu variable. It makes the order make more
			// sense by swapping the positions of 'Add New Email' and
			// 'Settings'
			// global $submenu;
			// $tmp = $submenu['edit.php?post_type=event'][12];
			// $submenu['edit.php?post_type=event'][12] = $submenu['edit.php?post_type=event'][13];
			// $submenu['edit.php?post_type=event'][13] = $tmp;
		}

		/**
		 * Helper function for adding SWT support for this plugin
		 */
		public function sitewide_tags_filter_eventmail($posts) {
			$posts[self::POST_TYPE] = true;
			return $posts;
		}

		/**
		 * Function to register the sending of emails when the post gets published
		 */
		public function listen_post_status()
		{
			// We want to send the email when the post first becomes published, either from 'new', 'draft', or 'future'
			add_action('publish_'.self::POST_TYPE, array(&$this, 'send_eventmail'));
		}

		/**
		 * Function that sends an email when a post is published
		 */
		public function send_eventmail($post_id)
		{
			write_log("Send_Eventmail called!");
			write_log($post_id);
			
			$post = get_post($post_id);

			add_filter('wp_mail_from', 'mail_from');
			add_filter('wp_mail_from_name', 'mail_from_name');
			// Use wp_mail to send the email, modifying the to and from fields within the $headers array
			error_log('From: Ole the Lion <'. get_post_meta($post_id, 'mailfrom', true) .'>');
			$headers = array(
				'content-type: text/html',  // Set the email to be an HTML formatted email
				'From: Ole the Lion <'. get_post_meta($post_id, 'mailfrom', true) .'>',
				'Cc: '.get_post_meta($post_id, 'mailcc', true).'',
				'Bcc: '.get_post_meta($post_id, 'mailbcc', true).'',
			);
			wp_mail(get_post_meta($post_id, 'mailto', true), get_post_meta($post_id, 'mailsubj', true), $this->generate_email($post_id), $headers);
			}

		/**
		 * Function that changes the mail from filter
		 */
		public function mail_from($content)
		{
			// This is the email address that all emails are set as from
			write_log('mail_from');
			write_log($content);
			write_log(get_option('from_email'));
			return get_option('from_email');
		}

		/**
		 * Function that changes the name of the email
		 */
		public function mail_from_name($name)
		{
			write_log('mail_from_name');
			write_log($name);
			return 'Ole the Lion';
		}

		/**
		 * Function hooked to WP's admin_init action
		 */
		public function admin_init()
		{
			add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
			add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
			add_action('wp_ajax_eventmail_query_update', array(&$this, 'eventmail_query_update'));
			add_action('wp_ajax_eventmail_theme_update', array(&$this, 'eventmail_theme_update'));
			add_action('wp_ajax_eventmail_event_update', array(&$this, 'eventmail_event_update'));
		}

		/**
		 * Function hooked to WP's add_meta_boxes action
		 */
		public function add_meta_boxes()
		{
			add_meta_box(
				sprintf('oleville_events_%s_section', self::POST_TYPE),
				'Email Builder',
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
			include(sprintf("%s/templates/eventmail_metabox.php", dirname(__FILE__)));
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
				'oleville-events-eventmail-css',
				plugins_url('templates/css/eventmail_metabox.css', __FILE__)
			);
			wp_register_script(
				'oleville-events-eventmail-js',
				plugins_url('templates/js/eventmail_metabox.js', __FILE__),
				array('jquery')
			);

			// Localize some values so that the JavaScript knows what's up
			wp_localize_script(
				'oleville-events-eventmail-js',
				'ajaxRequest',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('eventmail-ajax-nonce'),
					'events' => json_encode(unserialize(get_post_meta($post->ID, 'events', TRUE))),
				)
			);

			// Add them to load for our plugin
			wp_enqueue_style('oleville-events-eventmail-css');
			wp_enqueue_script('oleville-events-eventmail-js'); // Automatically loads jQuery too
		}

		/**
		 * Function that handles AJAX requests from the event query part
		 */
		public function eventmail_query_update()
		{
			// Verify the nonce value created for the script
			check_ajax_referer('eventmail-ajax-nonce');

			// Display a list of upcoming events in a neat list similar to the
			// All Events or All Emails page
			$meta_query = array(
				array(
					'key' => 'start_date',
					'type' => 'DATE',
					'value' => $_POST['query_start_date'],
					'compare' => '>=',
				),
				array(
					'key' => 'end_date',
					'type' => 'DATE',
					'value' => $_POST['query_end_date'],
					'compare' => '<=',
				),
			);
			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'event',
				'meta_query' => $meta_query,
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'start_date',
			);
			$query = new WP_Query($args);

			// This string will be the results we return from the
			// AJAX request.
			$htmlstring = '<table id="queryresultstable"><thead><tr class="eventmail-columns"><td></td><td>Image</td><td>Title</td><td>Start Date</td><td>Start Time</td><td>End Date</td><td>End Time</td></tr></thead><tbody>';

			if($query->have_posts())
			{
				// There are results! Build up an HTML String of them
				while($query->have_posts())
				{
					$query->the_post();
					$eventID = get_the_ID();

					$htmlstring .= '<tr class="eventmail-item"><td class="checkbox"><input type="checkbox" class="eventmail-checkbox" name="events[]" value='. $eventID;
					if(get_post_meta($_POST['post_ID'], 'events', TRUE) && in_array($eventID, unserialize(get_post_meta($_POST['post_ID'], 'events', TRUE))))
					{
						// If the event is already added to the email, then make it checked
						$htmlstring .= ' checked';
					}
					$htmlstring .= '></td><td class="thumbnail">';

					if(has_post_thumbnail())
					{
						$htmlstring .= get_the_post_thumbnail($eventID, array('100px', '100px'));
					}
					else
					{
						$htmlstring .= '<img width="100px" height="100px" alt="No Image Set"></img>';
					}
					$htmlstring .= '</td><td>'
						. get_the_title($eventID)
						. '</td><td>'
						. date("l, M j, Y",strtotime(get_post_meta($eventID, 'start_date', TRUE)))
						. '</td><td>'
						. date("g:i a",strtotime(get_post_meta($eventID, 'start_time', TRUE)))
						. '</td><td>'
						. date("l, M j, Y",strtotime(get_post_meta($eventID, 'end_date', TRUE)))
						. '</td><td>'
						. date("g:i a",strtotime(get_post_meta($eventID, 'end_time', TRUE)))
						. '</td><td><a href="post.php?post='
						. $eventID
						. '&action=edit" target="_blank">Edit</a></td></tr>';
				}
			}
			else
			{
				$htmlstring .= "<tr class='no-results'><td></td><td colspan='8'>Sorry, no Events available to select from. Please expand the time range or modify your search.</td></tr>";
			}
			$htmlstring .= '</tbody></table>';

			// This magically returns the HTML string to the AJAX request
			echo $htmlstring;

			wp_die(); // all AJAX handlers die when finished
		}

		/**
		 * Function that handles AJAX requests from the event theme part
		 */
		public function eventmail_theme_update()
		{
			// Verify the nonce value created for the script
			check_ajax_referer('eventmail-ajax-nonce');

			// Capture all the event data in an associative array of event objects
			$events = Array();
			if($_POST['events'])
			{
				foreach($_POST['events'] as $event_id) {

					// Add the event information to the array
					$events[$event_id] = (object)get_post_meta($event_id);
					foreach($events[$event_id] as $key => $value) {
						$events[$event_id]->$key = $value[0];
					}
					$events[$event_id]->display_date = date('D, M jS', strtotime($events[$event_id]->start_date));
					// Add the event title to the array
					$events[$event_id]->title = get_the_title($event_id);
				}
			}
			$events = array_reverse($events);
			$banner = $_POST['banner'];
			$body = $_POST['body'];

			// $_POST['theme'] contains the path to the file to include
			include($_POST['theme']);

			wp_die(); // all AJAX handlers die when finished
		}
		
		public function generate_email($post_id) {
			// Capture all the event data in an associative array of event objects
			$events = unserialize(get_post_meta($post_id, 'events', true));
			//error_log($events);
			if($events)
			{
				foreach($events as $event_id) {

					// Add the event information to the array
					$events[$event_id] = (object)get_post_meta($event_id);
					foreach($events[$event_id] as $key => $value) {
						$events[$event_id]->$key = $value[0];
					}
					$events[$event_id]->display_date = date('D, M jS', strtotime($events[$event_id]->start_date));
					// Add the event title to the array
					$events[$event_id]->title = get_the_title($event_id);
				}
			}
			$events = array_reverse($events);
			$banner = get_post_meta($post_id, 'banner', true);
			$body = get_post_meta($post_id, 'mailbody', true);
			
			error_log(get_post_meta($post_id, 'mailtheme', true));
			
			ob_start();
			include(get_post_meta($post_id, 'mailtheme', true));
			$result = ob_get_contents();
			ob_end_clean();
			
			// $_POST['theme'] contains the path to the file to include
			return $result;	
		}

		/**
		 * Function that handles AJAX requests from the selected events part
		 */
		public function eventmail_event_update()
		{
			// Verify the nonce value created for the script
			check_ajax_referer('eventmail-ajax-nonce');

			// Display a list of selected events in a neat list similar to the
			// All Events or All Emails page
			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'event',
				'post__in' => $_POST['events']
			);
			$query = new WP_Query($args);

			// This string will be the results we return from the
			// AJAX request.
			$htmlstring;

			if($query->have_posts())
			{
				// There are results! Build up an HTML String of them
				while($query->have_posts())
				{
					$query->the_post();
					$eventID = get_the_ID();

					$htmlstring .= '<tr class="selectedevent-item ui-state-default"><td class="thumbnail">';

					if(has_post_thumbnail())
					{
						$htmlstring .= get_the_post_thumbnail($eventID, array('75px', '75px'));
					}
					else
					{
						$htmlstring .= '<img width="75px" height="75px" alt="No Image Set"></img>';
					}
					$htmlstring .= '</td><td>'
						. get_the_title($eventID)
						. '</td><td>'
						. date("l, M j, Y",strtotime(get_post_meta($eventID, 'start_date', TRUE)))
						. '</td><td>'
						. date("g:i a",strtotime(get_post_meta($eventID, 'start_time', TRUE)))
						. '</td><td>'
						. date("l, M j, Y",strtotime(get_post_meta($eventID, 'end_date', TRUE)))
						. '</td><td>'
						. date("g:i a",strtotime(get_post_meta($eventID, 'end_time', TRUE)))
						. '</td></tr>';
				}
			}
			else
			{
				$htmlstring .= "<tr class='no-results'><td></td><td colspan='8'>Sorry, no Events available to select from. Please expand the time range or modify your search.</td></tr>";
			}

			// This magically returns the HTML string to the AJAX request
			echo $htmlstring;

			wp_die(); // all AJAX handlers die when finished
		}
	}
}
?>
