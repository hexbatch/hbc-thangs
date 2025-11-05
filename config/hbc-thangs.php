<?php


return [
    'test' => 'hi test',
    'middleware' => [
        'auth_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_AUTH'),
        'admin_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_ADMIN'),
        'owner_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_OWNER'),

        'thang_viewable_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_THANG_VIEWABLE'),
        'thang_editable_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_THANG_EDITABLE'),

        'hook_viewable_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_HOOK_VIEWABLE'),
        'hook_editable_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_HOOK_EDITABLE'),

        'callback_viewable_alias' => env('HBC_THANG_MIDDLEWARE_ALIAS_CALLBACK_VIEWABLE'),

    ]

];

//config('hbc-things.auth_middleware_alias') //example for accessing


