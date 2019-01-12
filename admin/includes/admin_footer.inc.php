        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>        
        <!-- Include all compiled plugins (below), or include individual files as needed --> 
        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="js/bootstrap-filestyle-1.2.3/src/bootstrap-filestyle.min.js"></script>
        <script src="js/custom.js"></script>

        <script>       
        // TOGGLE TABLE ORDER (ASC/DESC) 
        // function sortTable(column) {
        //     var order = $("#order").val();
        //     $.post("includes/sort_table.inc.php",
        //     // Send the column name passed to the function and the specified order.
        //     {
        //       column: column, 
        //       order: order
        //     },
        //     function(response) {
        //         $("#posts-table tr:not(:first)").remove();     
        //         $("#posts-table tbody").append(response.html);
        //         if (order == "DESC") {
        //             $("#order").val("ASC");
        //         } else {
        //             $("#order").val("DESC");
        //         }
        //     }, "JSON");            
        // }
        $(document).on('click', '#sort-table', function() {
          var order = $("#order").val();
          $.ajax({
            url: "includes/sort_table.inc.php",
            type: "POST",
            dataType: "JSON",
            data: { 
              order: order,
              column: $(this).data('column'),
              status: $(this).data('status'),
              catID: $(this).data('catid'),
              user: $(this).data('user'),
              search1: $(this).data('srch1'),
              search2: $(this).data('srch2')
            },              
            success: function(response) {
              $("#posts-table tr:not(:first)").remove();
              // $("#posts-table").append(response.html);
              $("#posts-table tbody").html(response.html);
              if (order == "DESC") {
              $("#order").val("ASC");
              } else {
              $("#order").val("DESC");
              }
            }
          });
        });
        </script>

        <script>            
        // SET FEATURED POST
        // The function to run when a checked radio button is unchecked (deselected)
        function deselectEvent() {
            // Create an object to hold the selected (checked) item.
            var selected = {};                
            // Set a click event on any radio button.
            $('input[type="radio"]').on('click', function() {
                // If the name property is in the selected object AND the radio button element is NOT in the selected object
                if (this.name in selected && this != selected[this.name]) {
                    // Trigger a deselect event on the current radio button element.
                    $(selected[this.name]).trigger("deselect");
                }
                // And assign it to the selected object.                    
                selected[this.name] = this;
            }).filter(':checked').each(function() {
                // Filter through to match an element with a checked property, and add it to the selected object.
                selected[this.name] = this;
            });
        }
            
        $(function() {
            // Call the deselect event function.
            deselectEvent(true);
            // (Try switching these around.)
            // Note .on is triggered on the deselect function here.
            $(document).on('deselect', 'input[name="set_featpost"]', function() {
                // If a radio button is unchecked, send a AJAX request to change the table's featured field to "No".
                $.post("includes/set_featpost.inc.php", 
                {
                  postID: $(this).data('postid'), // data to send
                  featPost: "No"
                },
                function(data, status) {
                    // alert(status);
                }, "JSON");
            }).on('change', 'input[name="set_featpost"]', function() {
                // var elType = $(this).parent().parent().find(".delete").get(0).tagName;
                var delBtn = $(this).parent().parent().find(".delete");
                // If a radio button is checked, send a AJAX request to change featured to "Yes".
                $.post("includes/set_featpost.inc.php", 
                {
                  postID: $(this).data('postid'), // data to send
                  featPost: "Yes"
                },
                function(data, status) { // success function
                    if (data.postIDErr) {
                        alert(data.postIDErr);
                    }
                    if (data.fail) {
                        alert(data.fail);
                    }
                    if (data.success) {
                        // alert(elType);
                        $("#posts-table tbody").find(".delete").removeClass("disabled");
                        alert(data.success);                        
                        delBtn.addClass("disabled");
                    }
                }, "JSON");
            });
        });
        </script>

        <script>
        // CONFIRM BULK DELETION
        // This function handles the confirmation for other options too.
        function confirmDelete() {
            // If there are checked items, set a boolean to true.
            var atLeastOneChecked = $('input[name="checkBoxArray[]"]:checked').length > 0;
            // Get the value of the selected option tag from the select element.
            var val = $('select[name="bulk_options"] option:selected').val();
            // If there are checked items       
            if (atLeastOneChecked) {
                // If the value is "delete_selected", confirm the deletion with the user.
                if (val == "delete_selected") {
                    return confirm("The selected item(s) will be deleted.");
                }
            // If there are no checked items
            } else {
                return alert("Please select at least one item.");
            }
        }
        </script>

    </body>
</html>
<?php ob_end_flush(); ?>
