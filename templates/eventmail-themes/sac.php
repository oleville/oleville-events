<?php
/*
	sac.php - the default theme for emails.
*/
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body style="font-family: Tahoma, Geneva, sans-serif; font-size: 14px;">
<table width="600" border="0" cellspacing="0" cellpadding="0" style="margin:auto;">
      <tr>
      <td colspan="2"><a href="http://www.oleville.com/sac/"><img src="<?php echo $banner ?  $banner : 'http://oleville.com/sac/wp-content/uploads/sites/4/2014/11/SAC-EA.jpg'; ?>" width="600" height="245" alt="Event Announcement" /></a></td>
    </tr>
    <tr>
      <td colspan="2" style="border-top:solid 5px white;"><a href="http://www.oleville.com/sac/"><img src="http://oleville.com/sac/wp-content/uploads/sites/4/2014/11/Event-Announcement-Text.jpg" width="600" height="50" alt="Event Announcement" /></a></td>
    </tr>
    <?php
			$i = 0;
			$size = count($events);
		 foreach($events as $id => $event) { ?>
  <tr width="600">
    <td width="457" style="padding: 10px; font-family: Tahoma, Geneva, sans-serif;"><div style="margin: 0px; padding: 0px; border-bottom-color: #000; border-bottom-style: solid; border-bottom-width: 2px; font-family: Tahoma, Geneva, sans-serif; font-size: 16px; color: #FDB52E;">
    <p style="padding: 0px; margin: 0px; font-family: Tahoma, Geneva, sans-serif; font-size:24px; color: #F90;"><?php if($event->facebook) { ?><a href="https://www.facebook.com/events/658674240835768/" style="border:0 none;"><img src="http://oleville.com/wp-content/uploads/2014/11/facebook.png" alt="Facebook" width="20" height="20" id="Facebook2" style="margin: 20px 0 0; float: right;" /></a><?php } ?><?php echo $event->title; ?></p>
    <p style="padding: 0 0 10px 10px; margin: 0px; font-family: Tahoma, Geneva, sans-serif; font-size: 14px; color: #333;"><?php echo $event->display_date; ?>, 4:15pm - <?php echo $event->location; ?></p>
  </div>
    <div> <p><?php echo $event->description; ?></p>
    </div></td>
    <?php if($i == 0) { ?>
    <td width="143" rowspan="<?php echo $size; ?>" style="vertical-align: text-top; padding: 5px;"><div style="width: 60px; margin: auto; margin-top: 10px; text-align: center;"><a href="https://www.facebook.com/stolaf.sga.sac" style="border:0 none;"><img src="http://oleville.com/wp-content/uploads/2014/11/facebook.png" alt="Facebook" width="32" height="32" id="Facebook" style="margin: 20px 0 0;" /></a><a href="https://twitter.com/StOlafSAC" style="border: none 0;"><img src="http://oleville.com/wp-content/uploads/2014/11/twitter.png" alt="Twitter" width="32" height="32" id="Twitter" style="margin: 20px 0 0;" /></a><a href="http://www.oleville.com/new/about/sac/" style="border: none 0;"><img src="http://oleville.com/wp-content/uploads/2014/11/oleville.png" alt="Oleville" width="32" height="32" id="Oleville" style="margin: 20px 0 0;" /></a></div></td>
    <?php } ?>
  </tr>
          <?php $i++;
					} ?>        
        <tr>
   
    <td style="padding: 10px; font-size: 10px; font-family: Tahoma, Geneva, sans-serif; text-align: center; color: #666;">Newsletter for the week of April 25th, 2014. <br />
       Email provided by the Student Activities Committee. <br />
    You may unsubscribe at any time under St. Olaf Account Services</td>
    <td style="vertical-align: text-top; padding: 5px;"><img src="http://i40.tinypic.com/t7z691.jpg" width="140" height="40" alt="SAC &amp; SGA" style="margin-top:4px;" /></td>
  </tr>
</table>
</body>
</html>