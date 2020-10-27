jQuery(document).ready(function($){
	$('#daterange').datepick({
    	rangeSelect: true,
    	monthsToShow: 2,
    	maxDate: new Date(),
    	onSelect: function(dates) {
    		// console.log(dates);
    		if(dates.length > 0){
    			var today = new Date();
    			today.setHours(0,0,0);

    			var date = new Date(dates[0]);
	    		var maxDate = (dates[0]).getDate() + 30;
		 	 	date.setDate(maxDate);
		 	 	if(date.getTime() < today.getTime()){
			 		$(this).data('datepick').options.maxDate = date
		 	 	}
    		}
	    }
	});

	$(document).on('click', '.datepick-cmd-clear', function(e){
		$('#daterange').data('datepick').options.maxDate = new Date();
	});

	$('.generate_summary_report').on('click', function(e){

		e.preventDefault();
		$('.custom-notice-summary-reports').hide();

		var daterange = $('#daterange').datepick('getDate');
		var market_data = $('#market_data_type').val();

		if(daterange.length == 0 || market_data == ''){
			$('.custom-notice-summary-reports p').text("Please fill all required fields").parent().show();
        	$('body').animate({ scrollTop: 0 }, "fast");

        	return;
		}

		var thisBtn = $(this);
		thisBtn.prop('disabled', true);
		thisBtn.val('Generating...');

	 	$.ajax({
	        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
	        url: php_data.ajax_url,
	        data:{
	        	action: 'generate_summary_report',
	        	daterange: [
	        		$.datepick.formatDate('yyyymmdd', daterange[0]),
	        		$.datepick.formatDate('yyyymmdd', daterange[1])
	        	],
	        	market_data: market_data
	        }
	    })
	    .done(function(dl_path){
	    	if(dl_path){
	    		top.location.replace("?md_file="+dl_path+'&remove=true');
	    	}else{
				$('.custom-notice-summary-reports p').text("No available report found").parent().show();
	    	}
	    	thisBtn.prop('disabled', false);
			thisBtn.val('Generate');	
	        
	    })
	});

	$('.custom-notice-summary-reports .acf-notice-dismiss').on('click', function(){
		$(this).parent().hide();
	});
});