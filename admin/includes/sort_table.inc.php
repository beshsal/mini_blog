<?php
require_once("../../includes/db.inc.php");
include("../../includes/util_funcs.inc.php");
session_start();

$currRole  = $_SESSION["role"]; // either admin or author
$currUname = $_SESSION["username"];
$currUid   = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row       = $currUid->fetch_array();
$uid       = $row["user_id"];
$order     = $_POST["order"];
$column    = $_POST["column"];

if (isset($_POST["status"]) && !empty($_POST["status"])) {
    $status = $_POST["status"];
}
if (isset($_POST["catID"]) && !empty($_POST["catID"])) {
    $catID = $_POST["catID"];
}
if (isset($_POST["user"]) && !empty($_POST["user"])) {
    $user = $_POST["user"];
}

$search1 = isset($_POST["search1"]) && !empty($_POST["search1"]) ? $_POST["search1"] : "";
$search2 = isset($_POST["search2"]) && !empty($_POST["search2"]) ? $_POST["search2"] : "";

if (isset($status)) {
    if (isset($user)) {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE post_status = '{$status}'
                     AND posts.auth_uid = {$uid}
                     ORDER BY " . $column . " " . $order . " ";  
    } else {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE post_status = '{$status}'
                     ORDER BY " . $column . " " . $order . " ";
    }
    
    $result = $conn->query($sortTable);
    confirmQuery($result);
} elseif (isset($catID)) {
    if (isset($user)) {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     LEFT JOIN postxcat USING (post_id)
                     WHERE postxcat.cat_id = {$catID}
                     AND posts.auth_uid = {$uid}
                     ORDER BY " . $column . " " . $order . " ";
    } else {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     LEFT JOIN postxcat USING (post_id)
                     WHERE postxcat.cat_id = {$catID}
                     ORDER BY " . $column . " " . $order . " ";
    }
    
    $result = $conn->query($sortTable);
    confirmQuery($result);
} elseif (isset($user) && !isset($status)) {
    $sortTable = "SELECT * FROM posts
                 LEFT JOIN images USING (image_id)
                 WHERE posts.auth_uid = {$uid}
                 ORDER BY " . $column . " " . $order . " ";
    
    $result = $conn->query($sortTable);
    confirmQuery($result);    
} elseif (isset($search1) || isset($search2)) {
    if (isset($user)) {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.title LIKE '%" . $search2 . "%'
                     AND posts.auth_uid = {$uid}
                     ORDER BY " . $column . " " . $order . " ";
    } else {
        $sortTable = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.post_auth LIKE '%" . $search1 . "%'
                     AND posts.title LIKE '%" . $search2 . "%'
                     ORDER BY " . $column . " " . $order . " ";
    }
    
    $result = $conn->query($sortTable);
    confirmQuery($result);
} else {
    $sortTable = "SELECT * FROM posts
                 LEFT JOIN images USING (image_id)
                 ORDER BY " . $column . " " . $order . " ";

    $result = $conn->query($sortTable);
    confirmQuery($result);
}

$html = "";

