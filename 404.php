<?php 
include "includes/html_head.inc.php";

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";
?>
<main class="page-content container">
    <section id="page-not-found">
        <header class="section-heading">
            <h2 style="margin-bottom: 10px;">Sorry. Can't find that page.</h2>
            <p>Perhaps you are looking for one of these pages instead:</p>
        </header>
<!--        <ul class="list-unstyled text-center">-->
        <ul class="list-unstyled">
            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
            <li><a href="categories">Our Categories Page</a></li>
            <li><a href="https://beshsaleh.com/project_details">MiniBlog Project Details</a></li>
            <li><a href="contact">Our Contact Page</a></li>          
            <li><a href="#search">Search</a></li>
        </ul>
    </section>
</main>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>
