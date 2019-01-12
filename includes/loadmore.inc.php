<?php
// The script for loading more comments
// * See the JS/AJAX code for loading results in footer.inc.php. *
require_once("db.inc.php");
include("util_funcs.inc.php");
session_start();
$resultsPerPage = 5; // maximum comments per page and load

// When the .loadmore button is clicked, it triggers an AJAX POST request that sends the post_id (postID) of the post
// that the comments belong to and a paging value (page). Initially, this value is page=2, but it will change if there
// are more comments to load after the button is clicked.

// Get the post_id sent from the AJAX request.
if (isset($_POST["postID"])) {
    $postId = $_POST["postID"];
} else {
    echo "postID is not set";
}

// Get the page value from the AJAX request.
if (isset($_POST["page"])):
    // Assign its value to a variable; the name indicates paging down.
    $paged = $_POST["page"];
    // Select all the approved comment records associated with the identified post from the comments table.
    $query = "SELECT * FROM comments
             LEFT JOIN postxcomment USING (comment_id)
             WHERE postxcomment.post_id = {$postId}        
             AND comment_status = 'approved'
             AND parent_id = 0
             ORDER BY comment_id DESC";

    // If the page value is greater than 0, we are paged down.
    if ($paged > 0) {
       // e.g. if $resultsPerPage is 5, our limit will be 5 * (page value - 1) or 5 * (2 - 1) = 5
       $page_limit = $resultsPerPage * ($paged-1);
       // The beginning of the limit in this case starts at 5.
       // LIMIT 5, 5 is the same as LIMIT 5 OFFSET 5", meaning return 5 records, starting at record 6 (OFFSET 5), and so forth.
       $pagination_sql = " LIMIT {$page_limit}, {$resultsPerPage}"; // leave the space because this may be added to the end of $query
    }
    else {
       // Otherwise, the beginning of the LIMIT starts at 0, the first record, because we have not paged down yet.
       // LIMIT 0, 5 is the same as LIMIT 5, OFFSET 0, meaning return 5 records, starting at record 0 (the first record).
       $pagination_sql = " LIMIT 0, {$resultsPerPage}"; // leave the space because this may be added to the end of $query
    }

    $result = $conn->query($query.$pagination_sql); // $result = the result of the SELECT query concatenated to the LIMIT SQL
    
    // If there is an error in the query, it will return the boolean false.
    if ($result === false) {
        echo "<h1>An error occured while loading.</h1>";
        die(mysqli_error($conn));
    }

    // Get the number of rows/records in the result-set.
    $num_rows = $result->num_rows;
    
    // If there are records
    if ($num_rows > 0) {
        // Go through the result-set and extract the data to variables.
        while($data = $result->fetch_array()) {
            $comment_id      = $data["comment_id"];
            $parent_id       = $data["comment_id"];
            $comment_auth    = $data["comment_auth"];
            $user_id         = $data["user_id"];
            $comment_date    = date("F j, Y", strtotime($data["comment_date"]));
            $comment_content = $data["comment_content"];
            
            // Get each comment's responses (if any).
            $responses = $conn->query("SELECT * FROM comments 
                                      WHERE comment_status = 'approved'
                                      AND parent_id = {$parent_id}");
            confirmQuery($responses);
            
            echo            
            "<article class='comment parent' id='comment_id{$comment_id}'>
              <header class='comment-header' style='position: relative;'>";
              // Get the user's image.
              $getFilename = $conn->query("SELECT filename FROM user_images WHERE user_id = {$user_id}");
              confirmQuery($getFilename);
              $row = $getFilename->fetch_array();
              $filename = $row["filename"];
            
              $getRole = $conn->query("SELECT role FROM users WHERE user_id = {$user_id}");
              confirmQuery($getRole);
              $row = $getRole->fetch_array();
              $role = ucfirst($row["role"]);
            
            if (isset($filename) && !empty($filename)) {
                echo 
                "<div class='user-thumb xs enlarge'
                      style='cursor: pointer; background-image: url(\"admin/images/user_images/$filename\")'
                      alt='User Image'>
                </div>                
                <div class='largeImg' style='display: none;'>
                    <div class='user-desc'>
                    <img src='admin/images/user_images/{$filename}' alt='User Image' width='200'/>
                    </div>
                </div>";
            } else {
                echo
                "<div class='user-thumb xs' 
                      style='cursor: pointer; background-image: url('admin/images/user_images/defaultuser.png')' 
                      alt='User Image'>
                </div>";
            }
                
            echo 
            "<h4 class='comment-details'>{$comment_auth} <span style='font-size: 12px; font-family: Sans-Serif;'> ({$role})</span></h4>
                <p><small><i>Posted on {$comment_date}</i></small></p>                    
              </header>
              <section class='comment-content'>
              <p>" . trim($comment_content) . "</p>
              </section>
              <footer class='comment-footer'>
                <ul class='list-inline'>                      
                  <li><a>Responses (" . $responses->num_rows . ")</a></li>
                  <li><a id='{$comment_id}' class='reply closed'>Reply</a></li>
                </ul>
              </footer>";
            
              echo "<div id='form-id{$comment_id}' class='child-comment-form-wrapper' style='display: none;'>";
              
              if (isset($_SESSION["authenticated"])) {
              echo
              "<form action='' method='post' class='child-comment-form form-horizontal' role='form'>
                  <p class='text-center error childCommentErr'></p>
                  <input name='comment_auth' type='hidden' value='" . $_SESSION['firstname'] . " " . $_SESSION['lastname'] . "'> 
                  <input name='comment_email' type='hidden' value='" . $_SESSION['email'] . "'>
                  <input name='parent_id' type='hidden' value='{$parent_id}'>
                  <input name='post_id' type='hidden' value='{$postId}'>
                  <div class='form-group'>
                       <div class=''>
                       <textarea name='comment_content' class='form-control' id='addComment' rows='5'></textarea>
                       </div>
                  </div>
                  <div class='form-group'>
                       <div class=''>                    
                           <button name='insert_child_comment' class='btn standard-btn send-reply' type='submit'>Reply</button>
                       </div>
                  </div>            
              </form>";
              } else {
              echo 
              "<h4 class='signin-warning text-center' style='margin-top: 8px;'>You must sign in to leave a comment.</h4>";              
              }
              echo
              "</div>";
              // echo displayReplies($parent_id, $postId);
              displayReplies($parent_id, $postId);
            echo
              "<div class='showMore' style='display: none;'><span>Show more replies</span></div>
              <div class='showLess' style='display: none;'><span>Show fewer replies</span></div>
            </article>";
        } 
    }

    // If the number of rows/records matches the value of $resultsPerPage, there are more items to load.
    if ($num_rows == $resultsPerPage) {        
    ?>
        <!-- So show the .loadmore button and advance the page value. -->
        <div class="load-more"><button class="loadmore" data-postid="<?php echo $postId; ?>" data-page="<?php echo $paged+1; ?>">Load More</button></div>
    <?php 
    } else {
        // Otherwise there are no more items to load, so show a message indicating this instead of the button.
        echo "<div class='load-more'><h3>There are no more comments.</h3></div>";
    }
endif;
?>
