<?php
include "includes/admin_header.inc.php";

// Select all categories. This is used below to inform the user if there are no categories yet.
$checkCats = $conn->query("SELECT category FROM categories");

if (isset($_POST["insert_category"])) {
    $category = $_POST["category"];    
    // Check if there is already a category of the same name in the table.  
    $checkField = $conn->query("SELECT category FROM categories WHERE category = '{$category}'");
    // If the category field is empty when the form is submitted or the category already exists in the table, inform the user.
    // Otherwise, insert a new record for the category in the table.
    if (!isset($category) || empty($category)) {
        $catError = "<p class='error'>Please enter a category.</p>";
    } elseif ($checkField->num_rows > 0) {    
        $catError = "<p class='error'>The category {$category} already exists.</p>";    
    } else {
        if (!($stmt = $conn->prepare("INSERT INTO categories(category) VALUES(?)"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("s", $category)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();        
        header("Location: " . BASE_URL . THIS_PAGE);
        exit;
    }        
}
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
                    <div class="col-sm-5">                        
                        <form action="" method="post">
                          <div class="form-group">
                             <label for="category">Add category:</label>
                              <!-- Display any errors. -->
                              <?php if(isset($catError)){echo $catError;} ?>
                              <input name="category" type="text" class="form-control">
                          </div>
                           <div class="form-group">
                              <input name="insert_category" class="btn standard-btn" type="submit" value="Add Category">
                          </div>
                        </form>                        
                        <?php
                        // If the user chooses to update a category, a GET request is used to trigger including the update
                        // form.
                        if (isset($_GET["update_cat"])) {
                            // Get the cat_id of the current category from the URL query string (?update_cat={$cat_id})
                            // and store it in a variable.
                            $cat_id = $_GET["update_cat"];
                            // Include the update form; this way the input field will not display until the Edit link 
                            // is clicked.                            
                            include "includes/update_category.inc.php";
                        }                        
                        ?>                        
                    </div>
                    <?php if($checkCats->num_rows == 0){echo "<h3 class='text-center'>There are currently no categories.</h3>";}else{ ?>
                    <div class="col-sm-7" 
                    <?php if ($checkCats->num_rows > 10) {
                        echo "style='height: 560px; overflow-y: auto; overflow-x: hidden;'";
                    } ?>>                    
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>                            
                            <tbody>
                            <?php 
                            // Find all categories, and display their data in a table. Note the links for editing and deleting 
                            // a category.
                            $query      = "SELECT * FROM categories ORDER BY cat_id ASC";    
                            $categories = $conn->query($query);    
                            while ($row = $categories->fetch_assoc()) {                                
                                $cat_id   = $row["cat_id"];
                                $category = $row["category"];
                                echo "<tr>";
                                echo "<td class='td-bold'>{$cat_id}</td>";
                                echo "<td>{$category}</td>";
                                echo "<td><a href='admin_categories.php?update_cat={$cat_id}' class='btn gray-btn'>Edit</a></td>";
                                echo "<td><a rel='{$cat_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                                echo "</tr>";
                            }
                            if (isset($_POST["delete_item"])) {
                                $del_id = $_POST["id"];
                                $deleteCat = $conn->query("DELETE from categories WHERE cat_id = {$del_id}");
                                header("Location: admin_categories.php"); // refresh the page
                                exit;
                            }
                            ?>
                            </tbody>
                            <?php } ?>
                        </table>                    
                    </div>                    
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->

<?php include "includes/admin_footer.inc.php"; ?>