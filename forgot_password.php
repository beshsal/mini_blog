<?php 
include "includes/html_head.inc.php";
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION["authenticated"])) {
    header("Location: " . BASE_URL);
    exit;
}

if (!isset($_GET["tempid"])) {
    header("Location: " . BASE_URL);
    exit;
}

if (isset($_GET["emailsent"])) {
    $emailSent = $_GET["emailsent"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_reset_link"])) {
    if(isset($_POST["email"])) {
        $email  = trim($_POST["email"]);
        $length = 50;
        $token  = bin2hex(openssl_random_pseudo_bytes($length));
        
        if ($stmt = $conn->prepare("SELECT email FROM users WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
        }
        
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

                    // Recepients
                    $mail->setFrom('beshsaleh@gmail.com', 'MiniBlog');
                    $mail->addAddress($email); // add a recipient
                    $mail->addReplyTo('no-reply@' . $_SERVER['SERVER_NAME'], 'Please do not reply to this email.');
                    $mail->Subject = 'Password Reset';
                    $mail->Body = 
                    '<p>Please click the link to reset your password:
                    <a href="'.BASE_URL.'reset_password.php?email='.$email.'&token='.$token.'">'.BASE_URL.'reset_password.php?email='.$email. '&token='.$token.'</a></p>';
                    
                    // $emailSent = "The link to reset your password has been sent to your email. Please check your email.";
                    // $mail->send();
                    
                    if ($mail->send()) {
                        header("Location:" . $_SERVER["PHP_SELF"] . "?tempid=" . $_GET["tempid"] . "&emailsent=true");
                        exit;
                    }
                    
                    // echo 'Message has been sent';
                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }
            }
        } else { 
            $emailErr = 
            "<h3 class='warning' style='padding: 15px;'><strong>Sorry. We can't find your email in our database. 
            Make sure you are entering the correct email adress</strong>.
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
    <?php if (!isset($emailSent)): ?>
        <?php if(isset($emailErr)){echo $emailErr;} ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="text-center">
                    <h3><i class="fa fa-lock fa-4x"></i></h3>
                    <h2 class="text-center">Password Reset</h2>
                    <div class="panel-body">
                        <form id="reset-pwd-form" role="form" autocomplete="off" method="post">
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
