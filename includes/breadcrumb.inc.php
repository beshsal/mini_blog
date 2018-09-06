<?php
$pageName = basename(THIS_PAGE, ".php");
$pageName = str_replace("_", " ", $pageName);
$bcTitle = "";    
?>
<div class="container">
    <div class="row">
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
        <li class="active <?php if($pageName == 'post'){echo 'postBc';} ?>">
            <em>
            <?php
            if(isset($postId)) {
                // Get the specific post by retrieving its post_id value through the query string
                $query  = "SELECT * FROM posts WHERE post_id = {$postId}";
                $result = $conn->query($query);
                confirmQuery($result);
                while($row = $result->fetch_assoc()) {
                    $bcTitle = strlen($row["title"]) > 20 ? substr($row["title"], 0, strrpos(substr($row["title"], 0, 20), " ")) . " ..." : $row["title"];
                    echo "<span title='" . $row["title"] . "'>" . $pageName . " : " . $bcTitle . "</span>";
                }
            } elseif ($pageName == "category") {
                echo $pageName . " : " . $category; // set in category.php             
            } elseif ($pageName == "author posts") {
                echo $pageName . " : " . $author; // set in author_posts.php                
            } else {
                echo $pageName;
            }
            ?>
            </em>
        </li>
      </ol>
    </div>
</div>