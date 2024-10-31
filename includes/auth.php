<?php

use Promo\API\Exception\PromoAPIException;
use Promo\API\Request\JWTRequest;
use Promo\DTO\ConsumerKeys;
use Promo\DTO\JWT;

function promo_video_maker_pre_login()
{
    if (promo_video_maker_is_logged_in()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-dashboard'));
        exit;
    }

    if (promo_video_maker_has_oauth_access_token()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login-success'));
        exit;
    }

    if (promo_video_maker_has_oauth_consumer()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-auth-start'));
        exit;
    }
}

function promo_video_maker_login()
{
    $returnUrl = promo_video_maker_get_page_url('promo-video-maker-auth-finalize');
    $loginUrl = promo_video_maker_get_authorization_url($returnUrl);
    promo_video_maker_load_js(__DIR__ . '/../app/build/main.bundle.js', [
        'nonce' => promo_video_maker_rest_create_nonce(),
        'configUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_CONFIG),
        'loginUrl' => $loginUrl,
    ]);
    promo_video_maker_load_css(__DIR__ . '/../app/build/main.bundle.css');
    echo promo_video_maker_get_twig()->render('app.html.twig');
}

function promo_video_maker_pre_login_success()
{
    if (!promo_video_maker_has_oauth_access_token()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login'));
        exit;
    }

    $jwt = promo_video_maker_regenerate_jwt();
    if (!$jwt) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login'));
        exit;
    }

    wp_redirect(promo_video_maker_get_page_url('promo-video-maker-dashboard'));
    exit;
}

function promo_video_maker_login_success()
{
}

function promo_video_maker_pre_auth_start()
{
    if (!promo_video_maker_has_oauth_consumer()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login'));
        exit;
    }

    $redirectUrl = promo_video_maker_get_page_url('promo-video-maker-auth-finalize');
    wp_redirect(promo_video_maker_get_auth_code_url($redirectUrl));
    exit;
}

function promo_video_maker_auth_start()
{
}

function promo_video_maker_pre_auth_finalize()
{
    if (!promo_video_maker_has_oauth_consumer()) {
        wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login'));
        exit;
    }

    if (!isset($_GET['code'])) {
        $url = add_query_arg(['error' => 1], promo_video_maker_get_page_url('promo-video-maker-oauth-settings'));
        wp_redirect($url);
        exit;
    }

    $code = $_GET['code'];
    $oauthApi = promo_video_maker_new_oauth_api();
    try {
        $accessToken = $oauthApi->generateAccessToken(
            promo_video_maker_get_consumer_keys(),
            $code,
            promo_video_maker_get_page_url('promo-video-maker-auth-finalize')
        );
    } catch (PromoAPIException $e) {
        promo_video_maker_log_exception($e, __METHOD__);
        $url = add_query_arg(['error' => 1], promo_video_maker_get_page_url('promo-video-maker-oauth-settings'));
        wp_redirect($url);
        exit;
    }

    update_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY, $accessToken);
    wp_redirect(promo_video_maker_get_page_url('promo-video-maker-login-success'));
    exit;
}

function promo_video_maker_auth_finalize()
{
}

function promo_video_maker_pre_auth_refresh()
{
    if (empty($_GET['redirectUrl'])) {
        wp_die('Bad request', 400);
    }

    $redirectUrl = $_GET['redirectUrl'];
    $baseUrl = get_option(PROMO_VIDEO_MAKER_SETTING_FE_URL);
    if (substr($redirectUrl, 0, strlen($baseUrl)) !== $baseUrl) {
        wp_die('Bad request', 400);
    }

    $returnUrl = add_query_arg(
        [
            'nonce' => promo_video_maker_rest_create_nonce(),
            'refreshUrl' => promo_video_maker_get_page_url('promo-video-maker-auth-refresh'),
            'jwtUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_JWT),
            'configUrl' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_CONFIG),
        ],
        $redirectUrl
    );
    wp_redirect($returnUrl);
    exit;
}

function promo_video_maker_auth_refresh()
{
}

function promo_video_maker_is_logged_in(): bool
{
    $jwt = promo_video_maker_get_jwt_storage()->getJWT();

    return $jwt && !$jwt->isExpired();
}

function promo_video_maker_has_oauth_access_token(): bool
{
    return !empty(get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY));
}

function promo_video_maker_has_oauth_consumer(): bool
{
    return !empty(get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY))
        && !empty(get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET));
}

function promo_video_maker_get_authorization_url(string $redirectUrl): string
{
    $query = [
        'partnership' => 'wordpress',
        'redirect_url' => $redirectUrl,
    ];

    return add_query_arg($query, get_option(PROMO_VIDEO_MAKER_SETTING_REGISTRATION_URL));
}

function promo_video_maker_get_auth_code_url(string $redirectUrl): string
{
    $consumerKeys = promo_video_maker_get_consumer_keys();
    if (!$consumerKeys) {
        wp_die('cannot generate auth code URL without OAuth consumer keys');
    }

    $query = [
        'client_id' => $consumerKeys->getConsumerKey(),
        'redirect_uri' => $redirectUrl,
        'response_type' => 'code',
        'scope' => 'wp',
    ];

    return sprintf('%s/v1/oauth/authorize?%s', get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL), http_build_query($query));
}

function promo_video_maker_get_consumer_keys(): ?ConsumerKeys
{
    if (!promo_video_maker_has_oauth_consumer()) {
        return null;
    }

    return new ConsumerKeys(
        get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY),
        get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET)
    );
}

function promo_video_maker_update_consumer_keys(ConsumerKeys $consumerKeys): void
{
    update_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_KEY, $consumerKeys->getConsumerKey());
    update_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_CONSUMER_SECRET, $consumerKeys->getConsumerSecret());

    if ($consumerKeys->areEmpty()) {
        update_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY, '');
        promo_video_maker_get_jwt_storage()->clear();
    }
}

function promo_video_maker_regenerate_jwt(): ?JWT
{
    $authApi = promo_video_maker_new_auth_api();
    $accessKey = get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY);

    try {
        $jwt = $authApi->getJWT(new JWTRequest($accessKey));
    } catch (PromoAPIException $e) {
        update_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_ACCESS_KEY, '');
        promo_video_maker_log_exception($e, __METHOD__);

        return null;
    }

    $jwtStorage = promo_video_maker_get_jwt_storage();
    $jwtStorage->store($jwt);

    return $jwt;
}

function promo_video_maker_pre_auth_regenerate_cookie()
{
    $user = wp_get_current_user();
    if (!$user->ID) {
        wp_die('User is not logged in', 400);
    }

    promo_video_maker_rest_set_auth_cookie($user->user_login, $user);
    wp_safe_redirect($_GET['return_url'] ?? promo_video_maker_get_page_url('promo-video-maker-login'));
    exit;
}

function promo_video_maker_auth_regenerate_cookie()
{
}
