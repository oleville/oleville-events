

<p>Enter information about the event here</p>

<label for="datetime">When is the event?</label>
<div id="datetime">
    <input type="text" class="date" id="start_date" name="start_date" value="<?php echo get_post_meta($post->ID, 'start_date', TRUE) ?>"/>
    <input type="text" class="time" id="start_time" name="start_time" value="<?php echo get_post_meta($post->ID, 'start_time', TRUE) ?>"/>
    <span>to</span>
    <input type="text" class="time" id="end_time" name="end_time" value="<?php echo get_post_meta($post->ID, 'end_time', TRUE) ?>"/>
    <input type="text" class="date" id="end_date" name="end_date" value="<?php echo get_post_meta($post->ID, 'end_date', TRUE) ?>"/>
</div>

<div id="repeat_container">
    <div id="repeat_checkbox">
        <input type="checkbox" id="repeat" name="repeat" value="repeat" <?php if(get_post_meta($post->ID, 'movie_type', TRUE) == '1') echo 'checked'; ?>/>
        <span>Repeat</span>
    </div>
    <div id="repeat_datetimes">
        <div class="datetime">
            <input type="text" id="sd0" name="sd0" class="date" value=""/>
            <input type="text" id="st0" name="st0" class="time" value=""/>
            <span>to</span>
            <input type="text" id="et0" name="et0" class="time" value=""/>
            <input type="text" id="ed0" name="ed0" class="date" value=""/>
            <input type="button" class="remove" value="X">
        </div>
        <input type="button" id="add_repeat" value="Add" />
    </div>
    <input type="hidden" id="max_repeats" name="max_repeats" value="0" />
</div>

<br/>
<label for="description">Description:</label>
<br />
<textarea rows="3" cols="40" id="description" name="description" placeholder="Enter Description"><?php echo get_post_meta($post->ID, 'description', TRUE) ?></textarea>
<br />
<label for="location">Location:</label>
<br />
<input type="text" id="location" name="location"  placeholder="Location" value="<?php echo get_post_meta($post->ID, 'location', TRUE) ?>"/>
<br />
<br />
<input type="checkbox" id="movie_type" name="movie_type" value='1' <?php if(get_post_meta($post->ID, 'movie_type', TRUE) == '1') echo 'checked'; ?>/>
<label for="movie_type">Is this event a movie?</label>
<br />
<label for="facebook_link">Facebook Link:</label>
<br />
<input type="url" id="facebook_link" name="facebook_link" value="<?php echo get_post_meta($post->ID, 'facebook_link', TRUE) ?>"/>
<br />
<label for="youtube_link">Youtube Link:</label>
<br />
<input type="url" id="youtube_link" name="youtube_link" value="<?php echo get_post_meta($post->ID, 'youtube_link', TRUE) ?>"/>
<br />
