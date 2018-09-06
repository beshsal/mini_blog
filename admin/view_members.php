<?php
$pageName = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$currRole = $_SESSION["role"];
$query    = "SELECT * FROM users WHERE role = 'member'";
$result   = $conn->query($query);
confirmQuery($result);

if(isset($result) && $result->num_rows == 0){echo "<h1 class='text-center'>There are currently no members</h1>";}else{ ?>
<table class="table">
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
    <tbody>        
    <?php
    // Display member users in the table
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
</table>
<?php } ?>