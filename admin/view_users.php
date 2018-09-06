<?php
$pageName = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$currRole = $_SESSION["role"];

if ($currRole != "admin") {
    header("Location: " . BASE_URL);
    exit;
}

$currUname   = $_SESSION["username"];
$currUid     = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row         = $currUid->fetch_array();
$uid         = $row["user_id"];
$searchError = "";
$query = "SELECT * FROM users";
$result = $conn->query($query);
confirmQuery($result);

if (isset($_GET["role"])) {    
    $userRole = $_GET["role"];
    
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

if(isset($_POST["search_users"])) {
    $firstname = '%' . $conn->real_escape_string($_POST["by_firstname"]) . '%';
    $lastname  = '%' . $conn->real_escape_string($_POST["by_lastname"]) . '%';
    
    If (!empty($firstname) && !empty($lastname)) {        
        $searchUser = "SELECT * FROM users WHERE firstname LIKE '$firstname' AND lastname LIKE '$lastname'";        
    } elseif (empty($firstname) && !empty($lastname)) {        
        $searchUser = "SELECT * FROM users WHERE lastname LIKE '$lastname'";        
    } elseif (!empty($firstname) && empty($lastname)) {        
        $searchUser = "SELECT * FROM users WHERE firstname LIKE '$firstname'";    
    } 
    // Check if both fields are empty (query is empty)
    if (empty($searchUser)) {        
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";        
    } else {
        // Check if the query was successful
        $result = $conn->query($searchUser);
        confirmQuery($result);
        
        if ($result->num_rows == 0) {
            $searchError = "No results found! The search term(s) may be mispelled or the record may not exist.";
        }
    } 
} elseif (isset($_POST['search_users2'])) {    
    $username = '%' . $conn->real_escape_string($_POST['by_username']) . '%';
    $email    = '%' . $conn->real_escape_string($_POST['by_email']) . '%';
    
    if (!empty($username) && !empty($email)) {        
        $searchUser = "SELECT * FROM users WHERE username LIKE '$username' AND email LIKE '$email'";        
    } elseif (empty($username) && !empty($email)) {        
        $searchUser = "SELECT * FROM users WHERE email LIKE '$email'";        
    } elseif (!empty($username) && empty($email)) {        
        $searchUser = "SELECT * FROM users WHERE username LIKE '$username'";    
    }    
    if (empty($searchUser)) {        
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";        
    } else {        
        $result = $conn->query($searchUser);
        confirmQuery($result);
        $search = $result;
        
        if ($search->num_rows == 0) {
            $searchError = "No results found! The search term(s) may be mispelled or a matching user record may not exist.";
        }
    }    
}

if(isset($_POST["checkBoxArray"])) {
    foreach ($_POST["checkBoxArray"] as $userValueId) {
        $bulk_options = $_POST["bulk_options"];
        
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
    
    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>
<nav class="navbar navbar-default search-sort-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php">View All <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="users.php?role=admin">Admin</a></li>
            <li><a href="users.php?role=author">Author</a></li>
            <li><a href="users.php?role=member">Member</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="users.php">View all</a></li>
          </ul>
        </li>
      </ul>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <i class="fa fa-search"></i>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php">View All <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="users.php?role=admin">Admin</a></li>
            <li><a href="users.php?role=author">Author</a></li>
            <li><a href="users.php?role=member">Member</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="users.php">View all</a></li>
          </ul>
        </li>
      </ul>        
<?php
// If the sort result has no value, a respective message is placed under the sort navigation
if (!isset($search) && $result->num_rows == 0 && isset($userRole) && $userRole == "author") {
    echo "<h1 class='text-center'>There are currently no authors.</h1>";
} elseif (!isset($search) && $result->num_rows == 0 && isset($userRole) && $userRole == "member") {
    echo "<h1 class='text-center'>There are currently no members.</h1>";
} else {
?>
      <ul id="search-wrapper" class="nav navbar-nav navbar-right">
        <?php if (isset($searchError) && $searchError != "") {
        echo "<p class='error'>{$searchError}</p>";
        } ?>
        <form id="searchUserForm1" action="" method="post" class="navbar-form navbar-left show" role="search">
          <div class="form-group">
            <input type="text" class="form-control" id="fname" placeholder="Search by firstname" name="by_firstname" value="<?php if (isset($firstname) && $searchError != "") { echo $firstname; } ?>">
          </div>
          <div class="form-group">
            <input type="text" class="form-control" id="lname" placeholder="Search by lastname" name="by_lastname" value="<?php if (isset($lastname) && $searchError != "") { echo $lastname; } ?>">
          </div>
          <button type="submit" name="search_users" class="btn gray-btn">Search</button>
        </form>          
        <form id="searchUserForm2" action="" method="post" class="navbar-form navbar-left" role="search" style="display:none;">
          <div class="form-group">
            <input type="text" class="form-control" id="uname" placeholder="Search by username" name="by_username" value="<?php if (isset($username) && $searchError != "") { echo $username; } ?>">
          </div>
          <div class="form-group">
            <input type="email" class="form-control" id="email" placeholder="Search by email" name="by_email" value="<?php if (isset($email) && $searchError != "") { echo $email; } ?>">
          </div>
          <button type="submit" name="search_users2" class="btn gray-btn">Search</button>
        </form>
        <div class="switchFields">
        <a id="switchFields" style="display: inline-block;">Search by username and/or email</a>
        </div>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="view-content col-xs-12" style="margin-top: 50px;">
    <label class="inline-label">Choose an action:</label>
    <form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
        <div id="bulkOptionContainer" class="col-xs-12">
        <select class="form-control" name="bulk_options" id="bulkoptions">
            <option value="">Select Option</option>
            <option value="admin_role">Make Admin</option>
            <option value="auth_role">Make Author</option>
            <option value="delete_selected">Delete</option>
        </select>
        </div>
        <div class="apply-div col-xs-12">
            <button type="submit" name="submit" class="btn standard-btn" id="apply">Apply</button>
        </div>
    </form>
    <div class="col-xs-12" style="margin-left: 0; padding-left: 0; margin-top: 15px; margin-bottom: 15px;">
        <a href="users.php?source=insert_user">Add New Admin or Author</a>        
    </div>
    <table class="table">
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
        <tbody>
            <?php
            // Display users in the table
            while($row = $result->fetch_assoc()) {
                $user_id   = $row["user_id"];
                $username  = $row["username"];
                $password  = $row["password"];
                $firstname = $row["firstname"];
                $lastname  = $row["lastname"];
                $email     = $row["email"];
                $role      = $row["role"];                    
                echo "<tr>";
                echo "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value='{$user_id}'></td>";
                echo "<td class='td-bold'>{$user_id}</td>";            
                echo "<td>{$username}</td>";
                echo "<td>{$firstname}</td>";
                echo "<td>$lastname</td>";
                echo "<td>$email</td>";
                echo "<td class='td-bold'>" . ucwords($role) . "</td>";
                if ($currRole == "admin") {
                echo "<td><a href='users.php?source=update_user&user={$user_id}' class='btn gray-btn'>Edit</a></td>";
                echo "<td><a rel='{$user_id}' data-page='{$pageName}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                }                    
                echo "</tr>";
            }
            ?> 
        </tbody>
    </table>
</div>
<?php
}

if(isset($_POST['delete_item'])) {
    $del_id = $_POST['id'];    
    $deleteUser    = $conn->query("DELETE from users WHERE user_id = {$del_id}");
    // Comment out to prevent the associated comment from being deleted when the user is deleted
    $deleteComment = $conn->query("DELETE from comments WHERE user_id = {$del_id}");
    header('Location: '.$_SERVER['PHP_SELF']);
    die;
}
?>