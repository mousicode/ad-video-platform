<?php
/*
Plugin Name: Ad Video Platform
Description: Video ads with budget deduction, quiz-gate, and role management.
Version: 1.0.0
Author: karasu
*/

defined('ABSPATH') || exit;

// ===== INCLUDES =====
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/roles.php';
require_once plugin_dir_path(__FILE__) . 'includes/video-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/quiz-functions.php';


// ===== FRONTEND Assets =====
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('avp-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0');
    wp_enqueue_script('avp-video-lock', plugin_dir_url(__FILE__) . 'assets/js/video-lock.js', ['jquery'], '1.0', true);
    wp_enqueue_script('avp-frontend', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', ['jquery'], '1.0', true);

    wp_localize_script('avp-frontend', 'AVP', [
        'ajax'  => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('avp_quiz_nonce'),
    ]);
});


// ===== ADMIN Scripts (Media uploader + video selection) =====
add_action('admin_enqueue_scripts', function () {
    // اطمینان از بارگذاری اسکریپت‌های مربوط به مدیا
    wp_enqueue_media();
    wp_enqueue_script('jquery');

    // بررسی اینکه در حال ویرایش نوع پست avp_video هستیم
    $screen = get_current_screen();
    if (!isset($screen->post_type) || $screen->post_type !== 'avp_video') {
        return;
    }

    // بارگذاری فایل جاوااسکریپت مخصوص مدیریت آپلود ویدیو
    wp_enqueue_script(
        'avp-admin-video',
        plugin_dir_url(__FILE__) . 'assets/js/admin-video.js',
        ['jquery', 'media-editor', 'media-views'],
        '1.0',
        true
    );
});


// ===== Plugin Activation / Deactivation =====
register_activation_hook(__FILE__, 'avp_add_custom_roles');
register_deactivation_hook(__FILE__, 'avp_remove_custom_roles');
