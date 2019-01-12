<?php include "includes/admin_header.inc.php"; ?>
<div id="wrapper">
    <?php 
    include "includes/admin_nav.inc.php"; 
    include "includes/page_header.inc.php"; 
    ?>    
    <div id="page-wrapper">
        <div class="container-fluid">            
            <div class="row">
                <div class="col-lg-12">
                <!-- Get the appropriate page template specified by a GET request. -->
                <?php
                if(isset($_GET["source"])){
                    $source = $_GET["source"];
                } else {
                    $source = "";
                }
                    
                // Note when posts.php is accessed without parameters, the view_posts page inserted by default.    
                switch($source) {
                    case "insert_post";
                    include "insert_post.php";                            
                    break;

                    case "update_post";                            
                    include "update_post.php";                                
                    break;

                    default:
                    include "view_posts.php";                            
                    break;
                }
                ?> 
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/admin_footer.inc.php"; ?>