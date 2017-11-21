<?php
require_once 'database.php';

function getUserName($userId)
{
   $userName = "";
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      $row = $database->getUser($userId);
      $userName= $row["firstName"] . " " . $row["lastName"];
   }
   
   return ($userName);
}

function getUserImage($userId)
{
   $userImage = "";
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      $row = $database->getUser($userId);
      $userImage = $row["imageFile"];
   }
   
   return ($userImage);
}

function getLikeText($postId)
{
   $likeText = "";
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      $likes = $database->getLikes($postId);
      $likeCount = mysqli_num_rows($likes);
      
      if ($likeCount > 0)
      {
         // Jason Tost
         if ($likeCount == 1)
         {
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            $likeText = getUserName($userId);
         }
         // Jason Tost and Michael Crowe
         else if ($likeCount == 1)
         {
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $likeText .= getUserName($userId) . " and ";
            
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $likeText .= getUserName($userId);
         }
         // Jason Tost and Michael Crowe
         else if ($likeCount == 2)
         {
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $likeText .= getUserName($userId) . " and ";
            
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $likeText .= getUserName($userId);
         }
         // Jason Tost, Michael Crowe, and 1 other
         else
         {
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $likeText .= getUserName($userId) . ", ";
            
            $row = mysqli_fetch_assoc($likes);
            $userId = $row["userId"];
            
            $other = ($likeCount == 3) ? "other" : "others";
            
            $likeText .= getUserName($userId) . ", and " . ($likeCount - 2) . " " . $other;
         }
      }
   }
   
   return ($likeText);
}

function getDateText($postId)
{
   $dateText = "";
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      $post = $database->getPost($postId);
      $dateTime = $post["dateTime"];
      
      $old = new DateTime($dateTime, new DateTimeZone('America/New_York'));
      $now = new DateTime(null, new DateTimeZone('America/New_York'));
      
      $interval = $now->diff($old);
      
      $weeks = floor($interval->d / 7);
      
      if ($weeks > 0)
      {
         $dateText = floor($interval->d / 7) . " weeks ago";
         $timeInterval = ($interval->d == 1) ? "week" : "weeks";
         $dateText = $weeks. " " . $timeInterval.  " ago";
      }
      else if ($interval->d > 0)
      {
         $timeInterval = ($interval->d == 1) ? "day" : "days";
         $dateText = $interval->d . " " . $timeInterval.  " ago";
      }
      else if ($interval->h > 0)
      {
         $timeInterval= ($interval->h == 1) ? "hour" : "hours";
         $dateText = $interval->h . " " . $timeInterval.  " ago";
      }
      else if ($interval->i > 0)
      {
         $timeInterval= ($interval->i == 1) ? "minute" : "minutes";
         $dateText = $interval->i . " " . $timeInterval.  " ago";
      }
      else
      {
         $dateText = "just now";
      }
   }
   
   return ($dateText);
}

function getPostHtml($postId)
{
   $htmlString = "";
   
   $userId = $_GET["userId"];
   $userImage = getUserImage($userId);
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      $post = $database->getPost($postId);
      $user = $database->getUser($post["userId"]);
      $userName= $user["firstName"] . " " . $user["lastName"];
      $imageFile = $user["imageFile"];
      $dateTime = $post["dateTime"];
      $content = $post["content"];
      
      $dateString = getDateText($postId);
      
      $likes = $database->getLikes($postId);
      $likeCount = mysqli_num_rows($likes);
      $likeText = getLikeText($postId);
      
      $comments = $database->getComments($postId);
      $commentCount = mysqli_num_rows($comments);
      
      $likeCountDiv = "";
      $likeSummaryDiv = "";
      if ($likeCount > 0)
      {
         if ($likeCount > 2)
         {
            $likeSummaryDiv = "<div class=\"vertical-flex like-summary-div\">";
            while ($row = mysqli_fetch_assoc($likes))
            {
               $likeUserName = getUserName($row["userId"]);
               $likeSummaryDiv .= "<div class=\"like-summary-name-div\">$likeUserName</div>";
            }
            $likeSummaryDiv .= "</div>";
         }
         
         $likeCountDiv = 
<<<HEREDOC
         <div class="horizontal-flex like-count-div">
            <div style="margin-right:10px;"><img src="./images/cheddar-small.png" width="25"/></div>
            <div class="like-text-div">
               $likeText
               $likeSummaryDiv
            </div>
         </div>
HEREDOC;
      }
         
      $commentsDivs = "";
      if ($commentCount > 0)
      {
         while ($row = mysqli_fetch_assoc($comments))
         {
            $commentUserId = $row["userId"];
            $commentUserName = getUserName($commentUserId);
            $commentUserImage = getUserImage($commentUserId);
            $commentContent = $row["content"];
         
            $commentsDivs .=
<<<HEREDOC
            <div class="horizontal-flex comment-div">
               <div style="margin:5px"><img width="30" src="./images/$commentUserImage"/></div>
               <div><b>$commentUserName</b> $commentContent</div>
            </div>
HEREDOC;
         }
      }
      
      $htmlString =
<<<HEREDOC
         <div class="post-div vertical-flex" id="post-$postId-div">
            <div class="horizontal-flex">
               <div style="margin:5px"><img width="50" src="./images/$imageFile"/></div>
               <div class="vertical-flex">
                  <span class="post-author">$userName</span>
                  <span class="post-date">$dateString</span>
               </div>
            </div>
            <div class="vertical-flex">$content</div>
            <div class="horizontal-flex like-div" onclick="like($userId, $postId)">
               <div style="margin-right:10px;"><img src="./images/cheddar.png" width="30"/></div>
               <a href="">Give cheddar</a>
            </div>
            $likeCountDiv
            $commentsDivs
            <div class="horizontal-flex comment-div">
               <div style="margin:5px"><img width="30" src="./images/$userImage"/></div>
               <input class="comment-input" type="text" id="comment-$postId-input" placeholder="Lay down some gouda ..." onkeypress="if (checkEnter(event) == true) comment($userId, $postId, 'comment-$postId-input')"/>
            </div>
         </div>
HEREDOC;
   }
      
   return ($htmlString);
}

// Add post
if (isset($_GET["action"]))
{
   $action = $_GET["action"];
   
   $database = new CheeseBookDatabase("localhost", "root", "", "cheesebook");
   
   $database->connect();
   
   if ($database->isConnected())
   {
      switch ($action)
      {
         case "post":
         {
            if (isset($_GET["userId"]) && isset($_GET["content"]))
            {
               $userId = $_GET["userId"];
               $content = $_GET["content"];
               
               $result = $database->addPost($userId, $content);
               
               if ($result)
               {
                  echo getPostHtml($result["id"]);
               }
            }
            break;
         }
         
         case "comment":
         {
            if (isset($_GET["userId"]) && isset($_GET["postId"]) && isset($_GET["content"]))
            {
               $userId = $_GET["userId"];
               $postId = $_GET["postId"];
               $content = $_GET["content"];
               
               $result = $database->addComment($userId, $postId, $content);
               
               if ($result)
               {
                  echo getPostHtml($postId);
               }
            }
            break;
         }
         
         case "like":
         {
            $userId = $_GET["userId"];
            $postId = $_GET["postId"];
            
            if (isset($_GET["userId"]) &&  isset($_GET["postId"]))
            {
               $isLiked = $database->isLiked($userId, $postId);
               
               if (!$isLiked)
               {
                  $result = $database->addLike($userId, $postId);
               }
               
               echo getPostHtml($postId);
            }
            break;
         }
         
         default:
         {
            break;         
         }
      }
   }
}
?>