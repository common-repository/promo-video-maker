<?php

use Promo\DTO\ConsumerKeys;
use Promo\DTO\PaginatedPhotos;
use Promo\DTO\Photo;

const PROMO_VIDEO_MAKER_REST_NAMESPACE = '/promo-video-maker/v1';
const PROMO_VIDEO_MAKER_REST_PATH_JWT = '/jwt';
const PROMO_VIDEO_MAKER_REST_PATH_PHOTOS = '/photos';
const PROMO_VIDEO_MAKER_REST_PATH_PHOTO = '/photo';
const PROMO_VIDEO_MAKER_REST_PATH_CONFIG = '/config';
const PROMO_VIDEO_MAKER_REST_PATH_CONFIG_CONSUMER_KEYS = '/config/consumer-keys';
const PROMO_VIDEO_MAKER_DEFAULT_PHOTOS_NUMBER = 20;
const PROMO_VIDEO_MAKER_MAX_PHOTOS_NUMBER = 250;
const PROMO_VIDEO_MAKER_REST_COOKIE_SCHEME = 'promo_video_maker_rest';
const PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME = 'wordpress_promo_video_maker_rest_' . COOKIEHASH;

function promo_video_maker_register_rest_routes()
{
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_JWT,
        [
            'methods' => 'GET',
            'callback' => 'promo_video_maker_rest_get_jwt',
            'args' => [
                'force' => [
                    'default' => false,
                    'validate_callback' => function ($param) {
                        $force = filter_var($param, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                        return null !== $force;
                    },
                    'sanitize_callback' => function ($param) {
                        return filter_var($param, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    },
                ],
            ],
            'summary' => 'Get Promo user JWT',
            'description' => 'Retrieve the Promo user WP-scoped JWT. It will be regenerated if not present or expired.',
            'produces' => 'application/json',
            'consumes' => 'application/json',
        ]
    );
    add_filter('swagger_api_responses_get_promo-video-maker_v1_jwt', function (): array {
        return [
            '200' => [
                'description' => 'OK',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'jwt' => [
                            'type' => 'string',
                            'example' => 'test_token',
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'User is not logged in',
            ]
        ];
    });
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_PHOTOS,
        [
            'methods' => 'GET',
            'callback' => 'promo_video_maker_rest_get_photos',
            'summary' => 'Retrieve photos from the gallery',
            'description' => 'The endpoint returns the list of all the photos in the WP gallery. It supports cursor-based pagination.',
            'produces' => 'application/json',
            'consumes' => 'application/json',
            'args' => [
                'limit' => [
                    'default' => PROMO_VIDEO_MAKER_DEFAULT_PHOTOS_NUMBER,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0 && $param <= PROMO_VIDEO_MAKER_MAX_PHOTOS_NUMBER;
                    },
                    'sanitize_callback' => function ($param) {
                        return intval($param);
                    },
                ],
                'cursor' => [
                    'default' => 1,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    },
                    'sanitize_callback' => function ($param) {
                        return intval($param);
                    },
                ],
            ],
        ]
    );
    add_filter('swagger_api_responses_get_promo-video-maker_v1_photos', function (): array {
        return [
            '200' => [
                'description' => 'OK',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'photos' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => [
                                        'type' => 'number',
                                        'example' => 18,
                                    ],
                                    'height' => [
                                        'type' => 'number',
                                        'example' => 1200,
                                    ],
                                    'width' => [
                                        'type' => 'number',
                                        'example' => 1200,
                                    ],
                                    'source' => [
                                        'type' => 'string',
                                        'example' => 'wordpress',
                                    ],
                                    'type' => [
                                        'type' => 'string',
                                        'example' => 'photo',
                                    ],
                                    'thumbnail_url' => [
                                        'type' => 'string',
                                        'example' => 'https://example.com/wp-content/uploads/image-150x150.png',
                                    ],
                                    'url' => [
                                        'type' => 'string',
                                        'example' => 'https://example.com/wp-content/uploads/image.png',
                                    ],
                                    'download_url' => [
                                        'type' => 'string',
                                        'example' => 'https://example.com/?id=18&rest_route=/promo-video-maker/v1/photo',
                                    ],
                                ],
                            ],
                        ],
                        'has_next_page' => [
                            'type' => 'boolean',
                            'example' => true,
                        ],
                        'has_previous_page' => [
                            'type' => 'boolean',
                            'example' => true,
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'User is not logged in',
            ],
        ];
    });
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_CONFIG,
        [
            'methods' => 'GET',
            'callback' => 'promo_video_maker_rest_get_config',
            'summary' => 'Get configuration for the FE app',
            'description' => 'The endpoint returns the list of available plugin URLs required to perform actions from within the WP instance.',
            'produces' => 'application/json',
            'consumes' => 'application/json',
        ]
    );
    add_filter('swagger_api_responses_get_promo-video-maker_v1_config', function (): array {
        return [
            '200' => [
                'description' => 'OK',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'jwt_url' => [
                            'type' => 'string',
                            'example' => 'https://example.com/?rest_route=/promo-video-maker/v1/jwt',
                        ],
                        'photos_url' => [
                            'type' => 'string',
                            'example' => 'https://example.com/?rest_route=/promo-video-maker/v1/jwt',
                        ],
                        'refresh_url' => [
                            'type' => 'string',
                            'example' => 'https://example.com/?rest_route=/promo-video-maker/v1/jwt',
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'User is not logged in',
            ]
        ];
    });
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_CONFIG_CONSUMER_KEYS,
        [
            'methods' => 'GET',
            'callback' => 'promo_video_maker_rest_get_consumer_keys',
            'summary' => 'Retrieve OAuth consumer key and secret',
            'description' => 'The endpoint returns consumer key and secret, the response will be empty if the keys are not set.',
            'produces' => 'application/json',
            'consumes' => 'application/json',
        ]
    );
    add_filter('swagger_api_responses_get_promo-video-maker_v1_config_consumer-keys', function (): array {
        return [
            '200' => [
                'description' => 'OK',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'consumer_key' => [
                            'type' => 'string',
                            'example' => 'test_key',
                        ],
                        'consumer_secret' => [
                            'type' => 'string',
                            'example' => 'test_secret',
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'User is not logged in',
            ]
        ];
    });
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_CONFIG_CONSUMER_KEYS,
        [
            'methods' => 'PUT',
            'callback' => 'promo_video_maker_rest_update_consumer_keys',
            'summary' => 'Update OAuth consumer key and secret',
            'description' => 'The endpoint allows updating OAuth consumer key and secret. '.
                'When empty strings are passed the keys will be unset along with related user credentials.',
            'produces' => 'application/json',
            'consumes' => 'application/json',
            'args' => [
                'consumer_key' => [
                    'required' => true,
                ],
                'consumer_secret' => [
                    'required' => true,
                ],
            ],
        ]
    );
    add_filter('swagger_api_responses_put_promo-video-maker_v1_config_consumer-keys', function (): array {
        return [
            '200' => [
                'description' => 'Keys are updated, the response contains the current OAuth keys',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'consumer_key' => [
                            'type' => 'string',
                            'example' => 'test_key'
                        ],
                        'consumer_secret' => [
                            'type' => 'string',
                            'example' => 'test_secret',
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'User is not logged in',
            ]
        ];
    });
    register_rest_route(
        PROMO_VIDEO_MAKER_REST_NAMESPACE,
        PROMO_VIDEO_MAKER_REST_PATH_PHOTO,
        [
        'methods' => 'GET',
        'callback' => 'promo_video_maker_rest_get_photo',
        'summary' => 'Get single photo contents',
        'description' => 'The endpoint returns a photo as an inline attachment and with appropriate photo\'s mime type.',
        'produces' => 'image/*',
        'consumes' => 'application/json',
        'args' => [
            'id' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
                'sanitize_callback' => function ($param) {
                    return intval($param);
                },
            ],
        ],
    ]
    );
    add_filter('rest_pre_serve_request', function (bool $served, WP_HTTP_Response $result, WP_REST_Request $request): bool {
        if ($served) {
            return true;
        }

        if (!promo_video_maker_rest_verify_namespace()) {
            return false;
        }

        $attributes = $request->get_attributes();
        if (!isset($attributes['callback'])) {
            return false;
        }

        if ($attributes['callback'] !== 'promo_video_maker_rest_get_photo') {
            return false;
        }

        // data contains the file contents
        echo $result->get_data();

        return true;
    }, 10, 3);

    add_filter('rest_authentication_errors', function ($result) {
        if (!promo_video_maker_rest_verify_namespace()) {
            return $result;
        }

        if (empty($_SERVER['HTTP_X_WP_NONCE'])) {
            return new WP_Error('rest_cookie_invalid_nonce', __('Cookie nonce is invalid'), ['status' => 403]);
        }

        $nonceResult = promo_video_maker_rest_verify_nonce($_SERVER['HTTP_X_WP_NONCE']);

        if (!$nonceResult) {
            return new WP_Error('rest_cookie_invalid_nonce', __('Cookie nonce is invalid'), ['status' => 403]);
        }

        return true;
    }, 1, 1);
}

