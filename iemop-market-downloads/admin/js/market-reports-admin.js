jQuery(document).ready(function($) {

	if($('.term-parent-wrap').length > 0){ //remove term parent field
        // $('.term-parent-wrap').remove();
    }

    if($('#edittag').length > 0){ // remove parent field on edit tag when tag is a parent
    	var parent_field = $('#parent'); 
    	var parent_val = parent_field.val();
    	if(parent_val == -1){
    		parent_field.parents('tr').hide();
    	}
    }

    $( document ).ajaxSuccess(function( event, xhr, settings ) {
	    //Check ajax action of request that succeeded
	    var url = php_data+"?"+settings.data;
	    var action = urlParam('action', url).replace(/\+/g, ' ');
	    var level_0 = $('#parent option:selected').hasClass('level-0'); 

	    if(action == 'add-tag' && !level_0) { //update parent field if the previous tag added is not a child

	        //Send ajax response to console.
	        // console.log("Triggered ajaxSuccess handler. The ajax response was: " + xhr.responseText );

	        $.ajax({
	    		type: 'POST',
				url: php_data.ajax_url,
	    		data:{
	    			action: 'load_parent_category_item_field_ajax'
	    		},
	    		success: function(data){
	    			var cats = JSON.parse(data);
	    			if(cats){
    					var options = '<option value="-1">None</option>';
    					$.each(cats, function(key, val){
    						options += '<option class="level-0" value="'+key+'">'+val+'</option>';
    						$('#parent').html(options);
    					});
	    			}
	    			
	    			
	    		}
	    	});
	    }
	});


    if(typeof acf !== 'undefined'){

	    acf.add_filter('select2_args', function( args, $select, settings, field, instance ){

	    	$select.data( 'placeholder', 'Select category' );

		    return args;
		});


		var parent_cats = Object.keys(php_data.parent_cats);

		acf.add_filter('select2_ajax_results', function( json, params, instance ){

			var all_cats = json.results;

			for (var i = 0; i < all_cats.length; i++) {
				var id = all_cats[i].id.toString();

				if($.inArray(id, parent_cats) !== -1){
					all_cats[i].disabled = true; //disabled parents
				}
			}
			console.log(all_cats);
			json.results = all_cats;

		    return json;

		});
	}

	if($('#upload_files_form #dropzone').length > 0){
		var preview = $('#preview');
		var previewTemplate = preview.html();
		preview.html('');
		preview.show();

		var myDropzone = new Dropzone("#upload_files_form #dropzone", { 
			url: php_data.ajax_url,
			maxFilesize: php_data.max_filesize, //mb
			timeout: 0,
			uploadMultiple: true,
			autoProcessQueue: false,
			previewTemplate: previewTemplate,
			previewsContainer: '#preview',
			parallelUploads: php_data.max_files,
			maxFiles: php_data.max_files,
			dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
			dictMaxFilesExceeded: "You can not upload any more files. Maximum of {{maxFiles}} files only.",
			acceptedFiles: '.doc,.DOC,.docx,.DOCX,.xls,.XLS,.xlsx,.XLSX,.ppt,.PPT,.pptx,.PPTX,.pdf,.PDF,.jpg,.JPG,.png,.PNG,.csv,.CSV,.gif,.GIF,.zip,.ZIP',
			init: function() {
		        dz = this; // Makes sure that 'this' is understood inside the functions below.


		        // for Dropzone to process the queue (instead of default form behavior):
		        $('#submit').on('click', function(e){
		        	e.preventDefault();
		            e.stopPropagation();
		            
		            if(validate_upload_fields()){
		            	dz.processQueue();	
		            }
		        });

		        //send all the form data along with the files:
		        dz.on("sendingmultiple", function(data, xhr, formData) {
		            formData.append('action', 'upload_file');
		            formData.append('post_id', php_data.post_id);

		            $('form .dz-preview:not(.dz-complete) input[type="text"]').each(function(i, elem){
		            	formData.append(this.name, $(elem).val());
		            });

		            $('#total-progress').show();
		            $('#submit').prop('disabled', true);
		        });

		        dz.on("totaluploadprogress", function(progress) {
		        	var prog = progress.toFixed()+"%";
		        	$('#total-progress-bar').css('width', prog);
		        	$('#percentage').text(prog);
		        	// console.log(prog);
				  // document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
				});

				dz.on("processing", function() {
			 		myDropzone.options.autoProcessQueue = true;
			 		// $('#dropzone').hide();
			 		$('#preview .delete').hide();
		 			dz.removeEventListeners();
			 	});

			 	dz.on("queuecomplete", function() { //all file
				    myDropzone.options.autoProcessQueue = false;
				    $('#total-progress').hide();
				    $('#total-progress-bar').css('width', '0%');
		        	$('#percentage').text('0%');
		        	$('#submit').prop('disabled', false);
					// $('#dropzone').fadeIn();
					dz.setupEventListeners();
			 		$('#preview .delete').show();
			 		disable_upload_btn();
				});

			 	dz.on("successmultiple", function(){
			 		Swal.fire({
				  		type: 'success',
					  	title: 'Upload Complete',
					});
			 	});

				dz.on("success", function(file) { //per file
			  		dz.removeFile(file);
				});

			 	dz.on("error", function(file) {
			 		$('#preview .dz-error input').prop('disabled', true);
			 		disable_upload_btn();
			 	});

			 	dz.on("addedfile", function(){
			 		disable_upload_btn();
			 	});

			 	dz.on("removedfile", function(){
		 		 	$('#total-progress-bar').css('width', '0%');
		        	$('#percentage').text('0%');
			 		disable_upload_btn();
			 	});
		    }
		});

		disable_upload_btn();
	  	
		
	}

	$(document).on('focus', 'input.published-date', function(){
  	// 		dt = new Date();
		// time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
  		// var datetime = php_data.date_today+" "+time;
  		// $(this).val(datetime);
  		$(this).datetimepicker({
			dateFormat: 'yy-mm-dd',
   	 		timeFormat: "HH:mm:ss",
   	 		// defaultDate: new Date(),
			changeYear: true,
			changeMonth: true
		});
  	});

	$(document).on('keydown keyup paste change', '#preview input', function(){
		var val = $.trim($(this).val());

		if(val.length){
			$(this).removeClass('error');
		}
	});


	//Delete on manage order page(single)
	$(document).on('click', '.delete_file', function(e){
	  	e.preventDefault();
	  	var id = jQuery(this).data('id');
	  	id = [id];
	  	delete_files(id);
	})

	if(php_data.post_id){
		$(document).on('click', '#doaction, #doaction2', function(e){

			var checkboxes = document.getElementsByName('bulk-chk[]');
			var ids = [];
			var newStatus = "";
			var select_top = $('#bulk-action-selector-top option:selected').val();
			var select_bot = $('#bulk-action-selector-bottom option:selected').val();

			e.preventDefault();

			for (var i=0, n=checkboxes.length;i<n;i++){
			  	if (checkboxes[i].checked){
		      		ids.push(checkboxes[i].value);
			  	}
			}

			if(ids.length == 0 ){ return; }

			if(select_top != "-1" || select_bot != "-1"){		  	
		      	delete_files(ids);  	
			}


		});	
	}
	

	$('.add_to_top').on('click', function(e){
		e.preventDefault();
		var thisLink = $(this);
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: php_data.ajax_url,
			data: {
				action: 'add_to_top_page',
				id: id
			},
			success: function(data){
				var data = JSON.parse(data);
				if(!data['suc']){
				  	var add_top_dialog = $('#maxTopPageDialog');
					add_top_dialog.find('.dialogContent').html('<span class="ui-icon ui-icon-alert" style="float:left; margin:2px 12px 20px 0;"></span><span class="statusMessage">'+data['msg']+'</span>');

					add_top_dialog.dialog({
				        closeOnEscape: false,
				        resizable: false,
				        height: "auto",
				        width: 400,
				        modal: true
				    });
				}else{
					if(data['msg'] == "Added"){
						thisLink.text("Remove from Top Page");
					}else{
						thisLink.text("Add to Top Page");
					}
				}
			}
		})
	});


	var post_status_page = $('.post_status_page').val();

    if(post_status_page == 'trash'){
        $('table input[type="checkbox"]').on('change', function(){
            disable_bulk_restore('post_title');
        });
    }

    var screen = $('input[name="screen"]').val();

    if(screen == 'edit-market_reports_category'){
        $('table input[type="checkbox"]').on('change', function(){
            disable_bulk_delete_category();
        });
    }

	function validate_upload_fields(){
		var success = true;

		$.each($('#preview .dz-preview:not(.dz-error) input'), function(i, elem){
			var val = $.trim($(elem).val());
			if(!val.length){
				$(elem).addClass('error');
				success = false;
			}else{
				$(elem).removeClass('error');
			}
		});

		if(!success){
			$([document.documentElement, document.body]).animate({
		        scrollTop: $("#preview .dz-preview:not(.dz-error) .error:visible:first").offset().top - 50
		    }, 600);
		}

		return success;
	}

	function disable_upload_btn(){
		if (myDropzone.files && myDropzone.files.length && $('#preview .dz-error').length == 0) {
  			$('#submit').prop('disabled', false);
  			$('#total-progress').show();
		} else {
			$('#submit').prop('disabled', true);
			$('#total-progress').hide();
		}

		$('#added-files').text(myDropzone.files.length);
	}

	function delete_files(ids){
	    var deleteDialogBulk = $('#deleteDialog');
	    var lbl = (ids.length == 1) ? 'this file?' : 'all selected files?';
 
        deleteDialogBulk.find('.dialogContent').html('<span class="ui-icon ui-icon-alert" style="float:left; margin:2px 12px 20px 0;"></span><span class="statusMessage">Are you sure you want to delete '+lbl+'</span>');
	   
	    deleteDialogBulk.dialog({
	        closeOnEscape: false,
	        resizable: false,
	        height: "auto",
	        width: 400,
	        modal: true,
			draggable: false,
	        close: function() {
	            $('#bulk-action-selector-top, #bulk-action-selector-bottom').val('-1');
	        },
	        buttons: {
	            Yes: function() {
	                $.ajax({
	                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
	                    url: php_data.ajax_url,
	                    data:{
                            // updateAction: 'singleUpdate',
                            ids: ids,
                        	action: 'delete_files',
                        	post_id: php_data.post_id
                        }
	                })
	                .done(function(){
	                    // location.reload();
	                    var redirect = 'edit.php?post_type=market-reports&page=browse-files&post='+php_data.post_id+'&paged=1';
	                    top.location.replace(redirect);
	                })
	                $( this ).dialog( "close" );
	            },
	            Cancel: function() {
	                $( this ).dialog( "close" );
	            }
	        }
	    });
	}

	function urlParam(name, url){
		// console.log(window.location.href);
	    // var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
	    if (results==null){
	       return '';
	    }
	    else{
	       return decodeURI(results[1]) || '';
	    }
	}
});