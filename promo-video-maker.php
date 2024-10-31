<?php

/**
 * Plugin Name: Promo Video Maker
 * Description: #1 video creation platform for businesses and agencies. Built to help users create powerful visual content to promote anything effectively.
 * Version: 1.1.2
 * Requires PHP: 7.2
 * Author: Promo.com - The #1 video creation platform
 * Author URI: https://promo.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 * Copyright 2020 Promo.com
 *
 * Promo Video Maker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Promo Video Maker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Promo Video Maker. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

if (!defined('WPINC')) {
    die;
}

if (!defined('PROMO_VIDEO_MAKER_DEBUG')) {
    define('PROMO_VIDEO_MAKER_DEBUG', false);
}

if (!defined('PROMO_VIDEO_MAKER_VERSION')) {
    define('PROMO_VIDEO_MAKER_VERSION', '1.1.2');
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/rest.php';
require_once __DIR__ . '/includes/dashboard.php';

const PROMO_VIDEO_MAKER_MENU_ICON = 'https://promo.com/favicons/favicon16x16.png';

add_action('admin_init', 'promo_video_maker_register_settings');
add_action('admin_menu', 'promo_video_maker_menu');
add_action('wp_logout', 'promo_video_maker_clear_jwt');
add_action('rest_api_init', 'promo_video_maker_register_rest_routes');
add_action('rest_api_init', 'promo_video_maker_rest_setup_cors');
add_action('wp_login', 'promo_video_maker_rest_set_auth_cookie', 10, 2);
add_action('wp_logout', 'promo_video_maker_rest_clear_auth_cookie');
register_activation_hook(__FILE__, 'promo_video_maker_activate');
register_deactivation_hook(__FILE__, 'promo_video_maker_deactivate');
register_uninstall_hook(__FILE__, 'promo_video_maker_uninstall');

function promo_video_maker_menu()
{
    $loginHookSuffix = add_menu_page(
        'Promo',
        'Promo',
        'administrator',
        'promo-video-maker-login',
        'promo_video_maker_login',
        PROMO_VIDEO_MAKER_MENU_ICON
    );
    promo_video_maker_add_load_action_hook($loginHookSuffix, 'promo_video_maker_pre_login');
    $dashboardHookSuffix = add_submenu_page(
        null,
        'Promo - My Videos',
        'My Videos',
        'administrator',
        '/promo-video-maker-dashboard',
        'promo_video_maker_dashboard_page'
    );
    promo_video_maker_add_load_action_hook($dashboardHookSuffix, 'promo_video_maker_pre_dashboard');
    $authStartHookSuffix = add_submenu_page(
        null,
        'Authorization',
        '',
        'administrator',
        '/promo-video-maker-auth-start',
        'promo_video_maker_auth_start'
    );
    promo_video_maker_add_load_action_hook($authStartHookSuffix, 'promo_video_maker_pre_auth_start');
    $authFinalizeHookSuffix = add_submenu_page(
        null,
        'Authorization',
        '',
        'administrator',
        '/promo-video-maker-auth-finalize',
        'promo_video_maker_auth_finalize'
    );
    promo_video_maker_add_load_action_hook($authFinalizeHookSuffix, 'promo_video_maker_pre_auth_finalize');
    $loginSuccessHookSuffix = add_submenu_page(
        null,
        'Login successful',
        '',
        'administrator',
        '/promo-video-maker-login-success',
        'promo_video_maker_login_success'
    );
    promo_video_maker_add_load_action_hook($loginSuccessHookSuffix, 'promo_video_maker_pre_login_success');
    $authSettingsHookSuffix = add_submenu_page(
        'promo-video-maker-login',
        'Authentication Settings',
        'Settings',
        'administrator',
        '/promo-video-maker-oauth-settings',
        'promo_video_maker_oauth_settings_page'
    );
    promo_video_maker_add_load_action_hook($authSettingsHookSuffix, 'promo_video_maker_pre_oauth_settings_page');
    $refreshNonceHookSuffix = add_submenu_page(
        null,
        'Refresh',
        '',
        'administrator',
        '/promo-video-maker-auth-refresh',
        'promo_video_maker_auth_refresh'
    );
    promo_video_maker_add_load_action_hook($refreshNonceHookSuffix, 'promo_video_maker_pre_auth_refresh');
    $regenerateAuthCookieHookSuffix = add_submenu_page(
        null,
        'Regenerate auth cookie',
        '',
        'administrator',
        '/promo-video-maker-auth-regenerate-cookie',
        'promo_video_maker_auth_regenerate_cookie'
    );
    promo_video_maker_add_load_action_hook($regenerateAuthCookieHookSuffix, 'promo_video_maker_pre_auth_regenerate_cookie');
    if (PROMO_VIDEO_MAKER_DEBUG) {
        add_submenu_page(
            'promo-video-maker-login',
            'Debug Settings',
            'Debug Settings',
            'administrator',
            '/promo-video-maker-debug-settings',
            'promo_video_maker_debug_settings_page'
        );
    }
}

function promo_video_maker_activate()
{
    $user = wp_get_current_user();
    if (!$user->ID) {
        return;
    }

    promo_video_maker_rest_set_auth_cookie($user->user_login, $user);
}

function promo_video_maker_deactivate()
{
    promo_video_maker_clear_jwt();
    promo_video_maker_rest_clear_auth_cookie();
}

function promo_video_maker_uninstall()
{
    promo_video_maker_delete_settings();
    promo_video_maker_clear_jwt();
}
