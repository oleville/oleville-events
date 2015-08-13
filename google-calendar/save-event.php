<?php
	// Include the connection file
	require_once(sprintf("%s/config.php", dirname(__FILE__)));

	// Start a session to begin authenticating the user to use the Google Calendar API
	session_start();

	// Set up a client and get the client authenticated
	$client = new Google_Client();

	$client->setApplicationName("Oleville-Events");
	$client->setUseObjects(true);
	$client->setClientId(CLIENT_ID);

	if (isset($_SESSION['token']))
	{
		$client->setAccessToken($_SESSION['token']);
	}

	$key = file_get_contents(KEY_FILE);

	$cred = new Google_AssertionCredentials(
		SERVICE_ACCOUNT_NAME,
		array('https://www.googleapis.com/auth/calendar'),
		$key
	);

	//$cred->sub = "nnooney2012@gmail.com";

	$client->setAssertionCredentials($cred);

	// Create the Calendar Service
	$service = new Google_CalendarService($client);

	// Convert the Dates and Times into RTF Format
	//$event_start = $_POST['start_date'] . "T" . $_POST['start_time'] . "";
	//$event_end = $_POST['end_date'] . "T" . $_POST['end_time'] . "";
	
	$event_start = date('Y-m-d\TH:i:s',strtotime($_POST['start_date'] . " " . $_POST['start_time']));
	$event_end = date('Y-m-d\TH:i:s',strtotime($_POST['end_date'] . " " . $_POST['end_time']));
	

	// Query the calendar for an event with the id stored in the current post
	$event_id = get_post_meta( $post_id, 'calendar-event-id', true );
	if( $event_id != "" )
		$google_event = $service->events->get(CALENDAR_ID, $event_id);

	if($google_event != null)
	{
		// Update the information in the Calendar Event with the information in the Wordpress Post
		$google_event->setSummary($_POST['post_title']);
		$google_event->setDescription($_POST['description']);
		$google_event->setLocation($_POST['location']);
		$google_event_start = new Google_EventDateTime();
		$google_event_start->SetDateTime($event_start);
		$google_event_start->SetTimeZone('America/Chicago');
		$google_event->setStart($google_event_start);
		$google_event_end = new Google_EventDateTime();
		$google_event_end->SetDateTime($event_end);
		$google_event_end->SetTimeZone('America/Chicago');
		$google_event->setEnd($google_event_end);
		$google_event->setAnyoneCanAddSelf(true);

		try
		{
			$updated_event = $service->events->update(CALENDAR_ID, $google_event->getID(), $google_event);
			$event_id = $updated_event->getId();
		}
		catch (Google_ServiceException $e)
		{
			write_log("Exception raised:");
			write_log( $e->getMessage());
		}

	}
	else
	{
		// Create the event in the calendar since it doesn't already exist
		$event = new Google_Event();

		// Give the new event the details from the post
		$event->setSummary($_POST['post_title']);
		$event->setDescription($_POST['description']);
		$event->setLocation($_POST['location']);
		$google_event_start = new Google_EventDateTime();
		$google_event_start->SetDateTime($event_start);
		$google_event_start->SetTimeZone('America/Chicago');
		$event->setStart($google_event_start);
		$google_event_end = new Google_EventDateTime();
		$google_event_end->SetDateTime($event_end);
		$google_event_end->SetTimeZone('America/Chicago');
		$event->setEnd($google_event_end);
		$event->setAnyoneCanAddSelf(true);

		try
		{
			$new_event = $service->events->insert(CALENDAR_ID, $event);
			$event_id = $new_event->getId();
		}
		catch (Google_ServiceException $e)
		{
			write_log("Exception raised:");
			write_log( $e->getMessage());
		}
	}

	// Also update the eventID of the SGA GoogleCalendar with this post
	update_post_meta( $post_id, 'calendar-event-id', $event_id);

	// End the session
	session_destroy();
?>
