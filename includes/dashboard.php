<?php

function promo_video_maker_pre_dashboard()
{
    if (!promo_video_maker_is_logged_in()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login'));
        exit;
    }

    if (!promo_video_maker_parse_auth_cookie()) {
        $regenerateCookieUrl = add_query_arg(
            ['return_url' => promo_video_maker_get_page_url('promo-video-maker-dashboard')],
            promo_video_maker_get_page_url('promo-video-maker-auth-regenerate-cookie')
        );
        wp_redirect($regenerateCookieUrl);
        exit;
    }
}

function promo_video_maker_dashboard_page()
{
    echo promo_video_maker_get_twig()->render('app.html.twig');

    promo_video_maker_load_js(__DIR__ . '/../app/build/main.bundle.js', [
        'nonce' => promo_video_maker_rest_create_nonce(),
        'apiUrl' => get_option(PROMO_VIDEO_MAKER_SETTING_BE_API_URL),
        'jwtUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_JWT),
        'appHost' => get_option(PROMO_VIDEO_MAKER_SETTING_BASE_URL),
        'appPrefix' => parse_url(get_option(PROMO_VIDEO_MAKER_SETTING_FE_URL), PHP_URL_PATH),
        'refreshUrl' => promo_video_maker_get_page_url('promo-video-maker-auth-refresh'),
        'photosUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_PHOTOS),
        'configUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_CONFIG),
    ]);
    promo_video_maker_load_css(__DIR__ . '/../app/build/main.bundle.css');
}
