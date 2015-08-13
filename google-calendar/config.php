<?php
	// Include the Google Calendar API services
	require_once dirname(__FILE__).'/google-php-client/src/Google_Client.php';
	require_once dirname(__FILE__).'/google-php-client/src/contrib/Google_CalendarService.php';

	/**
	 * Define some values for authenticating the user. These values were
	 * obtained at console.developers.google.com/project as of 10/2014.
	 */

	// CLIENT_ID is the CLIENT ID field of the Service Account created
	// on the Google Developers Console
	define('CLIENT_ID','');

	// SERVICE_ACCOUNT_NAME is the EMAIL ADDRESS field of the Service
	// Account created on the Google Developers Console
	define('SERVICE_ACCOUNT_NAME','');

	// KEY_FILE is the path to the P12 key that was saved in this directory.
	// The P12 file was generated under the same Service Account created
	// on the Google Developers Console
	define('KEY_FILE','');

	// CALENDAR_ID is the id of the calendar that the events are sent to.
	// Note that the SERVICE_ACCOUNT_NAME field (the email address) must be
	// able to make changes to this calendar.
	define('CALENDAR_ID','');

?>
