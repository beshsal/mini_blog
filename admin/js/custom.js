// TEXT EDITOR
$(function() {
// Target textarea elements
    tinymce.init({
        selector: '#content',
        theme: 'modern',
        plugins: [
          'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
          'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
          'save table contextmenu directionality template paste textcolor'
        ],
        // content_css: 'css/content.css',
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor fontsizeselect fontselect',
        setup : function(ed)
        {
            ed.on('init', function() 
            {
                this.getDoc().body.style.fontSize = '12.5pt';
                this.getDoc().body.style.fontFamily = 'Ledger';
            });
        },
        fontsize_formats: '8pt 10pt 10.5pt 11pt 11.5pt 12pt 12.5pt 14pt 16pt 18pt 24pt 36pt',
        font_formats: 'Arial=arial,helvetica,sans-serif; Ledger=ledger,serif; Oxygen=oxygen,sans-serif'
    });
    
    // Target textarea elements
    tinymce.init({
        selector: '#lead, #wel-msg',
        theme: 'modern',
        plugins: [
          'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
          'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
          'save table contextmenu directionality template paste textcolor'
        ],
        // content_css: 'css/content.css',
        toolbar: 'insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | forecolor backcolor fontsizeselect fontselect',
        setup : function(ed)
        {
            ed.on('init', function() 
            {
                this.getDoc().body.style.fontSize = '14pt';
                this.getDoc().body.style.fontFamily = 'Oxygen';
            });
        },
        fontsize_formats: '8pt 10pt 10.5pt 11pt 11.5pt 12pt 12.5pt 14pt 16pt 18pt 24pt 36pt 48pt',
        font_formats: 'Arial=arial,helvetica,sans-serif; Ledger=ledger,serif; Oxygen=oxygen,sans-serif'
    });
});

// SELECT ALL CHECKBOXES
$(function() {
    // Target the #selectAllBoxes checkbox input for selecting all checkboxes and add a click event to it
	$('#selectAllBoxes').click(function(event) {	
		// If the #selectAllBoxes checkbox is checked
		if(this.checked) {
            // All checkboxes that are not disabled should be checked
            // so iterate through all checkboxes
            $('.checkBoxes').each(function() {
                var disabled = $(this).hasClass("disabled");
                if (disabled !== true) {
                    // and set them to true (they are checked)
                    this.checked = true;
                }
            });
		} else {
            $('.checkBoxes').each(function() {
                // Otherwise if #selectAllBoxes checkbox is not checked
                // all checkboxes are set to false (they are unchecked)
                this.checked = false;
            });
		}
	});
});

// SELECT ALL CHECKBOXES USING THE SHIFT KEY
$(function() { 
    var lastChecked = null;
    var $checkboxes = $('.checkBoxes');
    $checkboxes.click(function(event) {
        if(!lastChecked) {
            lastChecked = this;
            return;
        }

        if(event.shiftKey) {
            var start = $checkboxes.index(this);
            var end   = $checkboxes.index(lastChecked);
            $checkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).attr('checked', lastChecked.checked);
        }

        lastChecked = this;
    });
});

// SINGLE ITEM DELETION
$(function() {   
    // When the Delete link is clicked (a.delete)
    $(".delete").on('click', function() {        
        var id = $(this).attr("rel");
        // Create a hidden input element to hold the id of the item to delete, and append it to the delete form in the modal
        var input = $("<input>")
           .attr("type", "hidden")
           .attr("name", "id").val(id);
        $('#deleteform').append($(input));        
        $("#deleteModal").modal('show'); // then call the modal        
    });
});

// SWITCH SEARCH FIELDS
$(function() {
    $('#switchFields').on('click',function() {
        if($('#form1, #searchUserForm1, #searchCommForm1, #logo1').hasClass('show')) {            
            $('#form1, #searchUserForm1, #searchCommForm1, #logo1').removeClass('show').hide();
            $('#form2, #searchUserForm2, #searchCommForm2, #logo2').addClass('show').show();
        } else if($('#form2, #searchUserForm2, #searchCommForm2, #logo2').hasClass('show')) {
            $('#form2, #searchUserForm2, #searchCommForm2, #logo2').removeClass('show').hide();
            $('#form1, #searchUserForm1, #searchCommForm1, #logo1').addClass('show').show();
        }
        
        if ($(this).text() == "Search by username and/or email") {
            $(this).text("Search by firstname and/or lastname");
        } else if ($(this).text() == "Search by firstname and/or lastname") {
            $(this).text("Search by username and/or email");
        } else if ($(this).text() == "Search by user") {
            var author = $(this).attr('author');
            if (typeof author !== undefined && author !== false && author == "yes") {
                $(this).text("Search by post title");
            } else {
                $(this).text("Search by author and/or post title");
            }            
        } else if ($(this).text() == "Search by author and/or post title" || $(this).text() == "Search by post title") {
            $(this).text("Search by user");
        } else if ($(this).text() == "Add multicolor logo") {
            $(this).text("Add single color logo");
        } else {
            $(this).text("Add multicolor logo");
        }
    });
});

// TOGGLE FIELDS
$(function() {
    $(".uploadOption").hide();
    $("#uploadNew").click(function() {
        if($(this).is(":checked")) {
            $(".uploadOption").show();
            $('#imageId').prop('disabled', true);
        } else {
            $(".uploadOption").hide();
            $('#imageId').prop('disabled', false);
        }
    });    
    $(".selectOption").hide();
    $("#selectImage").click(function() {
        if($(this).is(":checked")) {
            $(".selectOption").show();
            $('#image').prop('disabled', true);
        } else {
            $(".selectOption").hide();
            $('#image').prop('disabled', false);
        }
    });
});

// SEARCH & SORT NAVIGATION
$(function() {
    function searchSortNav() {
        var x = document.getElementById("searchSortNav");
        if (x.className === "search-sort-nav") {
            x.className += " responsive";
        } else {
            x.className = "search-sort-nav";
        }
    }
});

// DROPDOWN SUBMENU
$(function() {
      $('.dropdown-submenu a.test').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
      });
});

// SHOW POST IMAGE WHEN SELECTED
$(function() {
    $('select[name=image_id]').change(function() {
        var path = "images/post_images/",
            selectedImg  = $(this).find('option:selected').text(); // gets the text name of the post image

        var fullpath = path + selectedImg;

        $('img#post-img-holder').attr("src", fullpath);
    });
});

// TRACK ONLINE USERS
$(function() {
    // Using AJAX so the page doesn't need to be refreshed everytime a user logs on
    function loadOnlineMembers() {
        // Make a GET request to util_funcs.inc.php and execute a function when we get the response back from the server
        // (Note the "onlinemembers" parameter we are sending through the URL)
        $.get("../includes/util_funcs.inc.php?onlinemembers=result", function(data) {
            // The function takes the response data and inserts it in the .onlinemembers container (span)
            $(".onlinemembers").text(data);

        });
    }
    // Set a time interval to call loadOnlineMembers() every 1 second
    setInterval(function() {
        loadOnlineMembers();
    }, 1000);
});

// DISABLE ELEMENTS
$(function() {
    $("input.disabled").attr("disabled", true);
});