<?php
include "includes/html_head.inc.php";

if (isset($_SESSION["authenticated"])) {
    header("Location: " . BASE_URL);
    exit;
}

if(!isset($_GET["email"])) {
    header("Location: " . BASE_URL);
    exit;
}

if (isset($_GET["pwdreset"])) {
    $pwdReset = $_GET["pwdreset"];
    exit;
}

require "./vendor/autoload.php"; // loads ValidatePwd class

// Use the token value passed in through the URL query string to select the current user's
// username, email, and token from the database
if ($stmt = $conn->prepare("SELECT username, email, token FROM users WHERE token = ?")) {
    $stmt->bind_param("s", $_GET["token"]);
    $stmt->execute();
    $stmt->bind_result($username, $email, $token);
    $stmt->fetch();
    $stmt->close();    
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["reset_pwd"])) {
    $errors = array(); // create an array for holding errors
    
    if (isset($_POST["pwd"]) && isset($_POST["conf_pwd"])) {
        $password   = trim($_POST['pwd']);
        $confirmPwd = trim($_POST["conf_pwd"]);        
    } else {
        $errors[] = "Fields must not be empty";
    }
    
    if ($password != $confirmPwd) {
        $errors[] = "Your passwords do not match.";
    }
    
    // Validate the password
    $validatePwd = new ValidatePwd($password, 10);
    $validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
    $validatePwd->requireNumbers(2); // check that at least 2 numbers are used
    $passwordOK = $validatePwd->check();

    if (!$passwordOK) { 
      $errors = array_merge($errors, $validatePwd->getErrors());
    }

    if (!$errors) {   
        $salt = time();
        $pwd = sha1($password . $salt);
        // Note: removing the token because it is not needed after the update
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
            header("Location:" . $_SERVER["PHP_SELF"] . "?email=" . $_GET["email"] . "&pwdreset=true");
            exit;
        } else {
            $errors[] = "Sorry, there was a problem with the database.";
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
    <?php if (isset($pwdReset)) {
        echo "Your Password was Reset";
    } else {
        echo "Reset Your Password";
    } ?>
    </h2>
    <!-- <p>( No problem. You can reset your password here. )</p> -->
    </header>
        <?php if (isset($pwdReset)) { ?>
        <div class="row">
            <h4 class="text-center">Sign in with your new password:</h4>
            <form action="" method="post">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username">
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
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
                                    <input id="password" name="pwd" placeholder="Enter password" class="form-control"  type="password">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                    <input id="confirmPassword" name="conf_pwd" placeholder="Confirm password" class="form-control"  type="password">
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