<?php
// $_SERVER['SCRIPT_FILENAME'] gets the absolute path for the current page, and basename() extracts the filename
// The second argument indicates to strip the extension from the filename
$title = basename(THIS_PAGE, ".php");

// Look for an underscore in $title and replace it with a space
$title = str_replace("_", " ", $title);

// Exception for index, if $title is equal to "index"
if ($title == "index") {
  $title = "home";
}

if ($title == "post") {
    if(isset($_GET["postid"])) {
        // Save its value.
        $postId = $_GET["postid"];

        // Get the specific post by retrieving its post_id value through the query string
        $query = "SELECT title FROM posts WHERE post_id = {$postId}";

        // Run the query
        $result = $conn->query($query);

        // Confirm query ran successfully
         confirmQuery($result);

        while($row = $result->fetch_assoc()) {
             $title = $row["title"];
        }
    }
}

$title = ucwords($title);
?>