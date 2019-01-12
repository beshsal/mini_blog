<?php
// Get the name of the current page and the user's role.
$pageName = basename(THIS_PAGE, ".php"); // users
$currRole = $_SESSION["role"];

// Give only admin access to the users page.
if ($currRole != "admin") {
    header("Location: " . BASE_URL);
    exit;
}

// Get the current admin user's user_id.
$currUname = $_SESSION["username"];
$currUid   = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row       = $currUid->fetch_array();
$uid       = $row["user_id"];

// Initialize a variable to hold search errors.
$searchError = "";

// Get the users. All users will be displayed by default.
$query = "SELECT * FROM users";
$result = $conn->query($query);
confirmQuery($result);

if (isset($_GET["lmt"])) $lmt = true;

// Users can by sorted by role - member, author, or admin. If the user selects a role from the Sort by dropdown menu, 
// it's value is sent through the URL query string.
if (isset($_GET["role"])) {
    // Get the selected role to sort by.    
    $userRole = $_GET["role"];
    
    // Select the specified users.
    switch($userRole) {            
        case "member":                
        $viewUsers = "SELECT * FROM users WHERE role = 'member'";
        $result = $conn->query($viewUsers);
        confirmQuery($result);
        break;

        case "author":
        $viewUsers = "SELECT * FROM users WHERE role = 'author'";
        $result = $conn->query($viewUsers);
        confirmQuery($result);
        break;

        case "admin":
        $viewUsers = "SELECT * FROM users WHERE role = 'admin'";
        $result = $conn->query($viewUsers);
        confirmQuery($result);
        break;
    }
}


