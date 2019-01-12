<?php 
include "includes/html_head.inc.php";

// Import the PHPMailer script.
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If the current user is signed in, the user is redirected to the home page.
if (isset($_SESSION["authenticated"])) {
    header("Location: " . BASE_URL);
    exit;
}

// A temporary ID is only used to make it harder for a viewer to access the forgot_password page through the URL.
// If the URL does not contain a "tempid" value, the viewer is redirected to the home page.
if (!isset($_GET["tempid"])) {
    header("Location: " . BASE_URL);
    exit;
}

// If the request for a new password is successfully sent to the user's email, "emailsent=true" is passed through the URL.
if (isset($_GET["emailsent"])) {
    $emailSent = $_GET["emailsent"];
}

// If a POST request is made and it contains the value of the input submit field, check if an email is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_reset_link"])) {
    
    // If the variable that is set on the session if the user's
    // password has been successfully changed is set, unset it.
    if (isset($_SESSION["pwdreset"])) {
        unset($_SESSION["pwdreset"]);   
    }
    
    // If an email is sent, store its value in a variable, and set a token variable, which will be used in the GET request
    // for resetting the password.
    if(isset($_POST["email"])) {
        $email  = trim($_POST["email"]);
        $length = 50;
        $token  = bin2hex(openssl_random_pseudo_bytes($length));
        
        // Check if the email value sent from the form matches an email value for a record in the users table.
        if ($stmt = $conn->prepare("SELECT email FROM users WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
        }
        
        // If there is a matching email in the users table, update the record's token field with a new token value,
        // and send a link to the reset_password page containing the token to the user's email. 
        // (Note: The reset_password page will use this token to ensure that the exact record with the password to be reset
        // is selected from the users table.)
        if($stmt->num_rows > 0) {
            if($stmt = $conn->prepare("UPDATE users SET token='{$token}' WHERE email= ?")) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();
                
                // PHPMailer configuration
                
                try {
                    // Server settings
                    $mail = new PHPMailer();                
                    // $mail->SMTPDebug = 2; // enable verbose debug output
                    $mail->isSMTP(); // set mailer to use SMTP
                    $mail->Host = Config::SMTP_HOST;
                    $mail->Username = Config::SMTP_USER;
                    $mail->Password = Config::SMTP_PASSWORD;
                    $mail->Port = Config::SMTP_PORT;
                    $mail->SMTPSecure = 'tls'; // enable TLS encryption, `ssl` also accepted
                    $mail->SMTPAuth = true; // enable SMTP authentication
                    $mail->isHTML(true); // set email format to HTML
                    $mail->CharSet = 'UTF-8';

                    // Recipients
                    $mail->setFrom('beshsaleh@gmail.com', 'MiniBlog');
                    $mail->addAddress($email); // the user's email is added as a recipient
                    $mail->addReplyTo('no-reply@' . $_SERVER['SERVER_NAME'], 'Please do not reply to this email.');
                    $mail->Subject = 'Password Reset';
                    $mail->Body = 
                    '<p>Please click the link to reset your password:
                    <a href="'.BASE_URL.'reset_password.php?email='.$email.'&token='.$token.'">'.BASE_URL.'reset_password.php?email='.$email. '&token='.$token.'</a></p>';
                    
                    // If the mail is sent, redirect to this page with a URL query string.
                    if ($mail->send()) {
                        header("Location:" . $_SERVER["PHP_SELF"] . "?tempid=" . $_GET["tempid"] . "&emailsent=true");
                        exit;
                    }
                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }
            }
        } else { 
            // If a matching email is not found in the users table, an error message is set.
            $emailErr = 
            "<h3 class='warning' style='padding: 15px;'>Sorry. We can't find your email in our database. 
            Make sure you are entering the correct email adress.
            </h3>"; 
        }
    }
}

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";
?>

<main class="page-content container">
    <section id="forgot-pwd">
    <header class="section-heading">
    <h2 style="margin-bottom: 10px;">Forgot your Password?</h2>
    <p>( No problem. You can reset your password here. )</p>
    </header>
    <!-- If $emailSent is not set (the mail was not successfully sent), the respective error message along
    with the form are displayed. Otherwise, a message informing the user that the email was successfully sent 
    is displayed. -->
    <?php if (!isset($emailSent)): ?>
        <?php if(isset($emailErr)){echo $emailErr;} ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="text-center">
                    <h3><i class="fa fa-lock fa-4x"></i></h3>
                    <h2 class="text-center">Password Reset</h2>
                    <div class="panel-body">
                        <form id="reset-pwd-form" role="form" autocomplete="off" method="post" onsubmit="showLoader()">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                    <input id="email" name="email" placeholder="email address" class="form-control"  type="email">
                                </div>
                            </div>
                            <div class="form-group">
                                <input name="send_reset_link" class="btn standard-btn" value="Send Reset Link" type="submit">
                            </div>
                            <input type="hidden" class="hide" name="token" id="token" value="">
                        </form>
                    </div><!-- /.panel-body -->                        
                </div>
            </div>
        </div>        
    <?php else:
        echo "<h3>The link to reset your password has been sent to your email. Please check your email.</h3>";
        endif; 
    ?>     
    </section>
</main>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>
