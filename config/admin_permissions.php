<?php

return [
    'groups' => [
        'dashboard' => [
            'label' => 'Dashboard & Reports',
            'permissions' => [
                'view_dashboard' => 'View dashboard',
                'view_reports' => 'View sales reports',
            ],
        ],
        'orders' => [
            'label' => 'Orders',
            'permissions' => [
                'manage_orders' => 'Manage transactions and return requests',
                'manage_product_reviews' => 'Moderate product reviews',
            ],
        ],
        'catalog' => [
            'label' => 'Catalog',
            'permissions' => [
                'manage_catalog' => 'Manage products, categories, variants, stock, flash sale, and coupons',
                'manage_banners' => 'Manage banners',
                'manage_store_settings' => 'Manage store settings and store location',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'permissions' => [
                'view_customers' => 'View customer data',
                'manage_membership_tiers' => 'Manage membership tiers',
            ],
        ],
        'administration' => [
            'label' => 'Administration',
            'permissions' => [
                'manage_admin_users' => 'Manage admin users',
                'manage_roles_permissions' => 'Manage roles and permissions',
            ],
        ],
    ],
];
