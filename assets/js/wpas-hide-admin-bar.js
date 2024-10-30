(function( $, window, undefined ) {
    $(document).ready(function() {
        $('.rander-select2').select2();

        $('#wpas_admin_bar_settings_wrap input[type="checkbox"]').on( 'change', function() {

            var name = $(this).attr('name');

            var hide_bar_role = false;
            if('hide_admin_bar_for_role' === name) {
                hide_bar_role = true;
            }

            if(true === $(this).prop("checked") ){
                $(this).val(1);
                if(hide_bar_role){
                    $('tr.is_hide_bar_using_user_role').show();
                    $('tr.is_hide_bar_using_custom_rule').hide();
                }
            } else{
                $(this).val(0);
                if(hide_bar_role){
                    $('tr.is_hide_bar_using_user_role').hide();
                    $('tr.is_hide_bar_using_custom_rule').show();
                }
            }

        } ).change();

        $(document).on('click','a.create-new-custom-rule',function () {

            var currentObj = $(this);
            var counter_id = currentObj.attr('data-counter_id');

            currentObj.append('<i class="fa fa-spinner fa-spin"></i>');

            var data = {
                'action': 'wpas_create_new_custom_rule',
                'counter_id': counter_id,
            };

            jQuery.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);

                $('.wpas-hide-bar-content ul').append(response.html);
                $('.rander-select2').select2();

                var rule_length = $('.wpas-hide-bar-content ul.rule-items').children().length;
                currentObj.attr('data-counter_id',rule_length);
                $('input#wpas_custom_rule_count').val(rule_length);
                currentObj.children('i.fa-spinner').remove();

            });
        });

        $(document).on('click','a.remove-custom-rule',function () {
            var currentObj = $(this);
            var remove_id = currentObj.attr('data-id');

            var removeRuleObj = $( '#admin_bar_custom_rule_'+remove_id );
            removeRuleObj.css('box-shadow','0 0 10px #ca1616')
            removeRuleObj.fadeOut( 1000, function() {
                removeRuleObj.remove();
                var rule_length = $('.wpas-hide-bar-content ul.rule-items').children().length;
                $('a.create-new-custom-rule').attr('data-counter_id',rule_length);
                $('input#wpas_custom_rule_count').val(rule_length);
                $( "ul.rule-items li.admin-bar-custom-rule-wrap" ).each(function( index ) {
                    var counter_id = index + 1;
                    $( this ).attr('id','admin_bar_custom_rule_'+counter_id);
                    $( this ).children('.current-rule-number').children().html(counter_id);
                    $( this ).children('.left-content').children('select').attr('name','custom_rule_post_type_'+counter_id);
                    $( this ).children('.left-content').children('select').attr('id','custom_rule_post_type_'+counter_id);
                    $( this ).children('.right-content').children('select').attr('name','custom_rule_post_page_'+counter_id+'[]');
                    $( this ).children('.right-content').children('select').attr('id','custom_rule_post_page_'+counter_id);
                    $( this ).children('.custom-rule-action').children('.remove-custom-rule').attr('data-id',counter_id);
                });
            });
        });

        $(document).on('change','select.custom-rule-post-type',function () {
            var currentObj = $(this);
            var current_id = currentObj.attr('data-id');
            var posttype = currentObj.val();
            currentObj.parent().append('<i class="fa fa-spinner fa-spin"></i>');

            var data = {
                'action': 'wpas_get_posts',
                'posttype': posttype,
            };

            jQuery.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);

                $('#custom_rule_post_page_'+current_id).html(response.options);

                setTimeout(function(){
                    currentObj.parent().children('i.fa-spinner').remove();
                }, 2000);

            });
        })

    });
}( jQuery, window ));