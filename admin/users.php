<?php
include "includes/admin_header.inc.php";
?>
<div id="wrapper">
    <?php 
    include "includes/admin_nav.inc.php"; 
    include "includes/page_header.inc.php"; 
    ?>
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">                    
                <?php
                if(isset($_GET["source"])){
                    $source = $_GET["source"];
                } else {
                    $source = "";
                }
                    
                switch($source) {
                    case "insert_user";
                    include "insert_user.php";                            
                    break;
                        
                    case "update_user";                            
                    include "update_user.php";                                
                    break;

                    case "update_admin";                            
                    include "update_user.php";                                
                    break;

                    case "update_auth";                            
                    include "update_user.php";                                
                    break;
                        
                    case "view_members";                            
                    include "view_members.php";                                
                    break;
                        
                    default:
                    include "view_users.php";                            
                    break;
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "includes/admin_footer.inc.php"; ?>