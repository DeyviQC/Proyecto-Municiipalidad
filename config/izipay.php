<?php

return [
    'mode' => env('IZIPAY_MODE', 'test'),
    'username' => env('IZIPAY_REST_USERNAME'),
    'test_password' => env('IZIPAY_REST_TEST_PASSWORD'),
    'server' => env('IZIPAY_REST_SERVER', 'https://api.micuentaweb.pe'),
    'js_url' => env('IZIPAY_JS_URL'),
    'public_test_key' => env('IZIPAY_PUBLIC_TEST_KEY'),
    'hmac_test_key' => env('IZIPAY_HMAC_TEST_KEY'),
];
