<?php
	function fill_field($post_meta, $option, $default) {
		global $post;
		$result = get_post_meta($post->ID, $post_meta, TRUE);
		if(empty($result)) {
			$result = get_option($option, $default);
		}
		return $result;
	}
?>


<div id="tab-headers">
	<div id="step-1" class="tab-head">
		<h3>1. Select Events</h3>
	</div>
	<div id="step-2" class="tab-head">
		<h3>2. Compose Email</h3>
	</div>
	<div id="step-3" class="tab-head">
		<h3>3. Preview</h3>
	</div>
</div>
<hr />
<div id="select" class="tabpage">
	<p class="instructions">Search for events.</p>
	<div id="querybuilder">
		<div class="query-input inline-block">
			<label for="start-date">Start Date:</label>
			<input type="date" name="start-date" id="start-date" class="queryitem" value="<?php echo date('Y-m-d', strtotime('-1 week'));?>"></input>
			<input type="button" id="start-date-today" value="Today"></button>
		</div>
		<div class="query-input inline-block">
			<label for="end-date">End Date:</label>
			<input type="date" name="end-date" id="end-date" class="queryitem" value="<?php echo date('Y-m-d', strtotime('+1 month'));?>"></input>
		</div>
	</div>
	<p class="instructions">Choose which events to add to the email.</p>
	<div id="queryresults">
	</div>
</div>

<div id="compose" class="tabpage">
	<p class="instructions">Fill in details about the email. Use the Title field to set the name as it will be saved in WordPress (it will not appear in the email), and use "Set Featured Image" to choose a header image for this email.</p>
	<div class="inline-block half-width">
		<label for="mailfrom">From:</label>
		<input type="text" id="mailfrom" name="mailfrom" placeholder="From" value="<?php echo fill_field('mailfrom', 'from_email', 'olethelion@stolaf.edu') ?>"/>
		<div class="inline-block float-right">
			<label for="mailtheme">Theme:</label>
			<select id="mailtheme" name="mailtheme">
				<?php // Add options by parsing the the eventmail-themes folder in the templates folder
					foreach( glob(plugin_dir_path(__FILE__)."eventmail-themes/*.php") as $file) {
						if($file == get_post_meta($post->ID, 'mailtheme', TRUE)) {
							echo '<option value="'.$file.'" selected>'.ucwords(str_replace("_", " ", substr($file, strrpos($file, "/")+1, strrpos($file, ".")-(strrpos($file, "/")+1)))).'</option>';
						}
						else
						{
							echo '<option value="'.$file.'">'.ucwords(str_replace("_", " ", substr($file, strrpos($file, "/")+1, strrpos($file, ".")-(strrpos($file, "/")+1)))).'</option>';
						}
					}
				?>
			</select>
		</div>
		<br /><br />
		<div class="inline-block">
			<label for="mailto">To:</label>
			<input type="text" id="mailto" name="mailto" placeholder="Send To" value="<?php echo fill_field('mailto', 'to_email', 'olethelion@stolaf.edu') ?>"/>
		</div>
		<div class="inline-block">
			<label for="mailcc">cc:</label>
			<input type="text" id="mailcc" name="mailcc" placeholder="Copy To" value="<?php echo get_post_meta($post->ID, 'mailcc', TRUE); ?>"/>
		</div>
		<div class="inline-block">
			<label for="mailbcc">bcc:</label>
			<input type="text" id="mailbcc" name="mailbcc" placeholder="Blind Copy To" value="<?php echo get_post_meta($post->ID, 'mailbcc', TRUE); ?>"/>
		</div>
		<br /><br />
		<label for="mailsubj">Subject:</label>
		<input type="text" id="mailsubj" name="mailsubj" placeholder="Subject" value="<?php echo get_post_meta($post->ID, 'mailsubj', TRUE); ?>"/>
		<br /><br />
		<label for="mailbody">Body:</label><br />
		<textarea rows="5" cols="80" id="mailbody" name="mailbody" placeholder="Body"><?php echo get_post_meta($post->ID, 'mailbody', TRUE); ?></textarea>
	</div>
	<div class="inline-block half-width margin-left">
		<p>Reorder the events in the Newsletter here by dragging and dropping them in the order you desire.</p>
		
		<div id="selectedevents">
			<table id="selectedeventstable">
				<thead>
					<tr class="eventmail-columns">
						<td>Image</td>
						<td>Title</td>
						<td>Start Date</td>
						<td>Start Time</td>
						<td>End Date</td>
						<td>End Time</td>
					</tr>
				</thead>
				<tbody id="selectedeventsbody">
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="preview" class="tabpage">
	<p class="instructions">Here is a preview of what your email will look like.</p>
	<iframe id="preview-wrapper" src="about:blank">
	</iframe>
</div>
<hr />
<div id="tab-footer">
	<input type="button" id="prev-tab" value="Previous">
	<input type="button" id="next-tab" value="Next">
</div>