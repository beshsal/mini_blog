<?php
require_once("../../includes/db.inc.php");
include("../../includes/util_funcs.inc.php");

// Check if a postID parameter came in through the AJAX POST request.
if (isset($_POST["postID"])) {
    $postID = $_POST["postID"];
} else {
    echo json_encode(array("postIDErr" => "postID is not set"));
}

// Reset all posts' featured fields to "No".
$currFeat = $conn->query("SELECT * FROM posts WHERE featured = 'Yes'");
confirmQuery($currFeat);

$all = array();
while ($row = $currFeat->fetch_assoc()) {
    $all[] = $row;
}

foreach ($all as $row) {
    $currFeatID = $row["post_id"];
    $removeFeat = $conn->query("UPDATE posts SET featured = 'No' WHERE post_id = {$currFeatID}");
    confirmQuery($removeFeat);
} 

// Set the new featured post.
if (isset($_POST["featPost"])) {
    $featPost = $_POST["featPost"]; // the value will be "Yes" if an item is selected or "No" if deselected

    if ($featPost == "No") {
        // Update the specified record's featured field to "Yes" or "No".
        $removeFeatured = $conn->query("UPDATE posts SET featured = 'No' WHERE post_id = {$postID}");
        confirmQuery($removeFeatured);
    } 

    if ($featPost == "Yes") {
        // Update the specified record's featured field to "Yes" or "No".
        $setFeatured = $conn->query("UPDATE posts SET featured = '{$featPost}' WHERE post_id = {$postID}");
        confirmQuery($setFeatured);
        echo json_encode(array("success" => "Post " . $postID . " was successfully set to the featured post."));
    } else {
        echo json_encode(array("fail" => "The featured post could not be updated."));
    }
}