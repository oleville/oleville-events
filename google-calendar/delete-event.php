<?php
	// Include the config file
	require_once(sprintf("%s/config.php", dirname(__FILE__)));
	
	// Start a session to begin authenticating the user to use the Google Calendar API
	session_start();
	
	// Set up a client and get the client authenticated
	$client = new Google_Client();
	
	$client->setApplicationName("Oleville-Events");
	$client->setUseObjects(true);
	
	if (isset($_SESSION['token'])) 
	{
		$client->setAccessToken($_SESSION['token']);
	}
	
	$key = file_get_contents(KEY_FILE);
	$client->setClientId(CLIENT_ID);
	
	$client->setAssertionCredentials(
		new Google_AssertionCredentials(
			SERVICE_ACCOUNT_NAME,
			array('https://www.googleapis.com/auth/calendar'),
			$key
		)
	);
	
	$client->setClientId(CLIENT_ID);
	
	// Create the Calendar Service
	$service = new Google_CalendarService($client);
	
	// Query the calendar for an event with the id stored in the current post
	$event_id = get_post_meta( $post_id, 'calendar-event-id', true );
	if( $event_id != "" )
		$google_event = $service->events->get(CALENDAR_ID, $event_id);
		
	if($google_event != null)
	{
		try 
		{
			$service->events->delete(CALENDAR_ID, $event_id);
		} 
		catch (Google_ServiceException $e) 
		{
			write_log("Exception raised:");
			write_log( $e->getMessage());
		}
	}
	
	// End the session
	session_destroy();
?>