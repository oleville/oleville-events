<?php
if(!class_exists('Oleville_Events_Misc'))
{
	class Oleville_Events_Misc
	{

		/**
		 * Constructor
		 */
		public function __construct()
		{
			//add_filter( 'cron_schedules', array(&$this, 'cron_add_three_minutes') );


			//add_action( 'happening_now_cache_hook', array(&$this, 'update_hap_cache') );
		}

		public function cron_add_three_minutes( $schedules ) {
			// Adds once weekly to the existing schedules.
			$schedules['three_min'] = array(
				'interval' => 180,
				'display' => __( 'Three Minutes' )
			);
			return $schedules;
		 }

		public function update_hap_cache()
		{
			error_log('Cache Fired');
			/**
			switch_to_blog(1);
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
					'compare' => 'EXISTS'
				),
				array(
					'key' => 'start_time',
					'type' => 'CHAR',
					'compare' => 'EXISTS'
				)
			);
			$args = array(
				'post_type' => 'event',
				'meta_query' => $meta_query,
				'posts_per_page' => 2,
			);

			add_filter('posts_orderby', 'oleville_most_recent_post');
			$query = new WP_Query($args);
			remove_filter('posts_orderby', 'oleville_most_recent_post');

			$now = new DateTime();

			$count = 0;

			$result = '<div class="happening" style="float:left;">';
			while ($query -> have_posts()): $query -> the_post(); global $post;
				if($count >= 1)
					continue;
				$result .= 'Happening ';

				$meta_date = get_post_meta(get_the_ID(), 'start_date', true);
				$meta_time = get_post_meta(get_the_ID(), 'start_time', true);
				$meta_date_end = get_post_meta(get_the_ID(), 'end_date', true);
				$meta_time_end = get_post_meta(get_the_ID(), 'end_time', true);

				$event_time = new DateTime($meta_date . 'T' . $meta_time . ':00.00');
				$event_time_end = new DateTime($meta_date_end . 'T' . $meta_time_end . ':00.00');

				$diff = $event_time->diff($now);
				$diff_end = $event_time_end->diff($now);
				if($now > $event_time_end) {
								continue;
				}
				if ($event_time->getTimestamp() < $now->getTimestamp())
				{
					$result .= 'Now: ';
				}
				else if($diff->d ==0) {
						$result .= 'in ';
						switch ($diff->h)
						{
							case 0:
								$result .= 'Now: ';
								break;
							case 1:
								$result .= '1 hour: ';
								break;
							default:
								$result .= ($diff->h + 24*$diff->d) . ' hours: ';
								break;
						}
				} else if ($diff->d < 7) {
					switch ($diff->d)
						{
							case 0:
								$result .= 'Today at ' . $event_time->format('g:i a') . ': ';
								break;
							case 1:
								$result .= 'Tomorrow at ' . $event_time->format('g:i a') . ': ';
								break;
							default:
								$result .= $event_time -> format('l \a\t g:i a') . ': ';
								break;
						}
				}
				else {
						$result .= $event_time->format('M\. j \a\t g:i a') . ': ';
				}


				$result .= get_the_title() . ' - ' . get_post_meta(get_the_ID(), 'location', true);
		$count++;
			endwhile;
			$result .= '</div>';
			wp_reset_postdata();
			restore_current_blog();

			// Reset the timezone
			date_default_timezone_set($timezone);

			if(add_site_option( 'happening_now_cache', $result )) {

			} else {
				update_site_option('happening_now_cache', $result);
			}

*/
		}

		/**
		 * Function hooked to WP's init action
		 */
		public function init()
		{

		}

		/**
		 * Function hooked to WP's admin_init action
		 */
		public function admin_init()
		{

		}
	}
}
