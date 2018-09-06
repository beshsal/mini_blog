<?php
include "includes/html_head.inc.php";
include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

// Get all categories for Interests checklist
$getCategories = $conn->query("SELECT * FROM categories");
confirmQuery($getCategories);

$issues  = array();
$missing = array();
$suspect = false; // assume nothing is suspect

// Check if the form has been submitted
if (isset($_POST["send"])) {
  // Define the destination/receiver's address, e.g. web master's email
  $to      = "beshsaleh@gmail.com";
  $subject = "Message from MiniBlog";
    
  // List expected fields - the name attribute values
  $expected = array("name", "email", "comment", "interests");
    
  // Set required fields (attributes not listed here are optional)
  $required = array("name", "email", "comment");
    
  if (!isset($_POST["interests"])) {
    $_POST["interests"] = array();
  }
    
  // Create additional headers
  $headers  = "From: MiniBlog<" . $_SERVER['SERVER_NAME'] . ">\r\n";
  $headers .= "Content-Type: text/plain; charset=utf-8";
  // $headers .= "Content-Type: text/html; charset=UTF-8";
    
  // Require the processing script (executed only if the form is submitted)
  // require("includes/processmail.inc.php");
  require("includes/processmail_phpmailer.inc.php");
    
  // If $mailSent (from the processing script) is true, then go to the thank you page
  if ($mailSent) {
    // header("Location: " . BASE_URL . "thank_you.php?tempid=" . uniqid(true));
    header("Location: " . BASE_URL . "thank_you/" . uniqid(true));
	exit; //script terminated after page is redirected
  }
}

?>
<!-- PAGE CONTENT -->
<main class="page-content container"> 
  <section id="contact">
    <header class="section-heading">
      <h3>Get in Touch</h3>
    </header>
    <!-- 
    If $_POST array is set and a suspect phrase is found or there's a 'mailfail' error (code in processing script),
    display the appropriate error message
    -->
    <?php if (($_POST && $suspect) || ($_POST && isset($issues["mailfail"]))) { ?>
      <!-- Display an error message -->
      <h3 class="warning text-center">Sorry, your mail could not be sent. Please try later.</h3>
    <!-- Or if $missing or $issues is true (not empty) -->  
    <?php } elseif ($missing || $issues) { ?>
      <!-- Display an error message -->
       <h3 class="warning text-center">Please fix the item(s) indicated in red.</h3>
    <?php } ?>      
    <div class="row">              
      <form role="form" id="contact-form" class="contact-form" action="" method="post">
        <p>Leave a comment or ask a question:</p>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="name">Name:
              <!-- If the $missing array isn't empty and the name value is in the $missing array, display an error message -->
              <?php if ($missing && in_array("name", $missing)) { ?>
                <span class="warning">Please enter name</span>
              <?php } ?>                
              </label>
              <input type="text" class="form-control" name="name" autocomplete="off" id="name" placeholder="Enter name" 
              <?php
              // If a name value is missing or there is an error, preserve/persist data in input field
              if ($missing || $issues) {                 
                 echo 'value="' . htmlentities($name, ENT_COMPAT, 'UTF-8') . '"';
              } ?>>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="email">Email:
              <?php if ($missing && in_array("email", $missing)) { ?>
                  <span class="warning">Please enter email address</span>
              <!-- If an email is invalid (doesn't conform to the format in the processing script), warn the user -->
              <?php } elseif (isset($issues["email"])) { ?>
                <span class="warning">Invalid email address</span>
              <?php } ?>
              </label>
              <input type="email" class="form-control" name="email" autocomplete="off" id="email" placeholder="Enter email"
              <?php if ($missing || $issues) { 
                 echo 'value="' . htmlentities($email, ENT_COMPAT, 'UTF-8') . '"';
              } ?>>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="form-group">
              <label for="comment">Comment:
              <?php if ($missing && in_array("comment", $missing)) { ?>
                <span class="warning">Please enter comment</span>
              <?php } ?>
              </label>
              <textarea class="form-control textarea" rows="7" name="comment" id="comment" placeholder="Enter Message"><?php if($missing || $issues){echo htmlentities($comment, ENT_COMPAT, 'UTF-8');} ?></textarea>
            </div>
            <div class="form-group">
              <label for="selectInterests" class="selectInterests">Select Interests:</label> <span>(Optional)</span>
              <div class="row">                      
                  <div id="interests">
                  <?php while($row = $getCategories->fetch_assoc()) {
                    $category = $row["category"];
                    if ($category != "Miscellaneous") { ?>
                    <div class="col-xs-6 col-sm-4 col-md-3">
                      <div class="checkbox">
                        <label>
                        <input name="interests[]" type="checkbox" value="<?php echo $category; ?>" id="<?php echo $category; ?>" <?php
                        if ($_POST && in_array($category, $_POST['interests'])) {
                        echo "checked";
                        } ?>><?php echo $category; ?>
                        </label>
                      </div>
                    </div>
                    <?php } } ?>
                  </div>
              </div>
              <hr>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <button name="send" type="submit" class="btn standard-btn">Send</button>
          </div>
        </div>
      </form>
    </div>
  </section>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>