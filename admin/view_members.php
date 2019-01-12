<?php
// Get the current page name.
$pageName = basename(THIS_PAGE, ".php");

if (isset($_GET["online"])) {
?>    
    <table class="table olMembersRows"></table>
<?php
} else {
    $currRole = $_SESSION["role"];
    
    // Initialize a variable to hold search errors.
    $searchError = "";

    // Select only members for this page.
    $query  = "SELECT * FROM users WHERE role = 'member'";
    $result = $conn->query($query);
    confirmQuery($result);
    
    // Search users by first name and/or last name.
    if (isset($_POST["search_members1"])) {
        $firstname  = trim($_POST["by_firstname"]);
        $lastname   = trim($_POST["by_lastname"]);
        $firstname  = $conn->real_escape_string($firstname);
        $lastname   = $conn->real_escape_string($lastname);
        $srchParams = "?source=view_members&search=members";

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
    if (isset($_GET["search"]) && $_GET["search"] != "members2") {
        $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
        $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";

        // If both fields are filled
        if (!empty($search1) || !empty($search2)) {
            $searchMem = "SELECT * FROM users 
                         WHERE firstname LIKE '%" . $search1 . "%'
                         AND lastname LIKE '%" . $search2 . "%'
                         AND role='member'";
        } 

        // If empty fields are submitted, add an error message to $searchError.
        if (empty($searchMem)) {        
            $searchError = "Both fields cannot be empty! Please enter a search term in at least one field."; 
        // Otherwise, run the query.       
        } else {        
            $result = $conn->query($searchMem);
            confirmQuery($result);
            $search = $result;
            // if no results are found, add an error message to $searchError.
            if ($search->num_rows == 0) {
                $searchError = "There are no members who match the search term(s) you entered. 
                               <br>The search term(s) may be misspelled or the record may not exist.";
            }
        }
    }
    
    if (isset($_POST["search_members2"])) {
        $username   = trim($_POST["by_username"]);
        $email      = trim($_POST["by_email"]); 
        $username   = $conn->real_escape_string($username);
        $email      = $conn->real_escape_string($email);
        $srchParams = "?source=view_members&search=members2";

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
    if (isset($_GET["search"]) && $_GET["search"] == "members2") {
        $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
        $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";

        // If at least one of the fields is filled (not empty)
        if (!empty($search1) || !empty($search2)) {
            $searchMem = "SELECT * FROM users 
                         WHERE username LIKE '%" . $search1 . "%' 
                         AND email LIKE '%" . $search2 . "%'
                         AND role='member'";
        } 

        // If empty fields are submitted, add an error message to $searchError.   
        if (empty($searchMem)) {        
            $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
        // Otherwise, run the query.        
        } else {        
            $result = $conn->query($searchMem);
            confirmQuery($result);
            $search = $result;
            // if no results are found, add an error message to $searchError.
            if ($search->num_rows == 0) {
                $searchError = "There are no members who match the search term(s) you entered. 
                               <br>The search term(s) may be misspelled or the record may not exist.";
            }
        }    
    }
?>

<nav class="navbar navbar-default search-sort-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php?source=view_members">View all <span class="sr-only">(current)</span></a></li>
      </ul>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <i class="fa fa-search"></i>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="users.php?source=view_members">View all <span class="sr-only">(current)</span></a></li>
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
              <button type="submit" name="search_members1" class="btn gray-btn">Search</button>
          </div>         
          <div id="fieldset2" style="display:none">
              <label class="search-label">Search for users by username and/or email:</label>
              <div class="form-group">
                <input type="search" class="form-control" id="uname" placeholder="Search by username" name="by_username" value="<?php if (isset($username) && $searchError != "") { echo $username; } ?>">
              </div>
              <div class="form-group">
                <input type="search" class="form-control" id="email" placeholder="Search by email" name="by_email" value="<?php if (isset($email) && $searchError != "") { echo $email; } ?>">
              </div>
              <button type="submit" name="search_members2" class="btn gray-btn">Search</button>
          </div>
          <div class="form-group switchFields">
            <label class="switchFields-label">
                Search by username and/or email<input name="switchfieldset" class="switchfieldset" type="checkbox" style="margin-left: 4px;" 
                <?php
                if(isset($_GET["search"]) && $_GET["search"] == "members2") echo "checked='checked'";
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
    // If there are no members, inform the user. Otherwise, display the data in a table. Note the links for editing and deleting
    // members are displayed for only admin.
    if(!isset($search) && $result->num_rows == 0){echo "<h1 class='text-center'>There are currently no members.</h1>";}else{ ?>
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
            <th>Id</th>
            <th>Username</th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Email</th>
            <th>Role</th>
            <?php 
            if ($currRole == "admin") {
                echo "<th>DELETE</th>";
            }
            ?>
            </tr>
        </thead>
        <?php if (empty($searchError)) { ?>
        <tbody>        
        <?php
        while ($row = $result->fetch_assoc()) {
            $user_id   = $row["user_id"];
            $username  = $row["username"];
            $password  = $row["password"];
            $firstname = $row["firstname"];
            $lastname  = $row["lastname"];
            $email     = $row["email"];
            $role      = $row["role"];
            echo "<tr>";
            echo "<td class='td-bold'>{$user_id}</td>";            
            echo "<td>{$username}</td>";
            echo "<td>{$firstname}</td>";
            echo "<td>$lastname</td>";
            echo "<td>$email</td>";
            echo "<td class='td-bold'>" . ucwords($role) . "</td>";

            if ($currRole == "admin") {
            echo "<td><a rel='{$user_id}' data-page='{$pageName}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
            }
            echo "</tr>";            
        }
        ?>
        </tbody>
        <?php } ?>
    </table>
</div>
<?php }} ?>