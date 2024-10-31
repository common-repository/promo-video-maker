<?php

use Promo\API\AuthAPI;
use Promo\API\BaseGuzzleClient;
use Promo\API\GuzzleAuthAPI;
use Promo\JWTStorage\JWTStorage;
use Promo\API\GuzzleOAuthAPI;
use Promo\API\OAuthAPI;
use Promo\JWTStorage\TransientJWTStorage;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function promo_video_maker_new_oauth_api(): OAuthAPI
{
    $client = BaseGuzzleClient::withParams(get_option(PROMO_VIDEO_MAKER_SETTING_OAUTH_API_URL), PROMO_VIDEO_MAKER_DEBUG);

    return new GuzzleOAuthAPI($client);
}

function promo_video_maker_new_auth_api(): AuthAPI
{
    $client = BaseGuzzleClient::withParams(get_option(PROMO_VIDEO_MAKER_SETTING_AUTH_API_URL), PROMO_VIDEO_MAKER_DEBUG);

    return new GuzzleAuthAPI($client);
}

function promo_video_maker_get_twig(): Environment
{
    $loader = new FilesystemLoader(__DIR__ . '/../views');

    return new Environment($loader, [
        'debug' => PROMO_VIDEO_MAKER_DEBUG,
    ]);
}

function promo_video_maker_get_jwt_storage(): JWTStorage
{
    return new TransientJWTStorage();
}

function promo_video_maker_get_page_url(string $page): string
{
    return add_query_arg('page', $page, admin_url('admin.php'));
}

function promo_video_maker_log_exception(\Throwable $e, ?string $context)
{
    $message = sprintf('An exception occurred in Promo Video Maker plugin, exception message: %s', $e->getMessage());

    if ($e->getPrevious()) {
        $message .= sprintf(', previous exception: %s', $e->getPrevious()->getMessage());
    }

    if ($context) {
        $message .= sprintf(', context: %s', $context);
    }

    error_log($message);
}

function promo_video_maker_load_js(string $filePath, array $vars = []): void
{
    $info = pathinfo($filePath);
    if (!isset($info['extension']) || $info['extension'] != 'js') {
        return;
    }

    wp_enqueue_script($info['filename'], plugin_dir_url($filePath) . $info['basename'], [], PROMO_VIDEO_MAKER_VERSION, true);
    if ($vars) {
        wp_localize_script($info['filename'], 'promoVars', $vars);
    }
}

function promo_video_maker_load_css(string $filePath): void
{
    $info = pathinfo($filePath);
    if (!isset($info['extension']) || $info['extension'] != 'css') {
        return;
    }

    wp_enqueue_style($info['filename'], plugin_dir_url($filePath) . $info['basename'], [], PROMO_VIDEO_MAKER_VERSION);
}

function promo_video_maker_add_load_action_hook($hookSuffix, callable $action)
{
    if ($hookSuffix === false) {
        return;
    }

    add_action(sprintf('load-%s', $hookSuffix), $action);
}

function promo_video_maker_clear_jwt()
{
    promo_video_maker_get_jwt_storage()->clear();
}
