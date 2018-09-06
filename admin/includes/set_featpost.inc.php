<?php
require_once("../../includes/db.inc.php");
include("../../includes/util_funcs.inc.php");

// Check if postID and featPost came in with the AJAX request
if (isset($_POST["postID"])) {
    $postID = $_POST["postID"];
} else {
    echo "postID is not set";
}

if (isset($_POST["featPost"])) {
    $featPost = $_POST["featPost"];    
    echo "Post " . $postID . " was successfully set to the featured post.";
} else {
    echo "The featured post could not be updated.";
}

$setFeatured = $conn->query("UPDATE posts SET featured = '{$featPost}' WHERE post_id = {$postID}");
confirmQuery($setFeatured);