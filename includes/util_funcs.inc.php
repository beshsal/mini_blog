
<?php
/********** HOME SITE **********/

// OUTPUT LOGO
// Pass false to function to output an unstyled logo
function outputLogo($styled=true) {
    global $conn; // required to access the database
    // Retrieve the single colored or multi-colored logo from the database.
    $query  = "SELECT * FROM logo";
    $result = $conn->query($query);
    confirmQuery($result);
    while ($row = $result->fetch_assoc()) {
        $logo        = $row["logo"];
        $logo_multi1 = $row["logo_multi1"];
        $logo_multi2 = $row["logo_multi2"];
    }
    // If a single colored logo, check if the logo should be styled. If so, return the styled logo; otherwise, return
    // the logo unstyled (just the text).
    if (isset($logo) && !empty($logo)) {
        if ($styled) {
            return $logo;
        } else {
            return "{$logo}";
        }
    // If multi-colored, check if the logo should be styled. If so, return the styled logo; otherwise, return
    // the logo unstyled.
    } elseif (isset($logo_multi1) && !empty($logo_multi1) || isset($logo_multi2) && !empty($logo_multi2)) { 
        if ($styled) {
            return $logo_multi1 . "<span>" . $logo_multi2 . "</span>";
        } else {
            return "{$logo_multi1}{$logo_multi2}";            
        }
    } else {
        return "YourLogo"; // default logo text
    }
    
    $conn->close();
}

// REDIRECT TO URL WITH PARAMETERS
function redirectToParams() {
    // Parse the URL query string into parameters, and store those parameters in an array.
    parse_str($_SERVER["QUERY_STRING"], $params);
    // Get the full URL of the page.
    $url = BASE_URL . basename(THIS_PAGE, ".php");
    // Iterate through each parameter in the array, and extract the name and value.
    foreach($params as $key => $value) {
        // If the name of the parameter is "feat", indicating a featured post, append "/featured" to
        // the URL before the parameter values; otherwise, just append the parameter values to the URL.
        if ($key == "feat") {
            $url .= "/featured";
        } else {
            $url .= "/{$value}";
        }
    }
    // If the user signs in through the comment section, make sure the user is redirected to the 
    // comment section after the form is submitted.
    if (isset($_POST["cmnt_signin"])) {
        header("Location: " . $url. "#comments");
        exit;
    } else {
        header("Location: " . $url);
        exit;  
    }    
}

// REPLACE WHITESPACES WITH HYPHENS AND REMOVE ALL NON-ALPHANUMERIC CHARACTERS EXCEPT HYPHENS AND UNDERSCORES
function formatUrlStr($string) {
    // Make sure all characters are lowercase.
    $str = strtolower($string);
    // Replace any whitespace with hyphens.
    $str = str_replace(" ", "-", $str);
    // Remove specified non-alphanumeric characters and return the string.
    return preg_replace("/[^0-9a-zA-Z_.@\-]/", "", $str); // (element to replaces, element to replace it with, and target text).
}

// MAKE PARAGRAPHS
function makeParagraphs($text) {
  // Remove whitespace from the front and back of the text.
  $text = trim($text);
  // The return concatenates an opening <p> to $text. Inside $text, new lines are converted to </p><p> by preg_replace, effectively
  // closing the first paragraph and opening a new one. the final opening <p> is closed by the </p> concatenated directly from the return.
  return "<p>" . preg_replace("/[\r\n]+/", "</p><p>", $text) . "</p>";
}

// MAKE CONTENT EXCERPTS
// The parameters are the text from which to extract sentences and the number of sentences to extract; if left blank, $number 
// defaults to 2.
function getFirst($text, $number = 2) {
  // Uses a regex to identify the end of a sentence; then it is passed to preg_split() to split the text into an array of
  // sentences; components are the regex to identify the ends of each sentence (used by preg_split to split the text into
  // an array), the target text, and the maximum number to split the text into.
  $sentences = preg_split('/([.?!]["\']?\s)/', $text, $number+1, PREG_SPLIT_DELIM_CAPTURE);
    
  // count() finds the number of elements in the $sentences array, multiplied by 2 because array contains 2 elements for each sentence.
  // If there are more than 2 elements in the array (there is more text)
  if (count($sentences) > $number * 2) {
    // array_pop() removes the last element in the $sentences array and assigns it to $remainder.
    $remainder = array_pop($sentences);
  } else {
    // If there are no more than 2 elements in the array (there is no more text), $remainder will be an empty string.
    $remainder = "";
  }
  // Create an array to hold the result.    
  $result = array();
  // implode() uses an empty string to stitch sentences back together; it is assigned to the first element of $result.
  $result[0] = implode("", $sentences);
  // Any remaining text is assigned as the second element of $result (will be remaining text or empty).
  $result[1] = $remainder;
  // Finally the result is returned.
  return $result;
}


