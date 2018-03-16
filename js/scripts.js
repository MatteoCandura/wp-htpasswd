jQuery(document).ready(
    function(){
        jQuery('#wph-password-visibility').on('click', function(event){
            event.preventDefault();
            var input = jQuery("input[name='wph_pass'].wph-input");
            var div = jQuery(this).find('div');
            if( div.hasClass('dashicons-hidden') ){
                div.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                input.attr('type', 'password');
            } else {
                div.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                input.attr('type', 'text');
            }
        });
    }
);