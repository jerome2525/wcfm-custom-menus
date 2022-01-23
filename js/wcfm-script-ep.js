jQuery(document).ready(function($) {
	function wcfmAjax(filter, resId) {
        var filter = $(filter);
        if( filter ) {
            $.ajax({
                url:filter.attr('action'),
                data:filter.serialize(), // form data
                type:filter.attr('method'), // POST
                cache: false,
                beforeSend: function() {
                    $('.loader').show();
                },
                complete: function(){
                    $('.loader').hide();
                },
                success:function(data){
           			$(resId).html(data);
                    setTimeout(function() {
                        $('.ep-message').fadeOut(); 
                    }, 5000);
                },
                async: "false",
            });
        }
    }

    function checkIfEmpty() {
        $('#ep-api-settings-form input[type="text"]').each(function () {
            if (!$(this).val() ) {
              $(this).addClass('error');
            } else {
              $(this).removeClass('error');
            }
        });
    }

    $('#ep-api-settings-form').submit(function(){
		var resId = '#result';
		wcfmAjax(this, resId );
		return false;
    });

    $('#ep-import-product-form').submit(function(){
        var resId = '#result';
        wcfmAjax(this, resId );
        return false;
    });

    $('#save-button').click(function(e) {
		e.preventDefault();
        $('.ep-message').remove();
        checkIfEmpty();
        var countError = $('.error').length;
        if( countError == 0 ) {
		  $('#ep-api-settings-form').submit();
        }
	});

    $('#import-button').click(function(e) {
        e.preventDefault();
        checkIfEmpty();
        var countError = $('.error').length;
        if( countError == 0 ) {
            $('#ep-import-product-form').submit();
        }
    });


});	