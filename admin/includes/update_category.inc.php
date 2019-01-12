<?php
// When the Edit link is clicked, an "update_cat" parameter with the cat_id value is sent through the URL query string.
if (isset($_GET["update_cat"])) {
    // Make sure the value is numeric.
    if (!is_numeric($_GET["update_cat"])) {
        header("Location: " . BASE_URL);
        exit;
    } else {
        // Use the cat_id to select the category to be updated.
        $cat_id = (int) $_GET["update_cat"];
        $query  = "SELECT * FROM categories WHERE cat_id = {$cat_id}";    
        $result = $conn->query($query);
        confirmQuery($result);
    }
}

// UPDATE CATEGORY
if (isset($_POST["update_category"])) {
    $category = $_POST["category"];

    // If category field is left blank, add an error message to $catError.
    if (!isset($category) || empty($category)) {
        $catError = "<p class='error'>This field should not be empty</p>";
    // Otherwise update the category.
    } else {
        if (!($stmt = $conn->prepare("UPDATE categories SET category = '{$category}' WHERE cat_id = {$cat_id}"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("s", $category)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();
        header("Location: admin_categories.php");
        exit;
    }
}
?>

<form action="" method="post">
  <div class="form-group">
     <hr>
     <span style="float: right; padding-right: 5px;"><a style="color: #000;" href="admin_categories.php">X</a></span>
     <label for="cat-title">Update category:</label>
    <?php
    while ($row = $result->fetch_assoc()) {
        $cat_id  = $row["cat_id"];
        $catName = $row["category"];
        
        if(isset($catError)){echo $catError;} ?>
        <input name="category" value="<?php if(isset($catName)){echo $catName;} ?>" type="text" class="form-control">
    <?php } ?>
  </div>
  <div class="form-group">
      <input class="btn standard-btn" type="submit" name="update_category" value="Update Category">
  </div>
</form>