<?php
  class uki_facebook_wall_feed
  {
    private $fbID;
    private $fbWallFeed;
    private $appSecret;
    private $appID;

    function __construct($id, $appID, $appSecret)
    {
      $this->fbID = $id;
      $this->appID = $appID;
      $this->appSecret = $appSecret;
      //echo "Initializing (" . $this->fbID . ")...<br />";
    }
    function get_fb_wall_feed()
    {
      //echo "Contacting FaceBook...<br />";
      $id = $this->fbID;
      $secret = $this->appSecret;
      $clientID = $this->appID;

      // Make call to get authentication token
      $cht = curl_init();
      curl_setopt($cht, CURLOPT_URL, "https://graph.facebook.com/oauth/access_token?grant_type=client_credentials&client_id=$clientID&client_secret=$secret");
      curl_setopt($cht, CURLOPT_RETURNTRANSFER, 1);
      $token = curl_exec($cht);
      $tFile = get_template_directory() . "/fb_access_token.txt";
      $tokenFile = fopen("$tFile", "r");
      $token = fread($tokenFile, filesize($tFile));
      $token = "access_token=" . $token;
      fclose($tokenFile); 
      curl_close($cht);

      // Now make call to get the wall feed
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/$id/feed?limit=100&$token");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $this->fbWallFeed = json_decode(curl_exec($ch), true);
      //print_r($this->fbWallFeed);
      curl_close($ch);
    }
    function display_fb_wall_feed()
    {
      $fbFeed = $this->fbWallFeed["data"];
      echo "
        <div id=\"facebook_status_box\">
          <h2>Facebook Status</h2>
          <div id=\"facebook_canvas\">";

      for ($i = 0; $i < count($fbFeed); $i++)
      {
        $printArray = array();
        if ($fbFeed[$i]["type"] == "status")
        {
          $fbMsg = $fbFeed[$i]["message"];
          $fbID = $fbFeed[$i]["from"]["id"];
          $fbName = $fbFeed[$i]["from"]["name"];
          $fbPhoto = "http://graph.facebook.com/$fbID/picture";
          $fbTime = $fbFeed[$i]["created_time"];
          $fbStoryID = $fbFeed[$i]["id"];

          $printArray["fbMsg"] = $fbFeed[$i]["message"];
          $printArray["fbID"] = $fbFeed[$i]["from"]["id"];
          $printArray["fbName"] = $fbFeed[$i]["from"]["name"];
          $printArray["fbPhoto"] = "http://graph.facebook.com/$fbID/picture";
          $printArray["fbTime"] = $this->parse_fb_timestamp($fbFeed[$i]["created_time"]);
          $printArray["fbStoryID"] = $fbFeed[$i]["id"];
          $printArray["fbIcon"] = $fbFeed[$i]["icon"];
          $printArray["postType"] = "status";

          //$this->print_fb_post($fbStoryID, $fbPhoto, $fbID, $fbName, $fbMsg, $this->parse_fb_timestamp($fbTime));
          $this->print_fb_post($printArray);
        }
        else if ($fbFeed[$i]["type"] == "link" || $fbFeed[$i]["type"] == "video")
        {
          $fbMsg = $fbFeed[$i]["message"];
          $fbID = $fbFeed[$i]["from"]["id"];
          $fbName = $fbFeed[$i]["from"]["name"];
          $fbPhoto = "http://graph.facebook.com/$fbID/picture";
          $fbTime = $fbFeed[$i]["created_time"];
          $fbStoryID = $fbFeed[$i]["id"];

          $printArray["fbMsg"] = $fbFeed[$i]["message"];
          $printArray["fbID"] = $fbFeed[$i]["from"]["id"];
          $printArray["fbName"] = $fbFeed[$i]["from"]["name"];
          $printArray["fbPhoto"] = "http://graph.facebook.com/" . $printArray["fbID"] . "/picture";
          $printArray["fbTime"] = $this->parse_fb_timestamp($fbFeed[$i]["created_time"]);
          $printArray["fbStoryID"] = $fbFeed[$i]["id"];
          $printArray["postType"] = ($fbFeed[$i]["type"] == "link") ? "link" : "video";
          $printArray["fbIcon"] = $fbFeed[$i]["icon"];

          $printArray["picture"] = $fbFeed[$i]["picture"];
          $printArray["link"] = $fbFeed[$i]["link"];
          $printArray["linkName"] = $fbFeed[$i]["name"];
          $printArray["linkCaption"] = $fbFeed[$i]["caption"];
          $printArray["linkDescription"] = $fbFeed[$i]["description"];

          $this->print_fb_post($printArray);
        }
        else if ($fbFeed[$i]["type"] == "photo")
        {
          $printArray["fbMsg"] = $fbFeed[$i]["message"];
          $printArray["fbID"] = $fbFeed[$i]["from"]["id"];
          $printArray["fbName"] = $fbFeed[$i]["from"]["name"];
          $printArray["fbPhoto"] = "http://graph.facebook.com/" . $printArray["fbID"] . "/picture";
          $printArray["fbTime"] = $this->parse_fb_timestamp($fbFeed[$i]["created_time"]);
          $printArray["fbStoryID"] = $fbFeed[$i]["id"];
          $printArray["postType"] = "photo";
          $printArray["fbIcon"] = $fbFeed[$i]["icon"];

          $printArray["picture"] = $fbFeed[$i]["picture"];
          $printArray["link"] = $fbFeed[$i]["link"];
          $printArray["linkName"] = $fbFeed[$i]["name"];
          $this->print_fb_post($printArray);
        }
      }
      echo "</div>
          </div>";
    }
    //function print_fb_post($fbStoryID, $fbPhoto, $fbID, $fbName, $fbMsg, $postTime)
    function print_fb_post($fbInfo)
    {
      $fbMsg = $fbInfo["fbMsg"];
      $fbID = $fbInfo["fbID"];
      $fbName = $fbInfo["fbName"];
      $fbPhoto = $fbInfo["fbPhoto"];
      $fbTime = $fbInfo["fbTime"];
      $fbStoryID = $fbInfo["fbStoryID"];
      $postTime = $fbInfo["fbTime"];
      if ($fbInfo["fbIcon"] != "")
      {
        $postIcon = "<img class=\"fb_post_icon\" src=\"" . $fbInfo["fbIcon"] . "\" />";
      }

      $commentLink = $this->fb_comment_link($fbStoryID);
      echo "
          <div class=\"fb_post\"> 
            <div class=\"fb_photo\"><a href=\"http://www.facebook.com/profile.php?id=$fbID\"><img src=\"$fbPhoto\" alt=\"Facebook Profile Pic\" /></a></div>
            <div class=\"fb_msg\"><h5><a href=\"http://www.facebook.com/profile.php?id=$fbID\">$fbName</a></h5>
              <p>$fbMsg</p>
      ";
      if ($fbInfo["postType"] == "link" || $fbInfo["postType"] == "photo" || $fbInfo["postType"] == "video")
      {
        $fbPicture = $fbInfo["picture"]; 
        $fbLink = $fbInfo["link"]; 
        $fbDescription = $fbInfo["linkDescription"]; 
        $fblinkName = $fbInfo["linkName"]; 
        $fbCaption = $fbInfo["linkCaption"]; 
  
        echo "<div class=\"fb_link_post\">
          <img src=\"$fbPicture\" />
          <h6><a href=\"$fbLink\">$fblinkName</a></h6>
          <p class=\"fb_link_caption\"><a href=\"http://$fbCaption\">$fbCaption</a></p>
          <p>$fbDescription</p>
        </div>";
      }
      echo "
            <div style=\"clear: both;margin-bottom:3px;\"></div>
              $postIcon<p class=\"fb_time\">$postTime &sdot; 
              <span class=\"fb_commLink\"><a href=\"$commentLink\">Comment</a></span></p>
            </div>
            <div style=\"clear: both;\"></div>
          </div>";
    }
    function fb_comment_link($fbStoryID)
    {
      $link = "http://www.facebook.com/permalink.php?";
      $splitID = explode("_", $fbStoryID);
      $link .= "id=" . $splitID[0] . "&story_fbid=" . $splitID[1];
      return $link;
    }
    function parse_fb_timestamp($fbTime)
    {
      $timeStamp = explode("T", $fbTime);
      $dateStr = $timeStamp[0];

      $timeArr = explode(":", $timeStamp[1]);
      $timeHr = $timeArr[0] - 6;
      if ($timeHr < 0)
      {
        $timeHr = 24 + $timeHr;
      }
      $timeStr = $timeHr . ":" . $timeArr[1];

      return "Posted: $timeStr $dateStr";
    }
  }
?>
