<?php
// Script for loading more comments

require_once("db.inc.php");
include("util_funcs.inc.php");
$resultsPerPage = 5; // maximum comments per page and load

if(isset($_POST["postID"])) {
    $postID = $_POST["postID"];
} else {
    echo "postID is not set";
}

// If a post request with the key 'page' is sent
// page holds the value from page=2 (data-page attribute) that is sent when the .loadmore button is clicked
if(isset($_POST["page"])):
    // Assign its value to a variable; the name indicates paging down
    $paged = $_POST["page"];
    // Select all the approved comment records from the comments table
    $query = "SELECT * FROM comments
             LEFT JOIN postxcomment USING (comment_id)
             WHERE postxcomment.post_id = {$postID}        
             AND comment_status = 'approved'
             ORDER BY comment_id DESC";

    // If the page value is greater than 0, we are paged down
    if($paged > 0) {
       // e.g. if $resultsPerPage is 5, our limit will be 5 * (page value - 1) or 5 * (2 - 1) = 5
       $page_limit = $resultsPerPage * ($paged-1);
       // The beginning of the limit in this case starts at 5
       // LIMIT 5, 5 is same as LIMIT 5 OFFSET 5", meaning return 5 records, starting at record 6 (OFFSET 5), and so forth
       $pagination_sql = " LIMIT $page_limit, $resultsPerPage"; // leave the space because this may be added to the end of $query
    }
    else {
       // Otherwise, the beginning of the LIMIT starts at 0, the first record, because we have not paged down yet
       // LIMIT 0, 5 is the same as LIMIT 5, OFFSET 0, meaning return 5 records, starting at record 0 (the first record)
       $pagination_sql = " LIMIT 0, $resultsPerPage"; // leave the space because this is added to the end of $query
    }

    $result = $conn->query($query.$pagination_sql); // $result = the result of the SELECT query concatenated to the LIMIT SQL
    
    // If there is an error in the query, it will return the boolean false
    if ($result === false) {
        echo "<h1>Your query returned a boolean of FALSE. Fix the time thing!</h1> "; 
        die(mysqli_error($conn));
    }

    // Get the number of rows/records in the result-set
    $num_rows = $result->num_rows;
    
    // If there are records
    if($num_rows > 0) {
        // Go through the result-set and extract record data to variables
        while($data = $result->fetch_array()) {
            $comment_auth    = $data["comment_auth"];
            $user_id         = $data["user_id"];
            $comment_date    = date("F j, Y", strtotime($data["comment_date"]));
            $comment_content = $data["comment_content"];
            
            echo            
            "<article class='comment'>
              <header class='comment-header' style='position: relative;'>";
              // Get the user image
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
                "<img class='img-circle enlarge' src='admin/images/user_images/{$filename}' alt='User Image' width='40'>
                <div class='largeImg' style='display: none;'>
                    <div class='user-desc'>
                    <img src='admin/images/user_images/{$filename}' alt='User Image' width='200'/>
                    <p>User description</p>
                    </div>
                </div>";
            } else {
                echo "<img class='img-circle' src='admin/images/user_images/defaultuser.png' alt='User Image' width='40'>";
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
                  <li><a>Responses (0)</a></li>
                  <li><a class='reply unavail' data-placement='bottom' data-title='' data-content='THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.'>Reply</a></li>
                </ul>
              </footer>
            </article>";
        } 
    }

    // If the number of rows/records matches the value of $resultsPerPage, there are more items to load
    if($num_rows == $resultsPerPage) {        
    ?>
        <!-- So show the load button and advance the page value -->
        <div class="load-more"><button class="loadmore" data-postid="<?php echo $postID; ?>" data-page="<?php echo $paged+1; ?>">Load More</button></div>
    <?php 
    } else {
        // Otherwise there are no more items to load, so show a message indicating this instead of the button
        echo "<div class='load-more'><h3>There are no more comments</h3></div>";
    }
endif;
?>