if (promo_video_maker_rest_verify_namespace()) {
    add_filter('determine_current_user', function () {
        ['username' => $username, 'expiration' => $expiration, 'token' => $token, 'hmac' => $hmac] = promo_video_maker_parse_auth_cookie();

        if ($expiration < time()) {
            return false;
        }

        $user = get_user_by('login', $username);
        if (!$user) {
            return false;
        }

        $pass_frag = substr($user->user_pass, 8, 4);
        $key = wp_hash($username . '|' . $pass_frag . '|' . $expiration . '|' . $token, PROMO_VIDEO_MAKER_REST_COOKIE_SCHEME);
        $hash = hash_hmac('sha256', $username . '|' . $expiration . '|' . $token, $key);

        if (!hash_equals($hash, $hmac)) {
            return false;
        }

        $manager = WP_Session_Tokens::get_instance($user->ID);
        if (!$manager->verify($token)) {
            return false;
        }

        return $user->ID;
    }, 100);
}

function promo_video_maker_rest_setup_cors()
{
    // newer WP version allows the nonce header by default
    if (has_filter('rest_allowed_cors_headers')) {
        return;
    }

    add_filter('rest_pre_serve_request', function () {
        header('Access-Control-Allow-Headers: ' . implode(', ', [
           'Authorization',
           'X-WP-Nonce',
           'Content-Disposition',
           'Content-MD5',
           'Content-Type',
       ]));
    });
}

