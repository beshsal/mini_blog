<div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->
<div id="userImageModal" class="modal fade" role="dialog">
  <p class="success-alert"></p>
  <div id="modal-userImage" class="modal-dialog">
    <!-- Modal content -->
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">User Image</h4>
      </div>
      <div class="modal-body">
        <form enctype="multipart/form-data" id="userImageForm" role="form">
            <p class="uploadMsg"></p>
            <div class="form-group">
              <input type="file" name="image" id="imgFile" class="filestyle" data-buttonName="btn neutral-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
            </div>
            <input name="upload_img" type="button" class="btn standard-btn" id="uploadImg" value="Upload Image" onclick="submitImage()">
        </form>
      </div>
    </div>
  </div>
</div>