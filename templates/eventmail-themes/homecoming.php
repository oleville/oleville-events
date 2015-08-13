<?php
/*
	homecoming.php - the homecoming theme for emails.
*/
?>
<h2>Homecoming Theme</h2>
<p>events = <?php echo json_encode($_POST['events']) ?></p>
<p>banner = <?php echo $_POST['banner'] ?></p>
<p>body = <?php echo $_POST['body'] ?></p>