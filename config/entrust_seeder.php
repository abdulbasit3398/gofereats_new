<?php

return [
    'role_structure' => [
        'admin' => [
            'admin' => 'c,r,u,d',
            'role' => 'c,r,u,d',
            'user' => 'c,r,u,d',
            'restaurant' => 'c,r,u,d',
            'driver' => 'c,r,u,d',
            'promo' => 'c,r,u,d',
            'category' => 'c,r,u,d',
            'help' => 'c,r,u,d',
            'help_category' => 'c,r,u,d',
            'help_subcategory' => 'c,r,u,d',
            'static_page' => 'c,r,u,d',
            'country' => 'c,r,u,d',
            'currency' => 'c,r,u,d',
            'language' => 'c,r,u,d',
            'cancel_reason' => 'c,r,u,d',
            'review_issue_type' => 'c,r,u,d',
            'recipient' => 'c,r,u,d',
            'restaurant_slider' => 'c,r,u,d',
            'vehicle_type' => 'c,r,u,d',
            'orders' => 'm',
            'payouts' => 'm',
            'metas' => 'm',
            'owe_amount' => 'm',
            'restaurant_owe_amount' => 'm',
            'penality' => 'm',
            'send_message' => 'm',
            'site_setting' => 'm',
            'home_banner' =>'r,u',
            'support'   =>'c,r,u,d',
            
        ],
        'subadmin' => [
            'user' => 'c,r,u',
            'promo' => 'c,r,u',
            'help' => 'c,r,u,d',
            'help_category' => 'c,r,u,d',
            'help_subcategory' => 'c,r,u,d',
            'vehicle_type' => 'c,r,u',
            'send_message' => 'm',
        ],
    ],
    'user_roles' => [
        'admin' => [
            ['username' => "admin", "email" => "admin@trioangle.com", "password" => 'gofereats'],
        ],
        'subadmin' => [
            ['username' => "subadmin", "email" => "subadmin@trioangle.com", "password" => 'gofereats'],
            ['username' => "accountant", "email" => "accountant@trioangle.com", "password" => 'gofereats'],
        ],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'view',
        'u' => 'update',
        'd' => 'delete',
        'm' => 'manage',
    ],
];
