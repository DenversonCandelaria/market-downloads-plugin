jQuery(document).ready(function($){
	$('#category-select').on('change', function(){

		var selectedID = $(this).val();
		var marketDataGroup = $('.market-reports-group');
		if(selectedID){
			marketDataGroup.hide();
			$('#'+selectedID).fadeIn();	
		}else{
			marketDataGroup.fadeIn();
		}
	});
	var opt = {
  		autoUpdateInput: false,
  		timePicker: true,
  		timePicker24Hour: true,
	  	locale: {
	  		format: 'DD MMMM YYYY HH:mm',
	      	cancelLabel: 'Clear'
	  	},
	  	maxDate: new Date()
	}

	if(php.post_type == 'market-data'){
		opt.minDate = new Date(php.min_date);
	}

	$('input[name="datefilter"]').daterangepicker(opt);
	
	var start_date = '';
	var end_date = '';

	$('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
  		$(this).val(picker.startDate.format('DD MMM YYYY HH:mm') + ' - ' + picker.endDate.format('DD MMM YYYY HH:mm'));

  		start_date = picker.startDate.format('YYYY-MM-DD HH:mm');
  		end_date = picker.endDate.format('YYYY-MM-DD HH:mm');

		filter_market_downloads_files(php.post_type);
		$(this).attr('size', $(this).val().length);
	});

	$('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
	  	$(this).val('');
	  	start_date = '';
	  	end_date = '';
	  	filter_market_downloads_files(php.post_type);
	  	$(this).attr('size', $(this).val().length);
	});

	$('#sort').on('change', function(){
		filter_market_downloads_files(php.post_type);
	});

	
	// console.log(php);

	filter_market_downloads_files(php.post_type);

	var pagination = '';

	function filter_market_downloads_files(type){
		var container = $('#market-data-cont');
		var loader = $('#market-data-loader');
		var paginationCont = $('#pagination-container');
		var dl_all_btn_cont = $('#dl-all-btn');

		var sort = $('#sort').val();

		var action = (type == 'market-data') ? 'display_filtered_market_data_files' : 'display_filtered_market_reports_files';
		
		var data = {
			action: action,
			sort: sort,
			datefilter:'',
			page: 1,
			post_id: php.post_id
		};

		if(start_date && end_date){
			data.datefilter = {
				start: start_date,
				end: end_date
			};
		}
		
		container.hide();
		dl_all_btn_cont.hide();
		loader.show();
		if(pagination) { paginationCont.pagination('destroy'); }

		$.ajax({
			type: 'POST',
			url: php.ajax_url,
			data: data,
			success: function(files){
				var files = JSON.parse(files);
				loader.hide();
				if(files.count > 0){
					pagination = paginationCont.pagination({
					    dataSource: files.source,
					    // dataSource: [],
					    pageSize: 24,
					    prevText: '<img class="page-icon" src="'+php.theme_uri+'/assets/img/back.png">',
					    nextText: '<img class="page-icon" src="'+php.theme_uri+'/assets/img/next-page.png">',
					    className: 'custom-paginationjs',
					    formatResult: function(data) {
					        var result = [];
					        for (var i = 0; i < data.length; i++) {

					        	// console.log(path);
					        	if(type == "market-data"){
					        		var path = data[i];
					        		var html = '<div class="col-lg-6" >\
					        					<div class="market-reports-item">\
					        						<div class="market-reports-title">\
				        								'+files.data[path].filename+'\
				        								<div class="market-data-dl-date">'+files.data[path].date+'</div>\
					        						</div>\
					        						<a href="?md_file='+path+'">\
							                            <button class="mr-view-data-btn">\
							                                <img class="market-data-dl-icon" src="'+php.theme_uri+'/assets/img/inside-download.png">\
							                            </button>\
							                        </a>\
					        					</div>\
				        					</div>';
		        				}else{
		        					var id = data[i];

		        					var doc_title = files.data[id].doc_title;
		        					var published_date = files.data[id].published_date;
		        					var description = files.data[id].description;
		        					var dl_path = files.data[id].dl_path;

		        					var html = '<div class="col-lg-6" >\
						                          	<div class="market-reports-item">\
						                            	<div class="market-reports-title">\
						                            		'+doc_title+'\
						                              		<div class="market-data-dl-date1">'+published_date+'</div>\
							                              	<div class="market-data-dl-description">'+description+'</div>\
							                            </div>\
							                            <div class="market-report-dl-right">\
							                              	<div class="market-data-dl-date2">'+published_date+' <br></div> \
							                              	<div class="clear"></div>\
							                              	<a href="?md_file='+dl_path+'">\
							                                	<button class="mr-view-data-btn">\
							                                    	<img class="market-data-dl-icon" src="'+php.theme_uri+'/assets/img/inside-download.png">\
							                                	</button>\
							                              	</a>\
							                            </div>\
							                            <div class="clear"></div>\
						                          	</div>\
					                        	</div>';
		        				}
					        	

	        					
					            result.push(html);
					        }
					        return result;
					    },
					    callback: function(data, pagination) {
					        $('#list-cont').html(data)
					        container.fadeIn();
					        var dl_url = '?post='+php.post_id+'&sort='+sort+'&page='+pagination.pageNumber+'&start='+start_date+'&end='+end_date;
					        // console.log(dl_url);
					        dl_all_btn_cont.attr('href', dl_url);
					        dl_all_btn_cont.fadeIn();
					    },
					    afterNextOnClick: function(){
					    	$('body').mCustomScrollbar("scrollTo", "#events-tab-content", {
				                scrollInertia: 500
				            });
					    },
					    afterPreviousOnClick: function(){
					    	$('body').mCustomScrollbar("scrollTo", "#events-tab-content", {
				                scrollInertia: 500
				            });
					    },
					    afterPageOnClick: function(){
					    	$('body').mCustomScrollbar("scrollTo", "#events-tab-content", {
				                scrollInertia: 500
				            });
					    }
					});
				}else{
					var html = "<div class='empty-notice' style='width:100%'>No files found</div>";
					$('#list-cont').html(html);
					container.fadeIn();
				}
				

				// container.html(data.html).fadeIn();
			}
		});
	}

});