function promo_video_maker_rest_get_jwt(WP_REST_Request $request)
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    $force = $request->get_param('force');
    $jwtStorage = promo_video_maker_get_jwt_storage();
    $jwt = $jwtStorage->getJWT();

    if (!$jwt || $jwt->isExpired() || $force) {
        $jwt = promo_video_maker_regenerate_jwt();
    }

    if (!$jwt) {
        return new WP_Error(401, 'Unauthorized');
    }

    return [
        'jwt' => $jwt->getToken(),
    ];
}

function promo_video_maker_rest_get_photos(WP_REST_Request $request)
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    $queryParams = [
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => $request->get_param('limit'),
    ];

    $currentPage = $request->has_param('cursor') ? $request->get_param('cursor') : 1;
    $queryParams['paged'] = $currentPage;
    $imagesQuery = new WP_Query($queryParams);

    $photos = [];
    foreach ($imagesQuery->posts as $imagePost) {
        $photos[] = new Photo(
            $imagePost,
            wp_get_attachment_url($imagePost->ID),
            promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_PHOTO, ['id' => $imagePost->ID]),
            wp_get_attachment_metadata($imagePost->ID),
            wp_get_attachment_thumb_url($imagePost->ID)
        );
    }

    return new PaginatedPhotos($photos, $currentPage, $imagesQuery->max_num_pages, $imagesQuery->post_count);
}

function promo_video_maker_rest_get_photo(WP_REST_Request $request)
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    $imageId = $request->get_param('id');
    if (!wp_attachment_is_image($imageId)) {
        return new WP_Error(404, 'Image not found');
    }

    $path = get_attached_file($imageId);

    return new WP_REST_Response(file_get_contents($path), 200, [
        'Content-Type' => get_post_mime_type($imageId),
        'Content-Disposition' => 'inline',
    ]);
}

