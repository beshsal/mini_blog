<?php
include "includes/html_head.inc.php";

// If the user is signed in or a token value is not in the URL query string, 
// redirect the user to the home page.
if (isset($_SESSION["authenticated"]) || !isset($_GET["token"])) {    
    header("Location: " . BASE_URL);
    exit;
}

require "./vendor/autoload.php"; // loads ValidatePwd class

// Use the token value passed in through the URL query string to select the exact current user's
// username, email, and token from the database.
if ($stmt = $conn->prepare("SELECT username, email, token FROM users WHERE token = ?")) {
    $stmt->bind_param("s", $_GET["token"]);
    $stmt->execute();
    $stmt->bind_result($username, $email, $token);
    $stmt->fetch();
    $stmt->close();    
}

// If a POST request is made and it contains the value of the input submit field
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["reset_pwd"])) {
    // Create an array for holding errors.
    $pwdErrors = array(); 
    
    // If the required password fields are submitted, save their values.
    if (isset($_POST["pwd"]) && isset($_POST["conf_pwd"])) {
        $password   = trim($_POST['pwd']);
        $confirmPwd = trim($_POST["conf_pwd"]);
    } else {
        $pwdErrors[] = "Fields must not be empty.";
    }
    
    if ($password != $confirmPwd) {
        $pwdErrors[] = "Your passwords do not match.";
    }
    
    // Validate the password.
    $validatePwd = new ValidatePwd($password, 10);
    $validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
    $validatePwd->requireNumbers(2); // check that at least 2 numbers are used
    $passwordOK = $validatePwd->check();
    
    // If the password does not pass validation, merge any errors in the $pwdErrors array with the validation
    // errors.
    if (!$passwordOK) {
        $pwdErrors = array_merge($pwdErrors, $validatePwd->getErrors());
    }
    // If $pwdErrors doesn't contain values, there are no errors, so update the password for the respective record
    // in the users table.
    if (!$pwdErrors) {   
        $salt = time();
        $pwd = sha1($password . $salt);
        // (Note: removing the token because it is not needed after the update)
        if (!($stmt = $conn->prepare("UPDATE users SET token='', password= ?, salt = ? WHERE email = ?"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        
        if (!($stmt->bind_param("sis", $pwd, $salt, $_GET["email"]))) { // can also use $email
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        
        if (!($stmt->execute())) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        if($stmt->affected_rows == 1) {
            // If the password was successfully reset, set a flag on the session.
            $_SESSION["pwdreset"] = true;
            // Refresh the page.
            header("Location:" . $_SERVER["PHP_SELF"] . "?username=" . $username . "&email=" . $email . "&token=" . $token. "&pwdreset=true");
            exit;          
        } else {
            $pwdErrors[] = "Sorry, there was a problem with the database.";
        }

        $stmt->close();
    }
}

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";
?>
<main class="page-content container">
    <section id="forgot-pwd">
        <header class="section-heading">
        <h2 style="margin-bottom: 10px;">
        <?php if (isset($_SESSION["pwdreset"])) {
            echo "Your Password was Reset";
        } else {
            echo "Reset Your Password";
        } ?>
        </h2>
        </header>
        <div>
        <?php
        // If there are errors, display them.
        if (isset($pwdErrors)) {
            if (count($pwdErrors) > 1) {
                echo "<ul class='error' style='margin-bottom:15px;'>";
                foreach ($pwdErrors as $err) {
                   echo "<li><h3>$err</h3></li>";
                }
                echo "</ul>";
            } elseif (count($pwdErrors) == 1) {
                echo "<h3 class='text-center error' style='margin-bottom:15px;'>$pwdErrors[0]</h3>";
            }    
        }
        ?>
        </div>
        <!-- If $_SESSION["pwdreset"] is set, indicating the password has been successfully reset, display the sign-in form; 
        otherwise, display the form that resets the password. -->
        <?php if (isset($_SESSION["pwdreset"]) && $_SESSION["pwdreset"] == true) {
        ?>
        <div class="row">
            <h4 class="text-center">Sign in with your new password:</h4>
            <form action="" method="post">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input name="uname" value="<?php if(isset($_GET['username'])) echo $_GET['username']; ?>" type="text" class="form-control" id="uname" placeholder="Enter Username" required>
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password" required>
                </div>
                <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
            </form>
        </div>
        <?php } else { ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="text-center">
                    <h3><i class="fa fa-lock fa-4x"></i></h3>
                    <h2 class="text-center">Password Reset</h2>
                    <div class="panel-body">
                        <form id="register-form" role="form" autocomplete="off" class="form" method="post">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input id="password" name="pwd" placeholder="Enter new password" class="form-control"  type="password">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                    <input id="confirmPassword" name="conf_pwd" placeholder="Confirm new password" class="form-control"  type="password">
                                </div>
                            </div>
                            <div class="form-group">
                                <input name="reset_pwd" class="btn standard-btn" value="Reset Password" type="submit">
                            </div>
                            <input type="hidden" class="hide" name="token" id="token" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </section>
</main>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>