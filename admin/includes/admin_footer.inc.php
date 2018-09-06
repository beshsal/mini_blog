        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>        
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script>
            
        // SET FEATURED POST
        // Function to perform when a checked radio button is unchecked (deselected)
        function deselectEvent() {
            // Create an object to hold the selected (checked) item
            var selected = {};                
            // Set up a click event on any radio button
            $('input[type="radio"]').on('click', function() {
                // If the name property is in the selected object AND the radio button element is NOT in the selected object
                if (this.name in selected && this != selected[this.name]) {
                    // Trigger a deselect event on the current radio button element
                    $(selected[this.name]).trigger("deselect");
                }
                // And assign it to the selected object                    
                selected[this.name] = this;
            }).filter(':checked').each(function() {
                // Filter through to match an element with a checked property, and add it to the selected object
                selected[this.name] = this;
            });
        }
        $(function() {
            // Call the deselect event function
            deselectEvent(true);
            // Try switching these around
            // Note using .on is triggered on the deselect function, not click, change, etc.
            $('input[name="set_featpost"]').on('deselect', function() {
                // If a radio button is unchecked, send a AJAX request to change featured to "No"
                $.post("includes/set_featpost.inc.php", 
                {
                  postID: $(this).data('postid'), // data to send
                  featPost: "No"
                },
                function(data, status){ // success function
                });
            }).on('change', function() {
                // If a radio button is checked, send a AJAX request to change featured to "Yes"
                $.post("includes/set_featpost.inc.php", 
                {
                  postID: $(this).data('postid'), // data to send
                  featPost: "Yes"
                },
                function(data, status){ // success function 
                    alert(data);
                });
            });
         });
        </script>

        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="js/bootstrap-filestyle-1.2.3/src/bootstrap-filestyle.min.js"></script>
        <script src="js/custom.js"></script>

        <script>
        // CONFIRM BULK DELETION
        function confirmDelete() {
            var atLeastOneChecked = $('input[name="checkBoxArray[]"]:checked').length > 0;
            var val = $('select[name="bulk_options"] option:selected').val();        
            if (atLeastOneChecked) {
                if (val == "delete_selected") {
                    return confirm("The selected items will be deleted.");
                }
            } else {
                return alert("Please select at least one item.");
            }
        }
        </script>
    </body>
</html>
<?php ob_end_flush(); ?>
