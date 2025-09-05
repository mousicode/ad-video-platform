<?php
defined('ABSPATH') || exit;

/** متاباکس بودجه و آزمون **/
add_action('add_meta_boxes', function () {
    add_meta_box('avp_video_budget', 'تنظیمات بودجه و آزمون', 'avp_video_meta_callback', 'avp_video', 'normal', 'default');
    add_meta_box('avp_video_url', 'ویدیو', 'avp_video_url_callback', 'avp_video', 'side', 'default');
});

function avp_video_meta_callback($post) {
    wp_nonce_field('avp_video_meta', 'avp_video_meta_nonce');

    $budget        = get_post_meta($post->ID, '_avp_budget', true);
    $cpv           = get_post_meta($post->ID, '_avp_cost_per_view', true);
    $question      = get_post_meta($post->ID, '_avp_quiz_question', true);
    $opt1          = get_post_meta($post->ID, '_avp_option_1', true);
    $opt2          = get_post_meta($post->ID, '_avp_option_2', true);
    $opt3          = get_post_meta($post->ID, '_avp_option_3', true);
    $opt4          = get_post_meta($post->ID, '_avp_option_4', true);
    $correct       = get_post_meta($post->ID, '_avp_correct_option', true);
    ?>
    <p><label>بودجه کل (تومان):</label><br><input type="number" name="avp_budget" value="<?php echo esc_attr($budget); ?>"></p>
    <p><label>هزینه هر بازدید کامل (تومان):</label><br><input type="number" name="avp_cost_per_view" value="<?php echo esc_attr($cpv); ?>"></p>
    <hr>
    <p><label>سوال آزمون:</label><br><input type="text" name="avp_quiz_question" style="width:100%" value="<?php echo esc_attr($question); ?>"></p>
    <p><label>گزینه 1:</label><br><input type="text" name="avp_option_1" style="width:100%" value="<?php echo esc_attr($opt1); ?>"></p>
    <p><label>گزینه 2:</label><br><input type="text" name="avp_option_2" style="width:100%" value="<?php echo esc_attr($opt2); ?>"></p>
    <p><label>گزینه 3:</label><br><input type="text" name="avp_option_3" style="width:100%" value="<?php echo esc_attr($opt3); ?>"></p>
    <p><label>گزینه 4:</label><br><input type="text" name="avp_option_4" style="width:100%" value="<?php echo esc_attr($opt4); ?>"></p>
    <p><label>گزینه صحیح (1 تا 4):</label><br><input type="number" min="1" max="4" name="avp_correct_option" value="<?php echo esc_attr($correct); ?>"></p>
    <?php
}
    <?php
}

add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['avp_video_meta_nonce']) && wp_verify_nonce($_POST['avp_video_meta_nonce'], 'avp_video_meta')) {
        update_post_meta($post_id, '_avp_budget', sanitize_text_field($_POST['avp_budget'] ?? ''));
        update_post_meta($post_id, '_avp_cost_per_view', sanitize_text_field($_POST['avp_cost_per_view'] ?? ''));
        update_post_meta($post_id, '_avp_quiz_question', sanitize_text_field($_POST['avp_quiz_question'] ?? ''));
        for ($i=1; $i<=4; $i++) {
            update_post_meta($post_id, "_avp_option_$i", sanitize_text_field($_POST["avp_option_$i"] ?? ''));
        }
        update_post_meta($post_id, '_avp_correct_option', intval($_POST['avp_correct_option'] ?? 0));
    }

    if (isset($_POST['avp_video_url_nonce']) && wp_verify_nonce($_POST['avp_video_url_nonce'], 'avp_video_url_nonce_action')) {
    }
});