while ($row = $result->fetch_assoc()) {
    $post_id       = $row["post_id"];
    $image_id      = $row["image_id"];
    $auth_uid      = $row["auth_uid"];
    $featured      = $row["featured"];
    $title         = $row["title"];
    $post_auth     = $row["post_auth"];
    $post_date     = $row["post_date"];
    $updated       = (isset($row["updated"]) && $row["updated"] != "0000-00-00" ? $row["updated"] : "&mdash;");
    $post_image    = "images/post_images/{$row['filename']}";
    $post_views    = $row["post_views"]; // the value is set in post.php
    $caption       = $row["caption"];
    $post_comments = $row["post_comments"];
    $post_status   = ucwords($row["post_status"]);            
    $disabled      = "";
            
    // This is used to ensure that only an admin has access to setting any post to a featured post. 
    // Authors can only set one of their own posts to a featured post (may disable this feature for authors).

    // If the currently logged-in author's user_id is not the same as a post record's user_id,
    // then "disabled" is added to the field.
    if ($currRole == "author") {
        if ($auth_uid != $uid) {
            $disabled = "disabled";
        }
    }
    
    $html .= "<tr>"; 
    
    if ($currRole == "author") {
        if ($auth_uid == $uid) {
            $html .= "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
        } else {
            $html .= "<td><input class='checkBoxes disabled' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
        }
    } else {
        $html .= "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
    }            
    $html .= "<td class='td-bold'>{$post_id}</td>";            
    $html .= "<td>";
    
    // Radio button for setting the featured post
    // Note the disabled attribute ($disabled). 
    $html .= "<input type='radio' name='set_featpost' value='' data-postid='{$post_id}' class='btn set-featpost {$disabled}'";
    
    // If selected to be a featured post, add a checked attribute. If not, add unchecked. 
    // This is done through an AJAX request. See the respective JS in admin_footer.inc.php 
    // ("SET FEATURED POST") and the code in set_featpost.inc.php.        
    if ($featured == "Yes") {                
        $html .= " checked";
    } elseif ($featured == "No") {
        $html .= " unchecked";
    }

    $html .= ">";
    $html .= "</td>";
    $html .= "<td class='td-left'><a href='../post/{$post_id}/" . formatUrlStr($title) . "'>{$title}</a></td>";
    $html .= "<td class='td-left'>{$post_auth}</td>"; 
    $html .= "<td class='td-bold td-date'>{$post_date}</td>";
    $html .= "<td class='td-bold td-date'>{$updated}</td>";

    // Get the categories associated with each post.
    $getCategories = "SELECT * FROM categories
                     LEFT JOIN postxcat USING (cat_id)
                     WHERE postxcat.post_id = {$post_id}                              
                     ORDER BY category ASC";

    $categories = $conn->query($getCategories);            
    $cats = array();

    while ($row = $categories->fetch_assoc()) {                
        $catId  = $row["cat_id"];
        $cats[] = $row["category"];
    }
    
    $html .= "<td class='td-left'>";
    // Categories are separated by commas.          
    $catlist = rtrim(implode(', ', $cats));
    $html .= $catlist; 
    $html .= "</td>";
    
    // If there is an image, display it. Otherwise inform the user there is no image.
    if ($image_id != NULL) { 
        $html .= "<td><img width='100' src='{$post_image}'></td>";
        $html .= "<td class='td-left'>{$caption}</td>";
    } else { 
        $html .= "<td>No Image</td>";
        $html .= "<td></td>";
    }
    $html .= "<td>{$post_views}</td>"; 
    $html .= "<td>{$post_comments}</td>"; 
    $html .= "<td class='td-bold td-status'>{$post_status}</td>";

    // An author may edit only his or her own post(s). A disabled attribute is added to other posts, which prevents an
    // author from editing them. However, authors may still view other posts.
    if ($currRole == "author") {
        if ($auth_uid == $uid) {
            $html .= "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";  
        } else {
            $html .= "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-redtext-btn disabled'>Disabled</a></td>";
        }                
    } else {
        $html .= "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";
    }   

    // Only admin may delete posts. This displays a delete link on each post for admin.         
    if ($currRole == "admin") {
        // The post_id of the post the user wants to delete is needed when the Delete link is clicked.
        // When the link (a.delete) is clicked, JS attaches a click event to it that invokes the modal and passes
        // it the post_id through the rel attribute (see the respective JS in custom.js, "SINGLE ITEM DELETION").
        if ($featured == "Yes") {
            $html .= "<td><a class='btn delete-btn delete disabled'>Delete</a></td>";
        } else {
            $html .= "<td><a rel='{$post_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
        }    
    }            
    $html .= "</tr>"; // close the table row
}

echo json_encode(array("html" => $html));