// Search users by first name and/or last name.
if (isset($_POST["search_users1"])) {
    $firstname = trim($_POST["by_firstname"]);
    $lastname  = trim($_POST["by_lastname"]);
    $firstname = $conn->real_escape_string($firstname);
    $lastname  = $conn->real_escape_string($lastname);
    
    if (isset($lmt)) {
        $srchParams = "?role=member&lmt=1&search=users";
    } else {
        $srchParams = "?search=users";
    }
    
    if (isset($firstname) && !empty($firstname)) {
        $srchParams .= "&srch1={$firstname}";
    }
    
    if (isset($lastname) && !empty($lastname)) {
        $srchParams .= "&srch2={$lastname}";
    }
    
    // Refresh the page and add the search parameters to the URL query string.
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If a search is submitted from the first search option, get the search term(s) from the URL query string.
if (isset($_GET["search"]) && $_GET["search"] != "users2") {
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";
    
    // If both fields are filled
    if (!empty($search1) || !empty($search2)) {
        if (!isset($lmt)) {
            $searchUser = "SELECT * FROM users WHERE firstname LIKE '%" . $search1 . "%'
                          AND lastname LIKE '%" . $search2 . "%'";
        } else {
            $searchUser = "SELECT * FROM users 
                          WHERE firstname LIKE '%" . $search1 . "%'
                          AND lastname LIKE '%" . $search2 . "%'
                          AND role='member'";
        }
    } 

    // If empty fields are submitted, add an error message to $searchError.
    if (empty($searchUser)) {        
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field."; 
    // Otherwise, run the query.       
    } else {        
        $result = $conn->query($searchUser);
        confirmQuery($result);
        $search = $result;
        // if no results are found, add an error message to $searchError.
        if ($search->num_rows == 0) {
            if (!isset($lmt)) {
                $searchError = "No results found! The search term(s) may be misspelled or the record may not exist.";
            } else {
                $searchError = "There are no members who match the search term(s) you entered. 
                               <br>The search term(s) may be misspelled or the record may not exist.";
            }
        }
    }  
}

if (isset($_POST['search_users2'])) {
    $username = trim($_POST['by_username']);
    $email    = trim($_POST['by_email']); 
    $username = $conn->real_escape_string($username);
    $email    = $conn->real_escape_string($email);
    
    if (isset($lmt)) {
        $srchParams = "?role=member&lmt=1&search=users2";
    } else {
        $srchParams = "?search=users2";
    }
    
    if (isset($username) && !empty($username)) {
        $srchParams .= "&srch1={$username}";
    }
    
    if (isset($email) && !empty($email)) {
        $srchParams .= "&srch2={$email}";
    }
    
    // Refresh the page and add the search parameters to the URL query string.
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If a search is submitted from the second search option, get the search term(s) from the URL query string.
if (isset($_GET["search"]) && $_GET["search"] == "users2") {
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";
    
    // If at least one of the fields is filled (not empty)
    if (!empty($search1) || !empty($search2)) { 
        if (!isset($lmt)) {
            $searchUser = "SELECT * FROM users WHERE username LIKE '%" . $search1 . "%' 
                          AND email LIKE '%" . $search2 . "%'";
        } else {
            $searchUser = "SELECT * FROM users 
                          WHERE username LIKE '%" . $search1 . "%' 
                          AND email LIKE '%" . $search2 . "%'
                          AND role='member'";
        }
    } 
    
    // If empty fields are submitted, add an error message to $searchError.   
    if (empty($searchUser)) {        
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
    // Otherwise, run the query.        
    } else {        
        $result = $conn->query($searchUser);
        confirmQuery($result);
        $search = $result;
        // if no results are found, add an error message to $searchError.
        if ($search->num_rows == 0) {
            if (!isset($lmt)) {
                $searchError = "No results found! The search term(s) may be misspelled or the record may not exist.";
            } else {
                $searchError = "There are no members who match the search term(s) you entered. 
                               <br>The search term(s) may be misspelled or the record may not exist.";
            }
        }
    }    
}

// "checkBoxArray" is the name of the checkbox input element created for each user; the value(s) of the checkBoxArray array must
// be captured to know which user(s) to apply the action to.

if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST["checkBoxArray"] as $userValueId) {
        // "bulk_options" is the name of the select element; this takes the value from the select element (the value of an 
        // option element when selected) and assigns it to a variable, which will be used in the switch statement.
        $bulk_options = $_POST["bulk_options"];
        
        // Apply the new role to the checked user(s).
        switch($bulk_options) {
            case "admin_role":                
            $setRole = "UPDATE users SET role = 'admin' WHERE user_id = {$userValueId}";                    
            $userRole = $conn->query($setRole);
            confirmQuery($userRole);                        
            break;     
                        
            case "auth_role":                
            $setRole = "UPDATE users SET role = 'author' WHERE user_id = {$userValueId}";                    
            $userRole = $conn->query($setRole);                        
            confirmQuery($userRole);            
            break;
                    
            case "delete_selected":                    
            $deleteUsers = $conn->query("DELETE FROM users WHERE user_id = {$userValueId}");                        
            confirmQuery($deleteUsers);            
            break; 
        }
    }    
    
    if (isset($_GET["lmt"])) {
        header("Location: " . BASE_URL . THIS_PAGE . "?role=member&lmt=1");
        exit;
    } else {
        header("Location: " . BASE_URL . THIS_PAGE);
        exit;
    }    
}
?>
<nav class="navbar navbar-default search-sort-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php<?php if(isset($lmt)) echo '?role=member&lmt=1'; ?>">View all <span class="sr-only">(current)</span></a></li>
        <?php if (!isset($lmt)) { ?>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="users.php?role=admin">Admin</a></li>
            <li><a href="users.php?role=author">Authors</a></li>
            <li><a href="users.php?role=member">Members</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="users.php">View all</a></li>
          </ul>
        </li>
        <?php } ?> 
      </ul>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <i class="fa fa-search"></i>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php<?php if(isset($lmt)) echo '?role=member&lmt=1'; ?>">View all <span class="sr-only">(current)</span></a></li>
        <?php if (!isset($lmt)) { ?>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="users.php?role=admin">Admin</a></li>
            <li><a href="users.php?role=author">Authors</a></li>
            <li><a href="users.php?role=member">Members</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="users.php">View all</a></li>
          </ul>
        </li>
        <?php } ?>
      </ul>
      <ul id="search-wrapper" class="nav navbar-nav navbar-right">
        <form id="searchUserForm" action="" method="post" class="navbar-form navbar-left show" role="search">
          <div id="fieldset1">
              <label class="search-label">Search for users by first name and/or last name:</label>
              <div class="form-group">
                <input type="search" class="form-control" id="fname" placeholder="Search by first name" name="by_firstname" value="<?php if (isset($firstname) && $searchError != "") { echo $firstname; } ?>">
              </div>
              <div class="form-group">
                <input type="search" class="form-control" id="lname" placeholder="Search by last name" name="by_lastname" value="<?php if (isset($lastname) && $searchError != "") { echo $lastname; } ?>">
              </div>
              <button type="submit" name="search_users1" class="btn gray-btn">Search</button>
          </div>         
          <div id="fieldset2" style="display:none">
              <label class="search-label">Search for users by username and/or email:</label>
              <div class="form-group">
                <input type="search" class="form-control" id="uname" placeholder="Search by username" name="by_username" value="<?php if (isset($username) && $searchError != "") { echo $username; } ?>">
              </div>
              <div class="form-group">
                <input type="search" class="form-control" id="email" placeholder="Search by email" name="by_email" value="<?php if (isset($email) && $searchError != "") { echo $email; } ?>">
              </div>
              <button type="submit" name="search_users2" class="btn gray-btn">Search</button>
          </div>
          <div class="form-group switchFields">
            <label class="switchFields-label">
                Search by username and/or email<input name="switchfieldset" class="switchfieldset" type="checkbox" style="margin-left: 4px;" 
                <?php 
                if(isset($_GET["search"]) && $_GET["search"] == "users2") echo "checked='checked'";
                ?>>
            </label>
          </div>
        </form>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="view-content col-xs-12">
<?php
// If there is a search error, display it.
if (isset($searchError) && $searchError != "") {
    echo "<p class='error'>{$searchError}</p>";
}
    
