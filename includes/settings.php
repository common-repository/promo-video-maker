<?php

const PROMO_VIDEO_MAKER_SETTING_GROUP_OAUTH = 'promo-video-maker-oauth';
const PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL = 'promo-video-maker-internal';
const PROMO_VIDEO_MAKER_DEFAULT_BASE_URL = 'https://promo.com'; // todo move to env
const PROMO_VIDEO_MAKER_SETTING_BASE_URL = 'promo_video_maker_api_base_url';
const PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL = 'promo_video_maker_registration_url';
const PROMO_VIDEO_MAKER_SETTING_BE_API_URL = 'promo_video_maker_be_api_url';
const PROMO_VIDEO_MAKER_SETTING_FE_URL = 'promo_video_maker_fe_url';
const PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL = 'promo_video_maker_oauth_api_url';
const PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL = 'promo_video_maker_auth_api_url';
const PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY = 'promo_video_maker_oauth_consumer_key';
const PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET = 'promo_video_maker_oauth_consumer_secret';
const PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY = 'promo_video_maker_oauth_access_key';

function promo_video_maker_register_settings()
{
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_BASE_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL]
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL . '/signup']
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_BE_API_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL . '/shopify-backend']
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_FE_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL . '/services/wp']
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL . '/services/oauth/ext']
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL,
        ['default' => PROMO_VIDEO_MAKER_DEFAULT_BASE_URL . '/services/auth/ext']
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY,
        ['show_in_rest' => false]
    );

    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_OAUTH,
        PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY,
        ['show_in_rest' => false]
    );
    register_setting(
        PROMO_VIDEO_MAKER_SETTING_GROUP_OAUTH,
        PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET,
        ['show_in_rest' => false]
    );
}

function promo_video_maker_delete_settings()
{
    delete_option(PROMO_VIDEO_MAKER_SETTING_BASE_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_BE_API_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_FE_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL);
    delete_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY);
    delete_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET);
    delete_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY);
}

function promo_video_maker_pre_oauth_settings_page()
{
    if (!promo_video_maker_parse_auth_cookie()) {
        $regenerateCookieUrl = add_query_arg(
            ['return_url' => promo_video_maker_get_page_url('promo-video-maker-oauth-settings')],
            promo_video_maker_get_page_url('promo-video-maker-auth-regenerate-cookie')
        );
        wp_redirect($regenerateCookieUrl);
        exit;
    }
}

function promo_video_maker_oauth_settings_page()
{
    $returnUrl = promo_video_maker_get_page_url('promo-video-maker-auth-finalize');
    $loginUrl = promo_video_maker_get_authorization_url($returnUrl);
    promo_video_maker_load_js(__DIR__ . '/../app/build/main.bundle.js', [
        'nonce' => promo_video_maker_rest_create_nonce(),
        'configUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_CONFIG),
        'customerKey' => get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY),
        'customerSecret' => get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET),
        'customerKeysRedirect' => promo_video_maker_get_page_url('promo-video-maker-auth-start'),
        'authError' => !empty($_GET['error']),
        'loginUrl' => $loginUrl,
    ]);
    promo_video_maker_load_css(__DIR__ . '/../app/build/main.bundle.css');
    echo promo_video_maker_get_twig()->render('app.html.twig');
}

function promo_video_maker_debug_settings_page()
{
    if (!PROMO_VIDEO_MAKER_DEBUG) {
        wp_die('Not found');
    }

    promo_video_maker_render_settings_form(
        PROMO_VIDEO_MAKER_SETTING_GROUP_INTERNAL,
        [
            PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY => [
                'name' => 'OAuth access key',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY),
            ],
            PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL => [
                'name' => 'Promo registration URL',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL),
            ],
            PROMO_VIDEO_MAKER_SETTING_BE_API_URL => [
                'name' => 'Promo BE API URL',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_BE_API_URL),
            ],
            PROMO_VIDEO_MAKER_SETTING_FE_URL => [
                'name' => 'Promo FE URL',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_FE_URL),
            ],
            PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL => [
                'name' => 'Promo OAuth API URL',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL),
            ],
            PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL => [
                'name' => 'Promo Auth API URL',
                'value' => get_option(PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL),
            ],
        ]
    );
}

function promo_video_maker_render_settings_form(string $group, array $settings)
{
    echo promo_video_maker_get_twig()->render(
        'settings.html.twig',
        [
            'option_group' => $group,
            'nonce' => wp_create_nonce(sprintf('%s-options', $group)),
            'settings' => $settings,
        ]
    );
}
