// This code helps set up the email-creation page
jQuery(document).ready(function($) {

	// Track the events that are currently added to the event
	var eventIDs = JSON.parse(ajaxRequest.events) || [];

	// Function to add an event to the event array if it isn't already in there
	function addEvent(eventID) {
		if($.inArray(eventID, eventIDs) < 0) {
			eventIDs.push(eventID);
		}
	}
	// Function to remove an event from the event array if it is there
	function removeEvent(eventID) {
		var index = eventIDs.indexOf(eventID);
		if(index > -1) {
			eventIDs.splice(index, 1);
		}
	}

	// This code handles the tab functionality
	var activeTab = 0;
	function changeTabTo(newTab) {
		// Remove the active-tab class from the previous active tab
		$(".active-tab").removeClass("active-tab");
		// Add the active-tab class to the new active tab
		$(".tabpage").each(function(index) {
			if(index == newTab) {
				$(this).addClass("active-tab");
			}
		});
		// Remove the active class from the previous active tab-head
		$(".active").removeClass("active");
		// Add the active class to the new active tab-head
		$(".tab-head").each(function(index) {
			if(index == newTab) {
				$(this).addClass("active");
			}
		});
	}

	// Call changeTabTo to set the inital tab
	changeTabTo(activeTab);

	// Attach a click event handler to the buttons
	$("#tab-footer input").click(function(event) {
		if($(event.target).is("#prev-tab")) {
			// Previous button was clicked
			if(activeTab > 0) {
				changeTabTo(activeTab-1);
				activeTab -= 1;
			}
		} else {
			// Next button was clicked
			if(activeTab < 2) {
				changeTabTo(activeTab+1);
				activeTab += 1;
			}
		}
	});

	// Attach a click event handler to the tab-heads
	$(".tab-head h3").click(function(event) {
		$(".tab-head").each(function(index) {
			if($(this).children().is(event.target)) {
				changeTabTo(index);
				activeTab = index;
			}
		});
	});

	// This code allows the user to easily change the start date of the query to today
	$("#start-date-today").click(function() {
		var now = new Date();
		var datestring = now.getFullYear() + '-' + (now.getMonth()+1) + '-' + now.getDate();
		$("#start-date").val(datestring);
		$("#start-date").trigger("change");
	});

	// This code handles reissuing queries when a query parameter changes
	$(".queryitem").change(function() {
		// A query parameter was changed... send an AJAX request

		// Need to make the image not hard-coded in the plugin page
		$("#queryresults").empty().append("<img class='loading-animation' src='http://oleville.com/testing/wp-content/uploads/sites/13/2014/10/image_744363.gif'></img>");

		// This function can recognize the ajaxRequest object
		// which was localized in email-type.php
		$.post(
			ajaxRequest.ajax_url,
			{
				_ajax_nonce: ajaxRequest.nonce,
				action: "eventmail_query_update",
				// If needed, add other data here
				query_start_date: $("#start-date").val(),
				query_end_date: $("#end-date").val(),
				post_ID: $("#post_ID").val()
			},
			function(data) {
				// We need to update the #queryresults div
				// with the new data
				console.log(data);
				$("#queryresults").empty().append(data);

				// Reapply the click events
				$(".eventmail-item").click(function(event) {
					// This prevents accidental selection when the user's intent was to edit the event
					if(event.target.nodeName == 'A' || event.target.nodeName == 'INPUT')
						return;
					$(event.target).closest("tr").find("input").prop("checked", function(i, val) { return !val; });
					$(event.target).closest("tr").find("input").trigger("change");
				});

				// Apply change events to the checkboxes
				$(".eventmail-checkbox").change(function(event) {
					if($(event.target).is(":checked"))
					{
						addEvent($(event.target).val());
					}
					else
					{
						removeEvent($(event.target).val());
					}
				});
			}
		);
	});
	// Trigger the events once so that the AJAX query is run immediately
	$(".queryitem").trigger("change");

	// This code handles updating the preview when the theme is changed
	function updatePreview() {
			// Update the order of the eventIDs array based on the order from the compose tab
			// TODO

			$("#preview-wrapper").contents().find("body").empty();
			$("#preview-wrapper").contents().find("body").append("<img class='loading-animation' src='http://oleville.com/testing/wp-content/uploads/sites/13/2014/10/image_744363.gif'></img>");

			// Get the featured image url - WP calls the div containing the image
			// #postimagediv
			var bannerSrc = $("#postimagediv img").attr("src");

			// Get the body of the email
			var mailbody = $("#mailbody").val();

			// The theme was changed... send an AJAX request
			$.post(
				ajaxRequest.ajax_url,
				{
					_ajax_nonce: ajaxRequest.nonce,
					action: "eventmail_theme_update",
					// If needed, add other data here
					theme: $("#mailtheme").val(),
					events: eventIDs.map(Number), // This is the array that we use to keep track of ids
					banner: bannerSrc,
					body: mailbody
				},
				function(data) {
					// We need to update the #preview-wrapper iframe
					$("#preview-wrapper").contents().find("body").empty();
					$("#preview-wrapper").contents().find("body").append(data);

					// This line makes the iFrame adjust the height
					$("#preview-wrapper").height($("#preview-wrapper").contents().height());
				}
			);
	}

	// Register a mutation observer (whoa) to listen for the active preview tab
	//   What this does is it will submit the AJAX request only when the
	//   Preview tab becomes active, rather than handling a change to the
	//   Theme field.
	if($("#preview").length) {
		var eventmail_observer = new MutationObserver( function(mutations, observer) {
			mutations.forEach( function(mutation) {
				if(mutation.type == "attributes" && mutation.target == $("#preview").get(0) && mutation.attributeName == "class") {
					if($.inArray("active-tab", $("#preview").get(0).classList) > -1) {
						updatePreview();
					}
				}
			});
		});
		eventmail_observer.observe($("#preview").get(0), {
			attributes: true
		});
	}

	function updateCompose() {
		// Need to make the image not hard-coded in the plugin page
		$("#selectedeventsbody").empty().append("<tr><img class='loading-animation' src='http://oleville.com/testing/wp-content/uploads/sites/13/2014/10/image_744363.gif'></img></tr>");

		// This function can recognize the ajaxRequest object
		// which was localized in email-type.php
		$.post(
			ajaxRequest.ajax_url,
			{
				_ajax_nonce: ajaxRequest.nonce,
				action: "eventmail_event_update",
				// If needed, add other data here
				events: eventIDs,
				post_ID: $("#post_ID").val()
			},
			function(data) {
				// We need to update the #selectedeventsbody div
				// with the new data
				$("#selectedeventsbody").empty().append(data);

				// Make them reorderable using jQueryUI which is loaded for us
				$("#selectedeventsbody").sortable({
					placeholder: "ui-state-highlight",
					forcePlaceholderSize: true,
					axis: "y",
				});
			}
		);
	}

	// Register a mutation observer (whoa) to listen for the active compose tab
	//   What this does is it will submit the AJAX request only when the
	//   Compose tab becomes active.
	if($("#compose").length) {
		var eventmail_observer = new MutationObserver( function(mutations, observer) {
			mutations.forEach( function(mutation) {
				if(mutation.type == "attributes" && mutation.target == $("#compose").get(0) && mutation.attributeName == "class") {
					if($.inArray("active-tab", $("#compose").get(0).classList) > -1) {
						updateCompose();
					}
				}
			});
		});
		eventmail_observer.observe($("#compose").get(0), {
			attributes: true
		});
	}
});
