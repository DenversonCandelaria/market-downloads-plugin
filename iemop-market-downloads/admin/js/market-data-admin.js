jQuery(document).ready(function($) {

    var upload_folder_field = $('#acf-field_5e8c4e1b1a242');

    // Restrict input to alphanumeric and dash by using a regular expression filter.
    upload_folder_field.inputFilter(function(value) { //inputFilter function from *general-helper-admin.js*
        return /^[a-zA-Z0-9-]*$/.test(value);
    });
    upload_folder_field.on('change', function(){
        $('#post-body-content #opt').val('');
    });

    $(document).on('click', '.folder_option', function(){
        var opt = $(this).data('opt');
        var acf_notice = $(this).parents('.acf-notice');

        if(opt == 0){
            acf_notice.slideUp().remove();
            $('#opt').val('');
        }else{
            $('#opt').val(opt);
            acf_notice.removeClass('-error').addClass('-success');
            acf_notice.find('p').text('Please click the publish/update button to apply the changes.');
        }
    });


    if($('.term-parent-wrap').length > 0){ //remove term parent field
        $('.term-parent-wrap').remove();
    }


    if(typeof acf !== 'undefined'){
        acf.add_filter('select2_args', function( args, $select, settings, field, instance ){
            $select.data( 'placeholder', 'Select category' );

            return args;
        });
    }
    var post_status_page = $('.post_status_page').val();

    if(post_status_page == 'trash'){
        $('table input[type="checkbox"]').on('change', function(){
            disable_bulk_restore('upload-folder');
        });
    }

    var screen = $('input[name="screen"]').val();

    if(screen == 'edit-market_data_category'){
        $('table input[type="checkbox"]').on('change', function(){
            disable_bulk_delete_category();
        });
    }

});
