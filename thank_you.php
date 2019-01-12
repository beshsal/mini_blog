<?php 
include "includes/html_head.inc.php";

// A temporary ID is only used to make it harder for a viewer to access the thank you page through the URL.
// If the URL does not contain a "tempid" value, the viewer is redirected to the home page.
if (!isset($_GET["tempid"])) {
    header("Location: " . BASE_URL);
    exit;
}

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";
?>
<main class="page-content container">
    <section id="thank-you">
    <header class="section-heading">
    <h2 style="margin-bottom: 10px;">Thank You</h2>
    <p class="text-left">
    ( Your message has been sent. We appreciate your feedback and will be in touch if necessary. We hope you'll continue to enjoy browsing
    <?php
    // The message displays the logo text stored in the LOGO_UNSTYLED constant.
    echo "<strong>" . LOGO_UNSTYLED . "</strong>";
    ?>. )
    </p>
    </header>
    </section>
</main>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>
