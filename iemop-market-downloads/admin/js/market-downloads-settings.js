jQuery(document).ready(function($) {

    display_top_report_table();

    // Restrict input to digits by using a regular expression filter.
	$(".num").inputFilter(function(value) { //inputFilter function from *general-helper-admin.js*
		return /^\d*$/.test(value);
    });

	$('input[name="position"]').on('change', function(){
		display_top_report_table();
	});

	$('.report-files-table .sortable').sortable({
		opacity: 0.8,
		update: function(e, ui){
			var sortedLinks = $(this).sortable('toArray');
			$('.autosave').text("Saving..");
			$('.autosave').show();
			$.ajax({
				url: php_data.ajax_url,
				type: 'POST',
				data: {
					action: 'sort_report_file',
					sorted: sortedLinks
				},
				success: function(data){
					if(data == 'true'){
						$('.autosave').text("Saved!");
						setTimeout(function(){
							$('.autosave').hide();
						}, 1000)
					}
				}
			});
		}
	});

	$('.remove_top_page').on('click', function(e){
		e.preventDefault();
		var thisLink = $(this);
		var tbody = thisLink.parents('tbody');
		var id = $(this).data('id');

		var remove_top_dialog = $('#dialog-remove-file');

		remove_top_dialog.dialog({
	        closeOnEscape: false,
	        resizable: false,
	        height: "auto",
	        width: 400,
	        modal: true,
			draggable: false,
	        buttons: {
                Yes: function(){
                    $.ajax({
                    	url: php_data.ajax_url,
                    	type: 'POST',
                    	data:{
                    		action: 'add_to_top_page',
                    		id: id
                    	},
                    	success: function(data){
                    		var data = JSON.parse(data);
                    		if(data['suc']){
                    			thisLink.parents('tr').remove();
                    			if($('.report-files-table tbody tr').length == 0){
                    				tbody.html('<tr><th>No files added</th></tr>');
                    			}
                    			if($('.report-files-table tbody tr').length == 1){
                    				$('.sortable').sortable('disable');
                    			}
                    			$('.autosave').show();
								setTimeout(function(){
									$('.autosave').hide();
								}, 1000)
                    		}
                    	}
                    });
                    remove_top_dialog.dialog( "close" );
                },
                No: function() {
                    remove_top_dialog.dialog( "close" );
                }
            },
            close: function(){
                remove_top_dialog.dialog( "close" );
                $('body').css('overflow-y', 'scroll');
            },
            open: function(){
            	$('body').css('overflow-y', 'hidden');
            }
	    });
	});


    function display_top_report_table(){
    	var selected = $('input[name="position"]:checked').val();

    	if(selected == 'manual'){
    		$('#manual-table').show();
    	}else{
    		$('#manual-table').hide();
    	}
    }

});