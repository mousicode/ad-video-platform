<?php
defined('ABSPATH') || exit;

/** helper: آیا کاربر این ویدیو را پاس کرده؟ */
function avp_user_completed($user_id, $post_id) {
    return (bool) get_user_meta($user_id, "avp_completed_$post_id", true);
}

/** helper: ویدیوی بعدی (بر اساس تاریخ یا menu_order) */
function avp_get_next_video_id($post_id) {
    $args = [
        'post_type'      => 'avp_video',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ];
    $ids = get_posts($args);
    $i = array_search($post_id, $ids, true);
    if ($i === false) return 0;
    return $ids[$i+1] ?? 0;
}

/** محدود کردن دسترسی اگر قبلی پاس نشده (اختیاری/ساده) */
add_action('template_redirect', function () {
    if (!is_singular('avp_video')) return;
    $post = get_post();
    $current_id = $post->ID;

    // اگر اولین ویدیو است، اجازه بده
    $args = ['post_type'=>'avp_video','posts_per_page'=>1,'orderby'=>'date','order'=>'ASC','fields'=>'ids'];
    $first = get_posts($args);
    $first_id = $first ? $first[0] : 0;
    if ($current_id === $first_id) return;

    // اگر لاگین نیست، هدایت به لاگین
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(get_permalink())); exit;
    }

    // ویدیوی قبلی را پیدا کن و چک کن پاس شده یا نه
    $all = get_posts(['post_type'=>'avp_video','posts_per_page'=>-1,'orderby'=>'date','order'=>'ASC','fields'=>'ids']);
    $idx = array_search($current_id, $all, true);
    if ($idx > 0) {
        $prev_id = $all[$idx-1];
        $done = avp_user_completed(get_current_user_id(), $prev_id);
        if (!$done) {
            wp_die('برای مشاهده این ویدیو باید ویدیوی قبلی را کامل مشاهده کرده و آزمون آن را پاس کنید.');
        }
    }
});

/** شورت‌کد نمایش ویدیو + آزمون */
add_shortcode('avp_video_quiz', function () {
    if (!is_singular('avp_video')) return '';

    global $post;
    $video_id = (int) get_post_meta($post->ID, '_avp_video_id', true);
    $video = $video_id ? wp_get_attachment_url($video_id) : '';
    $q     = get_post_meta($post->ID, '_avp_quiz_question', true);
    $opts  = [];
    for ($i=1; $i<=4; $i++) $opts[$i] = get_post_meta($post->ID, "_avp_option_$i", true);
    $completed = is_user_logged_in() ? avp_user_completed(get_current_user_id(), $post->ID) : false;

    ob_start(); ?>
    <div class="avp-wrap">
        <?php if (!$video): ?>
            <p style="color:red">⛔ ویدیو برای این پست تنظیم نشده.</p>
        <?php else: ?>
            <video id="avp-video" width="800" controls preload="metadata" data-post="<?php echo esc_attr($post->ID); ?>">
                <source src="<?php echo esc_url($video); ?>" type="video/mp4">
                مرورگر شما از ویدیو پشتیبانی نمی‌کند.
            </video>
        <?php endif; ?>

        <div id="avp-quiz" style="display:none;margin-top:16px;">
            <h3><?php echo esc_html($q ?: ''); ?></h3>
            <form id="avp-quiz-form">
                <?php foreach ($opts as $i=>$text): if(!$text) continue; ?>
                <label style="display:block;margin-bottom:6px;">
                    <input type="radio" name="answer" value="<?php echo (int)$i; ?>"> <?php echo esc_html($text); ?>
                </label>
                <?php endforeach; ?>
                <button type="submit" class="button button-primary">ثبت پاسخ</button>
            </form>
            <div id="avp-quiz-result" style="margin-top:10px;"></div>
        </div>

        <div id="avp-next" style="display:none;margin-top:16px;"></div>
    </div>
    <?php
    return ob_get_clean();
});

/** هندل AJAX ارسال آزمون + کسر بودجه */
add_action('wp_ajax_avp_submit_quiz', 'avp_submit_quiz');
add_action('wp_ajax_nopriv_avp_submit_quiz', 'avp_submit_quiz');

function avp_submit_quiz() {
    check_ajax_referer('avp_quiz_nonce', 'nonce');

    $post_id = intval($_POST['post_id'] ?? 0);
    $answer  = intval($_POST['answer'] ?? 0);

    if (!$post_id || !$answer) wp_send_json_error(['msg'=>'درخواست نامعتبر است.']);

    $correct = (int) get_post_meta($post_id, '_avp_correct_option', true);
    if ($answer !== $correct) {
        wp_send_json_success(['ok'=>false, 'msg'=>'❌ پاسخ اشتباه است.']);
    }

    // پاسخ صحیح → ثبت پاس و کسر بودجه در صورت عدم تکرار
    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_success(['ok'=>true,'msg'=>'✅ پاسخ صحیح. (برای ذخیره پیشرفت نیاز به ورود دارید)']);

    // اگر قبلا پاس کرده بود، دوباره شارژ نکن
    $already = avp_user_completed($user_id, $post_id);
    if (!$already) {
        update_user_meta($user_id, "avp_completed_$post_id", 1);

        $budget = (int) get_post_meta($post_id, '_avp_budget', true);
        $cpv    = (int) get_post_meta($post_id, '_avp_cost_per_view', true);
        $spent  = (int) get_post_meta($post_id, '_avp_spent', true);

        if ($budget >= $cpv && $cpv > 0) {
            $budget -= $cpv;
            $spent  += $cpv;
            update_post_meta($post_id, '_avp_budget', $budget);
            update_post_meta($post_id, '_avp_spent', $spent);
        }
    }

    $next_id = avp_get_next_video_id($post_id);
    $next_link = $next_id ? get_permalink($next_id) : '';

    wp_send_json_success([
        'ok'   => true,
        'msg'  => '✅ پاسخ صحیح ثبت شد. ویدیوی بعدی باز شد.',
        'next' => $next_link,
    ]);
}
