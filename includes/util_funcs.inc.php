
<?php
/********** HOME SITE **********/

// OUTPUT LOGO
// Pass false to function to output an unstyled logo
function outputLogo($styled=true) {
    global $conn; // required to access the database
    
    $query  = "SELECT * FROM logo";
    $result = $conn->query($query);
    confirmQuery($result);
    while ($row = $result->fetch_assoc()) {
        $logo        = $row["logo"];
        $logo_multi1 = $row["logo_multi1"];
        $logo_multi2 = $row["logo_multi2"];
    }
        
    if (isset($logo) && !empty($logo)) {
           return $logo;
    } elseif (isset($logo_multi1) && !empty($logo_multi1) || isset($logo_multi2) && !empty($logo_multi2)) { 
        if ($styled) {
            return $logo_multi1 . "<span>" . $logo_multi2 . "</span>";
        } else {
            return "{$logo_multi1}{$logo_multi2}";            
        }
    } else {
        return "YourLogo";
    }
    
    $conn->close();
}

// REDIRECT TO URL WITH PARAMETERS
function redirectToParams() {
    parse_str($_SERVER["QUERY_STRING"], $params);
    $url = BASE_URL . basename(THIS_PAGE, ".php");
    foreach($params as $key => $value) {
        if ($key == "feat") {
            $url .= "/featured";
        } else {
            $url .= "/{$value}";
        }
    }
    
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
    $str = strtolower($string);
    $str = str_replace(' ', '-', $str);
    return preg_replace("/[^0-9a-zA-Z_.@\-]/", '', $str);
}

// MAKE PARAGRAPHS
function convertToParas($text) {
  // Remove whitespace from the front and back of the text
  $text = trim($text);
  // Return the paragraph(s) including preg_replace (element to replace, element to replace it with, and target text)
  // So in $text, an opening <p> tag is added immediately in the return, preg_replace converts any new line to </p><p>, effectively
  // closing the first paragraph and starting a new one; the final opening <p> is closed by the </p> added directly from the return
  return "<p>" . preg_replace("/[\r\n]+/", "</p><p>", $text) . "</p>";
}

// MAKE CONTENT EXCERPTS
// Passed arguments are the text from which to extract sentences and the number of sentences to extract; if left blank, $number 
// defaults to 2
function getFirst($text, $number = 2) {
  // Uses a regex to indentify the end of a sentence; then it is passed to preg_split() to split the text into an array that contains
  // sentences as elements; components are the regex to identify the ends of each sentence (used by preg_split to split the text into
  // an array), the target text, the max number to split the text into
  $sentences = preg_split('/([.?!]["\']?\s)/', $text, $number+1, PREG_SPLIT_DELIM_CAPTURE);
    
  //count() finds the number of elements in $sentences array, multiplied by 2 because array contains 2 elements for each sentence
  if (count($sentences) > $number * 2) {
    // If there is more text, array_pop() removes the last element of $sentences array and assigns it to $remainder
    $remainder = array_pop($sentences);
  } else {
    // If no more text, $remainder is an empty string
	$remainder = '';
  }
    
  $result = array();
  // implode() uses an empty string to stitch sentences back together; it is assigned to first element of $result
  $result[0] = implode('', $sentences);
  // Any remaining text is assigned as the second element of $result (will be remaining text or empty)
  $result[1] = $remainder;
  // Finally the result of the function is returned
  return $result;
}

/********** ADMIN **********/

// CONFIRM QUERY SUCCESS
// In addition to this, there is a conditional for db connection errors in db.inc.php
function confirmQuery($result) {
    global $conn;
    if (!$result) {
        die("QUERY FAILED: (" . $conn->errno . ")"  . $conn->error);        
    }
}

// COUNT RECORDS
// $column, $role, $status, and $auth_uid are optional
function countRecords($table, $column = '', $role = '', $status = '', $auth_uid = '') {    
    global $conn;    
    // Select all members
    // if "users" is passed to the $table parameter, select only members (not admin or author users)
    if ($table == "users") {        
        $get_records = "SELECT COUNT(*) FROM {$table} WHERE role = '{$role}'"; // be sure to pass "members" as an argument to $role    
    } elseif ($table == "posts") { // be sure to pass a $column and $status argument
        if ($status != '' && $auth_uid == '') {
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE {$column} = '{$status}'";
        } elseif ($status != '' && $auth_uid != '') {
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE {$column} = '{$status}' AND auth_uid = {$auth_uid}";
        } elseif ($status == '' && $auth_uid != '') {        
            $get_records = "SELECT COUNT(*) FROM {$table} WHERE auth_uid = '{$auth_uid}'";
        } else {
            $get_records = "SELECT COUNT(*) FROM " . $table;
        }        
    } elseif ($table == "comments") { // be sure to pass a $column and $status ("unapproved") argument
        if ($status != "" && $auth_uid == "") {
            $get_records = "SELECT COUNT(*) FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.{$column} = '{$status}'";
        } elseif ($status != "" && $auth_uid != "") {
            $get_records = "SELECT COUNT(*) FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.{$column} = '{$status}'
                           AND posts.auth_uid = {$auth_uid}";
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
        // Select all records (e.g. all posts or all categories)
        $get_records = "SELECT COUNT(*) FROM " . $table;
    }
    
    $result = $conn->query($get_records);        
    confirmQuery($result);
    $row = $result->fetch_row();
    $records = $row[0];
    return $records;
}

// GET A COUNT OF ALL MEMBERS CURRENTLY ONLINE
function getOnlineMembers() {
    // Check if the "onlinemembers" parameter from the GET request has a value
    if (isset($_GET["onlinemembers"])) {
        global $conn;        
        // If there is no connection to the database, include the db script
        if(!$conn) {           
            // For other files, util_funcs.inc.php has access to db.inc.php from admin-header.inc.php, which is included
            // in those files, but since util_funcs.inc.php is being used directly here and db.inc.php is not included in 
            // util_funcs.inc.php, db.inc.php must be included            
            require_once("../includes/db.inc.php");

            // Select any member that is currently online (has a session)
            $query = "SELECT * FROM online_users WHERE sess_role = 'member'";
            $onlineMembers = $conn->query($query);
            
            echo $count  = $onlineMembers->num_rows; // get the count of members online
        }
    }
}
// Call the function
getOnlineMembers();
?>