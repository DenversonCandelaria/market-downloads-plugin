jQuery(document).ready(function($) {
	$(document).on('click', '.view-files-logs', function(e){
    	e.preventDefault();
    	show_list_files($(this));
    });

    function show_list_files(elem){

    	var id = elem.data('id');
    	var post_id = elem.data('postid');
    	var count = elem.data('count');
    	var act = elem.data('act');
    	var date = elem.data('date');
    	var archive_name = elem.data('archive');
    	
 		$('#view-files-'+id).dialog({
	        closeOnEscape: false,
	        resizable: false,
	        height: "auto",
	        width: 600,
	        maxHeight: 500,
	        modal: true,
			draggable: false,
			buttons: {
		        "Export to CSV": function(e) {

		        	$(e.currentTarget).prop('disabled', true);
		        	$(e.target).text('Exporting...');
	        	 	$.ajax({
	                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
	                    url: php_data.ajax_url,
	                    data:{
                        	action: 'export_log_files',
                            log_id: id,
                            act: act,
                            date: date,
                            post_type: php_data.post_type,
                            post_id: post_id
                        }
	                })
	                .done(function(dl_path){
	                    top.location.replace("?md_file="+dl_path+'&remove=true');
		        		$(e.currentTarget).prop('disabled', false);
		        		$(e.target).text('Export');
	                })
		        }
	      	},
	      	open: function(){
            	$('body').css('overflow-y', 'hidden');
        		var lbl = (count > 1) ? 'files' : 'file';
        		archive_name = (archive_name) ? '('+archive_name+')' : '';
    			$(this).parent().find(".ui-dialog-title").html("Viewing Files "+archive_name+" <span style='float: right;''>Total number of files: "+count+" "+lbl+"</span>");
            },
	        close: function() {
	        	$('body').css('overflow-y', 'scroll');
	            $('#bulk-action-selector-top, #bulk-action-selector-bottom').val('-1');
	        }
	    });
	}
});