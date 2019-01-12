<?php
require_once("db.inc.php");
include("util_funcs.inc.php");
session_start();

// If logged in, get the user's user_id.
if (isset($_SESSION["authenticated"])) {
    $currUname = $_SESSION["username"];
    $uidResult = $conn->query("SELECT user_id FROM users WHERE username = '{$currUname}'");    
    confirmQuery($uidResult);
    
    $row     = $uidResult->fetch_array();
    $uid     = $row["user_id"]; // the current user's user_id
}

// If all fields are submitted
if (isset($_POST["comment_auth"]) && isset($_POST["comment_email"]) && isset($_POST["parent_id"]) && isset($_POST["post_id"]) && isset($_POST["comment_content"])) {    
    $comment_auth    = $conn->real_escape_string($_POST["comment_auth"]);
    $comment_email   = $conn->real_escape_string($_POST["comment_email"]);
    $parent_id       = $conn->real_escape_string($_POST["parent_id"]);
    $post_id         = $conn->real_escape_string($_POST["post_id"]);
    $comment_content = trim($_POST["comment_content"]);
    $comment_content = stripslashes($conn->real_escape_string($comment_content));
    
    if (!empty($comment_content)) {
        // The code for requiring members' comments to be approved by an admin or author is removed for this demo.
        $insertComment = 
        "INSERT INTO comments (parent_id, comment_auth, user_id, username, comment_email, comment_content, comment_status, comment_date)
        VALUES(?, ?, ?, ?, ?, ?, 'approved', now())";

        // The prepared statement does not need to be initialized if $conn->prepare() is used.
        if (!($stmt = $conn->prepare($insertComment))) {
            // echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            echo json_encode(array("InsertFail" => "Prepare failed: (" . $conn->errno . ") " . $conn->error));
        }
        // Bind the values for the user's name, user_id, username, email, and the comment's content.
        if (!$stmt->bind_param("isisss", $parent_id, $comment_auth, $uid, $currUname, $comment_email, $comment_content)) {
            // echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            echo json_encode(array("InsertFail" => "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error));
        }
        // Execute the statement.
        if (!$stmt->execute()) {
            // echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            echo json_encode(array("InsertFail" => "Execute failed: (" . $stmt->errno . ") " . $stmt->error));
        }

        // Get the new comment entry's ID.
        $child_comment_id = $stmt->insert_id;

        // Insert the new comment_id and the associated post_id into the postxcomment cross-reference table.
        $query = "INSERT INTO postxcomment (post_id, comment_id) VALUES({$post_id}, {$child_comment_id})";

        // Run the query, and display any errors that occur.
        if (!$conn->query($query)) {
            // echo $conn->error;
            echo json_encode(array("InsertFail" => $conn->error));
        }
        
        echo json_encode(array("commentSuccess" => $child_comment_id, "parentID" => $parent_id, "postID" => $post_id));
    } else {
        echo json_encode(array("commentError" => "Please enter a comment."));
    }
}
?>