// FETCH AND DISPLAY REPLIES TO COMMENTS
// Fetch the reply data assigned to the specified comment.
function fetchReplies($id) {     
    global $conn;
    // Get all children (replies) of the parent comment.
    $result = $conn->query("SELECT * FROM comments 
                           WHERE parent_id = {$id} 
                           ORDER BY comment_id DESC,
                           comment_date DESC");
    confirmQuery($result);
    
    // Return the query result from the function.
    return $result;
}

function displayReplies($id, $post_id, $include = "") {
    global $conn;
    // Save the reply data for the specified comment.
    $children = fetchReplies($id);
    // If there are children, retrieve the data.
    if ($children->num_rows > 0) {
        while($row = $children->fetch_array()) {
            $commentId = $row["comment_id"];
            $parentId  = $id;
            $commenter = $row["comment_auth"];
            $content   = $row["comment_content"];                            
            $date      = $row["comment_date"];
            $userId    = $row["user_id"];
            $postId    = $post_id;
            
            // If the user does not pass the function a path argument, it will be automatically set to
            // child_comments.inc.php. Note this function calls itself recursively in child_comments.inc.php.
            if (empty($include)) {
                $include = realpath(dirname(__DIR__)) . "/includes/child_comments.inc.php";   
            }            
            require $include;
        }
    }
}

// ADD HTTP TO URL
function addHttp($url) {
    return parse_url($url, PHP_URL_SCHEME) === NULL ? 'http://' . $url : $url;
}

/********** ADMIN **********/

// CONFIRM QUERY SUCCESS
// In addition to this, there is a conditional for db connection errors in db.inc.php.
function confirmQuery($result) {
    global $conn; // required to access the database
    if (!$result) {
        die("QUERY FAILED: (" . $conn->errno . ")"  . $conn->error);        
    }
}

// COUNT RECORDS
// The $column, $role, $status, and $auth_uid parameters are optional.
function countRecords($table, $column = '', $role = '', $status = '', $auth_uid = '') {    
    global $conn; // required to access the database
    // Select all members.
    // if "users" is passed to the $table parameter, select only members (not admin or author users).
    if ($table == "users") {        
        $get_records = "SELECT COUNT(*) FROM {$table} WHERE role = '{$role}'"; // be sure to pass "member" as an argument to $role
    // If the posts table
    } elseif ($table == "posts") { // be sure to pass a $column and $status argument
        // If $status has a value and $auth_uid wasn't passed a value, include $status in the query.
        if ($status != "" && $auth_uid == "") {
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE {$column} = '{$status}'";
        // If both $status and $auth_uid have values, include them in the query.
        } elseif ($status != "" && $auth_uid != "") {
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE {$column} = '{$status}' AND auth_uid = {$auth_uid}";
        // If $status wasn't passed a value and $auth_uid has a value, include $auth_uid in the query.
        } elseif ($status == "" && $auth_uid != "") {        
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE auth_uid = '{$auth_uid}'";
        } else {
            $get_records = "SELECT COUNT(*) FROM " . $table;
        }
    // If the comments table
    } elseif ($table == "comments") { // be sure to pass a $column and $status ("unapproved") argument
        // If $status has a value and $auth_uid wasn't passed a value, include $status in the query.
        if ($status != "" && $auth_uid == "") {
            $get_records = "SELECT COUNT(*) FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.{$column} = '{$status}'";
        // If both $status and $auth_uid have values, include them in the query.
        } elseif ($status != "" && $auth_uid != "") {
            $get_records = "SELECT COUNT(*) FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.{$column} = '{$status}'
                           AND posts.auth_uid = {$auth_uid}";
        // If $status wasn't passed a value and $auth_uid has a value, include $auth_uid in the query.
        } elseif ($status == "" && $auth_uid != "") { 
            $get_records = "SELECT COUNT(*) FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND posts.auth_uid = {$auth_uid}";
            
        } else {
            $get_records = "SELECT COUNT(*) FROM " . $table;
        }
                
    } else {
        // Default action        
        // Select all records (e.g. all posts or all categories).
        $get_records = "SELECT COUNT(*) FROM " . $table;
    }
    
    $result = $conn->query($get_records);        
    confirmQuery($result);
    $row = $result->fetch_row();
    $records = $row[0];
    return $records;
}

// GET USER DATA OF ALL MEMBERS CURRENTLY ONLINE
function getOnlineMembers() {
    // Check if the "onlinemembers" parameter from the GET request is set.
    if (isset($_GET["onlinemembers"])) {
        global $conn;        
        // If there is no connection to the database, include the db script directly.
        if(!$conn) {           
            // For other files, util_funcs.inc.php has access to db.inc.php from admin-header.inc.php, which is included
            // in those files, but since util_funcs.inc.php is being used directly here and db.inc.php is not included in 
            // util_funcs.inc.php, db.inc.php must be included directly.         
            require_once("../includes/db.inc.php");

            // Select any member who is currently online (has a session).
            $query = "SELECT * FROM online_users WHERE sess_role = 'member'";
            $onlineMembers = $conn->query($query);
            // Return the number of members online in the response.
            echo $count  = $onlineMembers->num_rows; 
        }
    }
    
    if (isset($_GET["reloadmemberdata"])) {
        global $conn;
        if(!$conn) {
            require_once("../includes/db.inc.php");
            
            // Initialize an array for holding usernames retrieved from the online_users table.
            $sessUname = array();
            // Get the usernames of the users (members) who are online.
            $memberData = $conn->query("SELECT sess_username FROM online_users WHERE sess_role = 'member'");
  
            while ($row = $memberData->fetch_assoc()) {
                array_push($sessUname, $row["sess_username"]); // push the usernames into the $sessUname array
            }
        
            // Use the usernames in a query to retrieve other user data for populating the tables.        
            $onlineUserData = $conn->query("SELECT * FROM users
                                           WHERE role = 'member'
                                           AND username 
                                           IN('" . implode("','", $sessUname) . "')");
            
            // If there are members online, return the contents of the table with the user data.
            if ($onlineUserData->num_rows > 0) {
                echo "<thead>
                        <tr>
                        <th>Id</th>
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($onlineUserData as $row) {
                    echo "<tr>";
                    echo "<td class='td-bold'>{$row['user_id']}</td>";            
                    echo "<td>{$row['firstname']}</td>";
                    echo "<td>{$row['lastname']}</td>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td class='td-bold'>" . ucwords($row['role']) . "</td>";
                    echo "</tr>"; 
                }
                echo "</tbody>";
            } else {
                echo "<h1 class='text-center'>There are currently no online members.</h1>";
            }
        }
    }
}
// Call the function.
getOnlineMembers();
?>