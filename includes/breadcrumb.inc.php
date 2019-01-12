<?php
// Get the name of the current page.
$pageName = basename(THIS_PAGE, ".php");
// If there is an underscore in the page's name, remove it.
$pageName = str_replace("_", " ", $pageName);
$bcTitle  = ""; // the breadcrumb title
?>
<div class="container">
    <div class="row">
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
        <li class="active <?php if($pageName == 'post'){echo 'postBc';} ?>">
            <em>
            <?php
            // If $postId from the post page is set, get the specific post by retrieving its post_id value through the query string.
            if(isset($postId)) {                
                $query  = "SELECT * FROM posts WHERE post_id = {$postId}";
                $result = $conn->query($query);
                confirmQuery($result);
                while($row = $result->fetch_assoc()) {
                    $bcTitle = strlen($row["title"]) > 20 ? substr($row["title"], 0, strrpos(substr($row["title"], 0, 20), " ")) . " ..." : $row["title"];
                    echo "<span title='" . $row["title"] . "'>" . $pageName . " : " . $bcTitle . "</span>";
                }
            // If on the category page, display the name of the category.
            } elseif ($pageName == "category") {
                echo $pageName . " : " . $category; // set in category.php
            // if on the author_posts page, display the name of the author.
            } elseif ($pageName == "author posts") {
                echo $pageName . " : " . $author; // set in author_posts.php
            // Otherwise, for any other page, just display the name of the page.
            } else {
                echo $pageName;
            }
            ?>
            </em>
        </li>
      </ol>
    </div>
</div>