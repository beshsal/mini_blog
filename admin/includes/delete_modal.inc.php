<?php
// Get the name of the page
$pageName = basename(THIS_PAGE, ".php");
?>
<!-- MODAL -->
<div id="deleteModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Please confirm deletion:</h4>
      </div>
      <div class="modal-body">
        <h3 class="text-center">This
        <?php
            // Customize the delete modal's message for the respective page.
            if ($pageName == "posts") {
                echo "post";
            } elseif ($pageName == "categories") {                
                echo "category";
            } elseif ($pageName == "comments") {
                echo "comment";
            } elseif ($pageName == "users") {
                echo "user";
            } else {
                echo "item";
            }
        ?> will be deleted.
        </h3>
        <h5 class="text-center" style="color: #ff0000;">This action cannot be undone!</h5>
      </div>
      <div class="modal-footer">          
          <form action="" method="post" id="deleteform">
              <input type="submit" name="delete_item" class="btn delete-btn modal_delete_link" value="OK">
              <button type="button" class="cancel-btn" data-dismiss="modal">Cancel</button>
          </form>
      </div>
    </div>
  </div>
</div>