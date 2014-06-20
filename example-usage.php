/* Example of how to use uki_facebook_wall_feed code in your website
   The strings YOUR_FACEBOOK_ID, YOUR_APP_ID, and YOUR_APP_SECRET
   should be changed to the values that pertain to your facebook account
   and app.
*/
<link href="uki_facebook_wall_feed.css" rel="stylesheet" type="text/css" />

<?php
  include("uki_facebook_wall_feed.php");
  $feed = new uki_facebook_wall_feed('YOUR_FACEBOOK_ID', 'YOUR_APP_ID', 'YOUR_APP_SECRET');
  $feed->get_fb_wall_feed();
  $feed->display_fb_wall_feed();
?>
