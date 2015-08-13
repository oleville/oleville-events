<?php
if(!class_exists('Oleville_Events_Shortcode'))
{
	class Oleville_Events_Shortcode
	{
		const POST_TYPE = "event";
		const SHORTCODE = "events";
		// This list matches the field values in templates/event_metabox.php
		private $_meta = array(
			'description',
			'location',
			'start_date',
			'start_time',
			'end_date',
			'end_time',
			'facebook_link'
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
			// Add the shortcode hook
			if (!shortcode_exists(self::SHORTCODE)) {
				add_shortcode(self::SHORTCODE, array(&$this, 'add_shortcode'));
			}
		}

		/**
		 * Function to enable short codes for embedding events on a page
		 */
		public function add_shortcode($atts)
		{
			// Use only the attributes listed; provide defaults
			// if they aren't given as args
			$atts = shortcode_atts(
				array(
					'view' => 'calendar',
				),
				$atts
			);

			$view = sanitize_text_field( $atts['view'] );

			// This large if - if else - else statement defines the different ways the
			// shorcode can display the events
			if ($view == 'calendar')
			{
				// The number of posts to display in the calendar view
				$num_posts = 4;

				// So as not to break query
				$timezone = date_default_timezone_get();
				date_default_timezone_set('America/Chicago');

				//$displayed_posts = 0;
				//$paged = 0;

				// Create a query and add to posts until we get the next 4 events
				$result = '<div class="calendar">';
				//while ($displayed_posts < $num_posts):

				// Set up the initial query for the post
				$meta_query = array(
					array(
						'key' => 'end_date',
						'type' => 'CHAR',
						'value' => date('Y-m-d'),
						'compare' => '>=',
					),
					array(
						'key' => 'start_date',
						'type' => 'CHAR',
						'value' => date('Y-m-d'),
						'compare' => '>=',
					),
					array(
						'key' => 'start_time',
						'type' => 'CHAR',
						'compare' => 'EXISTS'
					)
					// ),
					// array(
					// 	'key' => 'end_time',
					// 	'type' => 'CHAR',
					// 	'value' => date("H:it"),
					// 	'compare' => '<=',
					// )
				);
				$args = array(
					'post_type' => self::POST_TYPE,
					// 'order' => 'ASC',
					'meta_query' => $meta_query,
					'posts_per_page' => $num_posts,
					//'paged' => $paged,
				);

				add_filter('posts_orderby', array(&$this, 'custom_posts_orderby'));
				$query = new WP_Query($args);
				remove_filter('posts_orderby', array(&$this, 'custom_posts_orderby'));

				// Get the current date
				$today = new DateTime(date('Y-m-d'));
				$now = new DateTime();

				while ($query -> have_posts()): $query -> the_post(); global $post;
					$dateword = $title = $location = $time = $date = $host = '';

					// Calculate the day string (Today, Tomorrow, Day of Week...)
					$event_start = new DateTime(get_post_meta(get_the_ID(), 'start_date', true));
					$datediff = $event_start->diff($today)->d;
					$daystring = '';
					if($datediff < 7) {
						switch ($datediff)
						{
							case 0:
								$daystring = 'Today';
								break;
							case 1:
								$daystring = 'Tomorrow';
								break;
							default:
								$daystring = $event_start -> format('l');
								break;
						}
					} else {
						$daystring = $event_start->format('M\. j');
					}

					$dateword = '<div class="dateword">' . $daystring . '</div>';
					$title = '<div class="title">' . get_the_title() . '</div>';
					$location = '<div class="location">' . get_post_meta(get_the_ID(), 'location', true) . '</div>';

					// Calculate the time string
					$event_time = new DateTime(get_post_meta(get_the_ID(), 'start_time', true));

					// Make sure the event hasn't already ended
					$event_end = new DateTime(get_post_meta(get_the_ID(), 'end_time', true));

					if($event_end->getTimestamp() < $now->getTimestamp()) {
						continue;
					}

					$timestring = ($event_time->getTimestamp() < $now->getTimestamp() && $datediff == 0) ? 'Now' : $event_time->format('g:i a');

					$time = '<div class="time">' . $timestring . '</div>';
					$host = '<div class="host">Host: ' . get_post_meta(get_the_ID(), 'branch', true) . '</div>';
					$date = '<div class="date">' . $event_start->format('F j') . '</div>';


					$output = '<div class="event">' . $dateword . $title . $location . $time . $host . $date . '</div>';

					$result .= $output;

					// Increment the number of displayed posts
					//$displayed_posts = $displayed_posts + 1;
				endwhile;

				//$paged = $paged + 1; // Search the next page
				//endwhile; // The outer loop to make sure we have 4 posts
				$clear_style = '<div style="clear:both;" />'; // A nice little thing to help with the formatting
				$result .= $clear_style . '</div>';

				wp_reset_postdata();
				date_default_timezone_set($timezone);
				return $result;

			}
			else if ($view == 'timeline')
			{
				return "<div>:p</div>";
			} else if ($view == 'movies') {
				// The number of posts to display in the calendar view
				$num_posts = 4;

				// So as not to break query
				$timezone = date_default_timezone_get();
				date_default_timezone_set('America/Chicago');

				// Set up the initial query for the post
				$meta_query = array(
					array(
						'key' => 'end_date',
						'type' => 'CHAR',
						'value' => date('Y-m-d'),
						'compare' => '>=',
					),
					array(
						'key' => 'start_date',
						'type' => 'CHAR',
						'value' => date('Y-m-d'),
						'compare' => '<='
					),
					array(
						'key' => 'start_time',
						'type' => 'CHAR',
						'compare' => 'EXISTS'
					),
					array(
						'key' => 'movie_type',
						'type' => 'CHAR',
						'value' => '0',
						'compare' => '!='
					)
				);
				$args = array(
					'post_type' => self::POST_TYPE,
					// 'order' => 'ASC',
					'meta_query' => $meta_query,
					'posts_per_page' => 1,
				);

				add_filter('posts_orderby', array(&$this, 'custom_posts_orderby'));
				$query = new WP_Query($args);
				remove_filter('posts_orderby', array(&$this, 'custom_posts_orderby'));

				// Get the current date
				$today = new DateTime(date('Y-m-d'));
				$now = new DateTime();

				$result = '<div class="movies">';
				while ($query -> have_posts()): $query -> the_post(); global $post;
					$dateword = $title = $location = $time = $date = $host = '';

					// Calculate the day string (Today, Tomorrow, Day of Week...)
					$event_start = new DateTime(get_post_meta(get_the_ID(), 'start_date', true));
					$datediff = $event_start->diff($today)->d;
					$daystring = '';
					if($datediff < 7) {
						switch ($datediff)
						{
							case 0:
								$daystring = 'Today';
								break;
							case 1:
								$daystring = 'Tomorrow';
								break;
							default:
								$daystring = $event_start -> format('l');
								break;
						}
					} else {
						$daystring = $event_start->format('M\. j');
					}

					$dateword = '<div class="dateword">' . $daystring . '</div>';
					$title = '<h2 class="title">' . get_the_title() . '</h2>';
					$location = '<div class="location">' . get_post_meta(get_the_ID(), 'location', true) . '</div>';

					// Calculate the time string
					$event_time = new DateTime(get_post_meta(get_the_ID(), 'start_time', true));

					// Make sure the event hasn't already ended
					$event_end = new DateTime(get_post_meta(get_the_ID(), 'end_time', true));

					if($event_end->getTimestamp() < $now->getTimestamp()) {
						continue;
					}

					$timestring = ($event_time->getTimestamp() < $now->getTimestamp() && $datediff == 0) ? 'Now' : $event_time->format('g:i a');

					$time = '<div class="time">5:00pm, 7:30pm, 10:00pm</div>';
					$description = '<div class="description">' . get_post_meta(get_the_ID(), 'description', true) . '</div>';
					$date = '<div class="date">' . $event_start->format('F j') . '</div>';

					$url = get_post_meta(get_the_ID(), 'youtube_link', TRUE);
					preg_match(
					        '/[\\?\\&]v=([^\\?\\&]+)/',
					        $url,
					        $matches
					    );
					$id = $matches[1];

					$width = '800';
					$height = '450';
					$yt .= '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="http://www.youtube.com/v/' . $id . '&amp;hl=en_US&amp;fs=1?rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' . $id . '&amp;hl=en_US&amp;fs=1?rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $width . '" height="' . $height . '"></embed></object>';



					$output = $yt . $title . $description . $date . $time . $location;


					$result .= $output;
				endwhile;
				$clear_style = '<div style="clear:both;" />'; // A nice little thing to help with the formatting
				$result .= $clear_style . '</div>';

				wp_reset_postdata();
				date_default_timezone_set($timezone);
				return $result;
			}
			else
			{
				return "<p>Error: Events Shortcode 'view' argument is invalid. It must be 'calendar' or 'timeline'</p>";
			}


		}

		/**
		 * Helper function to use custom ordering of the posts in WP Query
		 */
		public function custom_posts_orderby($orderby) {
			global $wpdb;
			return "mt1.meta_value ASC, mt2.meta_value ASC";
		}

		/**
		 * Function hooked to WP's admin_init action
		 */
		public function admin_init()
		{

		}
	}
}
