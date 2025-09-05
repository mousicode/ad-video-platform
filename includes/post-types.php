<?php
defined('ABSPATH') || exit;

add_action('init', function () {
    register_post_type('avp_video', [
        'labels' => [
            'name' => 'Ad Videos',
            'singular_name' => 'Ad Video',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'ad-videos'],
        'supports' => ['title', 'editor', 'thumbnail', 'author', 'page-attributes'],
        'menu_icon' => 'dashicons-video-alt3',
        'show_in_rest' => true,
    ]);
});
