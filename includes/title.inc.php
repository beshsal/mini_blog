<?php
// This script sets the $title variable used in the title tags in html_head.inc.php.

// $_SERVER['SCRIPT_FILENAME'] gets the absolute path for the current page, and basename() extracts the filename.
// The second argument indicates to strip the extension from the filename.
$title = basename(THIS_PAGE, ".php");

// Look for an underscore in $title, and replace it with a space.
$title = str_replace("_", " ", $title);

// Exception for the index: if $title is equal to "index", replace its value with "home".
if ($title == "index") {
  $title = "home";
}

// Exception for the post page: if $title is equal to "post", replace its value with the title of the post.
if ($title == "post") {
    // Get the specific post's post_id from the URL query string.
    if(isset($_GET["postid"])) {
        $postId = $_GET["postid"];
        
        // Use the post_id to retrieve the title of the post.
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
// Capitalize the first character of each word in the title.
$title = ucwords($title);
?>