function promo_video_maker_rest_get_config()
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    return [
        'jwt_url' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_JWT),
        'photos_url' => promo_video_maker_rest_get_url(PROMO_VIDEO_MAKER_REST_PATH_PHOTOS),
        'refresh_url' => promo_video_maker_get_page_url('promo-video-maker-auth-refresh'),
    ];
}

function promo_video_maker_rest_get_consumer_keys()
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    return promo_video_maker_get_consumer_keys();
}

function promo_video_maker_rest_update_consumer_keys(WP_REST_Request $request)
{
    if (!is_user_logged_in()) {
        return new WP_Error(401, 'Unauthorized');
    }

    ['consumer_key' => $consumerKey, 'consumer_secret' => $consumerSecret] = $request->get_json_params();
    $keys = new ConsumerKeys($consumerKey, $consumerSecret);
    promo_video_maker_update_consumer_keys($keys);

    return promo_video_maker_get_consumer_keys();
}

function promo_video_maker_rest_get_url(string $path, array $query = []): string
{
    $url = trailingslashit(home_url('', 'rest'));
    $route = sprintf('/promo-video-maker/v1/%s', ltrim($path, '/'));
    $query = array_merge($query, ['rest_route' => $route]);

    return add_query_arg($query, $url);
}

function promo_video_maker_rest_set_auth_cookie(string $username, WP_User $user)
{
    if (!$user->ID) {
        return;
    }

    $expiration = time() + 1 * DAY_IN_SECONDS;
    $manager = WP_Session_Tokens::get_instance($user->ID);
    $token = $manager->create($expiration);

    $cookie = wp_generate_auth_cookie($user->ID, $expiration, PROMO_VIDEO_MAKER_REST_COOKIE_SCHEME, $token);

    if (PHP_VERSION_ID < 70300) {
        setcookie(PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME, $cookie, $expiration, SITECOOKIEPATH.'; samesite=none', COOKIE_DOMAIN, true, false);
    } else {
        setcookie(PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME, $cookie, [
            'expires' => $expiration,
            'path' => SITECOOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => true,
            'httponly' => false,
            'samesite' => 'None',
        ]);
    }
}

function promo_video_maker_rest_clear_auth_cookie()
{
    setcookie(PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME, ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN);
}

function promo_video_maker_rest_create_nonce(): string
{
    $user = wp_get_current_user();
    if (!$user) {
        return '';
    }

    $userId = (int)$user->ID;
    ['token' => $token] = promo_video_maker_parse_auth_cookie();
    if (!$token) {
        return '';
    }

    return substr(wp_hash(wp_nonce_tick() . '|' . 'wp_rest' . '|' . $userId . '|' . $token, 'nonce'), -12, 10);
}

function promo_video_maker_rest_verify_nonce(string $nonce): bool
{
    $user = wp_get_current_user();
    $userId = (int) $user->ID;
    if (!$userId) {
        return false;
    }

    ['token' => $token] = promo_video_maker_parse_auth_cookie();
    if (!$token) {
        return false;
    }

    $expected = substr(wp_hash(wp_nonce_tick() . '|' . 'wp_rest' . '|' . $userId . '|' . $token, 'nonce'), -12, 10);

    return hash_equals($expected, $nonce);
}

function promo_video_maker_parse_auth_cookie()
{
    if (empty($_COOKIE[PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME])) {
        return [];
    }

    $values = explode('|', $_COOKIE[PROMO_VIDEO_MAKER_AUTH_COOKIE_NAME]);
    if (count($values) !== 4) {
        return [];
    }

    [$username, $expiration, $token, $hmac] = $values;

    return compact('username', 'expiration', 'token', 'hmac');
}

function promo_video_maker_rest_verify_namespace(): bool
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return false;
    }

    return isset($_GET['rest_route']) && strpos($_GET['rest_route'], PROMO_VIDEO_MAKER_REST_NAMESPACE) === 0;
}
