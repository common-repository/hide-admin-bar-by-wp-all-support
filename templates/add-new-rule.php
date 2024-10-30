<?php

$current_id = !empty($args['current_id']) ? (int)$args['current_id'] : 0;
$current_id = $current_id + 1;
$rule = !empty($args['rule']) ? $args['rule'] : array();

$rule_post_type = !empty($rule['post_type']) ? $rule['post_type'] : 0;
$post_page_ids = !empty($rule['post_page_id']) ? $rule['post_page_id'] : array();

$post_status = apply_filters('wpas_hide_admin_bar_post_status',array('any'));

$args = array(
    'public'   => true,
);
$get_post_types = get_post_types($args);

$exclude_post_type = apply_filters('wpas_hide_admin_bar_exclude_post_type',array('attachment'));

$wrapper_id = !empty($current_id) ? '_'.$current_id : '';
$counter_id = $current_id;

?>
<li class="admin-bar-custom-rule-wrap" id="admin_bar_custom_rule<?php echo $wrapper_id; ?>">
    <div class="current-rule-number"><span><?php echo $counter_id; ?></span></div>
    <div class="left-content">
        <select name="custom_rule_post_type<?php echo $wrapper_id; ?>" id="custom_rule_post_type<?php echo $wrapper_id; ?>" class="rander-select2 regular-text custom-rule-post-type" data-id="<?php echo $current_id; ?>">
            <option value=""><?php _e('Please select post type to hide','wpas-hide-admin-bar'); ?></option>
            <?php
            if(!empty($get_post_types) && is_array($get_post_types)) {
                foreach ($get_post_types as $post_type ) {
                    if(!empty($exclude_post_type) && !in_array($post_type,$exclude_post_type)) {
                    ?>
                    <option <?php selected($rule_post_type,$post_type); ?> value="<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></option>
                    <?php
                    }
                }
            }
            ?>
        </select>
    </div>
    <div class="right-content">
        <select name="custom_rule_post_page<?php echo $wrapper_id; ?>[]" id="custom_rule_post_page<?php echo $wrapper_id; ?>" class="rander-select2 regular-text" multiple  data-placeholder="<?php _e('Please select item(s)','wpas-hide-admin-bar'); ?>">
            <?php
            if(!empty($rule_post_type) ) {
                $get_posts = get_posts(array(
                    'posts_per_page' => -1,
                    'post_type' => $rule_post_type,
                    'post_status' => $post_status,
                    'order' => 'DESC',
                    'orderby' => 'title',
                ));

                if(!empty($get_posts) && is_array($get_posts)) {
                    foreach ($get_posts as $key => $post ) {
                        $post_id = !empty($post->ID) ? $post->ID : 0;
                        $post_title = !empty($post->post_title) ? $post->post_title : 0;

                        $selected = '';
                        if(!empty($post_page_ids) && in_array($post_id,$post_page_ids)) {
                            $selected = selected($key,$key,false);
                        }
                        ?>
                        <option <?php echo $selected; ?> value="<?php echo $post_id; ?>"><?php echo $post_title; ?></option>
                        <?php
                    }
                }
            }
            ?>
        </select>
    </div>
    <div class="custom-rule-action">
        <a href="javascript:void(0);" class="remove-custom-rule" data-id="<?php echo $counter_id; ?>"><i class="fas fa-minus-circle"></i></a>
    </div>
</li>