// If the sort result has no value, a respective message is placed under the sort navigation.
if (!isset($search) && $result->num_rows == 0 && isset($userRole) && $userRole == "author") {
    echo "<h1 class='text-center'>There are currently no authors.</h1>";
} elseif (!isset($search) && $result->num_rows == 0 && isset($userRole) && $userRole == "member") {
    echo "<h1 class='text-center'>There are currently no members.</h1>";
} else {
?>
    <label class="inline-label">Choose an action:</label>
    <form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
        <div id="bulkOptionContainer" class="col-xs-12">
        <select class="form-control" name="bulk_options" id="bulkoptions">
            <option value="">Select Option</option>
            <!-- <option value="admin_role">Change Role to Admin</option> -->
            <option value="auth_role">Change Role to Author</option>
            <option value="delete_selected">Delete</option>
        </select>
        </div>
        <div class="apply-div col-xs-12">
            <button type="submit" name="submit" class="btn standard-btn" id="apply">Apply</button>
        </div>
    <div class="col-xs-12" style="margin-left: 0; padding-left: 0; margin-top: 15px; margin-bottom: 15px;">
        <a href="users.php?source=insert_user">Add New Admin or Author</a>        
    </div>
    <table class="table">
        <?php if (isset($search) && $search->num_rows > 0) { 
            if (isset($firstname) || isset($username)) {
                $srchTerm1 = isset($firstname) ? $firstname : $username;
            }
            if (isset($lastname) || isset($email)) {
                $srchTerm2 = isset($lastname) ? $lastname : $email;
            }                                
        ?>
        <div class="query-total">
            <p>
                Search: 
                <span><?php
                if (isset($firstname) && !empty($srchTerm1)) echo 'First name: '.'"'.$srchTerm1.'"';
                elseif (isset($username) && !empty($srchTerm1)) echo 'Username: '.'"'.$srchTerm1.'"';
                ?></span> 
                <?php if (!empty($srchTerm1) && !empty($srchTerm2)) echo " | ";  ?>
                <span><?php
                if (isset($lastname) && !empty($srchTerm2)) echo 'Last name: '.'"'.$srchTerm2.'"';
                elseif (isset($email) && !empty($srchTerm2)) echo 'Email: '.'"'.$srchTerm2.'"';
                ?></span>
                Results: 
                <span><?php echo $search->num_rows; ?></span>
            </p>
        </div>
        <?php } else { ?>
        <div class="query-total">
            <p>
                Total: 
                <span><?php 
                if (empty($searchError)) echo $result->num_rows; else echo "0";
                ?></span>
            </p>
        </div>
        <?php } ?>
        <thead>
            <tr>
            <th><input id="selectAllBoxes" type="checkbox"></th>
            <th>Id</th>
            <th>Username</th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Email</th>
            <th>Role</th>
            <?php 
            if ($currRole == "admin") {
                echo "<th>EDIT</th>
                     <th>DELETE</th>";
            }
            ?>
            </tr>
        </thead>
        <?php
        // If there is a search error, hide the table body. 
        // The while loop already prevents the table body from showing if there isn't
        // a matching result, but it doesn't prevent it from showing if an empty
        // form is submitted.
        if (empty($searchError)) {
        ?>
        <tbody>
            <?php
            // Display users in the table.
            while($row = $result->fetch_assoc()) {
                $user_id   = $row["user_id"];
                $username  = $row["username"];
                $password  = $row["password"];
                $firstname = $row["firstname"];
                $lastname  = $row["lastname"];
                $email     = $row["email"];
                $role      = $row["role"];                    
                echo "<tr>";
                if ($currRole == "admin" && $currUname != $username) {
                echo "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value='{$user_id}'></td>";
                } else {
                echo "<td><input class='checkBoxes disabled' type='checkbox' name='checkBoxArray[]' value='{$user_id}'></td>";    
                }
                echo "<td class='td-bold'>{$user_id}</td>";            
                echo "<td>{$username}</td>";
                echo "<td>{$firstname}</td>";
                echo "<td>$lastname</td>";
                echo "<td>$email</td>";
                echo "<td class='td-bold'>" . ucwords($role) . "</td>";
                if ($currRole == "admin") {
                echo "<td><a href='users.php?source=update_user&userid={$user_id}' class='btn gray-btn'>Edit</a></td>";
                }
                if ($currRole == "admin" && $currUname != $username) {
                echo "<td><a rel='{$user_id}' data-page='{$pageName}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                } else {
                echo "<td><a class='btn delete-btn delete disabled'>Delete</a></td>";   
                }                 
                echo "</tr>";
            }
            ?> 
        </tbody>
        <?php } ?>
    </table>
    </form>
</div>
<?php
}

// If "delete_item", the name of the submit input element in the delete modal (delete_modal.inc.php), is set.
if (isset($_POST["delete_item"])) {
    // Get the user_id value stored in the hidden input element.
    $del_id = $_POST["id"]; 
    // Use the user_id to delete the user.
    $deleteUser = $conn->query("DELETE from users WHERE user_id = {$del_id}");
    // Comment out to prevent the associated comment from being deleted when the user is deleted.
    $deleteComment = $conn->query("DELETE from comments WHERE user_id = {$del_id}");
    // header('Location: ' . $_SERVER['PHP_SELF']);
    header("Location: " . BASE_URL . THIS_PAGE); // users.php
    exit;

    // See the respective JS in custom.js (SINGLE ITEM DELETION).
}
?>