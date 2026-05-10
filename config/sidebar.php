<?php

return [
    [
        'group' => 'Main Menu',
        'items' => [
            [
                'name'   => 'Dashboard',
                'route'  => 'pages.index',
                'active' => 'pages.index',
                'icon'   => 'layout-grid',
                'permission' => 'view_dashboard',
            ],
            // [
            //     'name'   => 'Dashboard 2',
            //     'route'  => 'pages.dashboard2',
            //     'active' => 'pages.dashboard2',
            //     'icon'   => 'bar-chart-2',
            // ],
            // [
            //     'name'   => 'Data Tables',
            //     'route'  => 'pages.datatables',
            //     'active' => 'pages.datatables',
            //     'icon'   => '<path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18" />',
            // ],
            // [
            //     'name'   => 'Charts',
            //     'route'  => 'pages.charts',
            //     'active' => 'pages.charts',
            //     'icon'   => '<line x1="18" y1="20" x2="18" y2="10" /><line x1="12" y1="20" x2="12" y2="4" /><line x1="6" y1="20" x2="6" y2="14" /><line x1="2" y1="20" x2="22" y2="20" />',
            // ],
            // [
            //     'name'   => 'Components',
            //     'route'  => 'pages.components',
            //     'active' => 'pages.components',
            //     'icon'   => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />',
            // ],
            // [
            //     'name'   => 'Settings',
            //     'route'  => 'pages.settings',
            //     'active' => 'pages.settings',
            //     'icon'   => '<circle cx="12" cy="12" r="3" /><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" /><path d="M12 2v2M12 20v2M2 12h2M20 12h2" />',
            // ],
            // [
            //     'name' => 'Reports',
            //     'icon' => '<path d="M4 6h16M4 10h16M4 14h8M4 18h8" />',
            //     'children' => [
            //         [
            //             'name'  => 'Monthly Report',
            //             'route' => null,
            //             'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" />',
            //         ],
            //         [
            //             'name'  => 'Annual Report',
            //             'route' => null,
            //             'icon'  => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" />',
            //         ],
            //         [
            //             'name'  => 'Analytics',
            //             'route' => null,
            //             'icon'  => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />',
            //         ],
            //     ],
            // ],
            // [
            //     'name' => 'Management',
            //     'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
            //     'children' => [
            //         [
            //             'name'  => 'Users',
            //             'route' => null,
            //             'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />',
            //         ],
            //         [
            //             'name' => 'Roles & Permissions',
            //             'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />',
            //             'children' => [
            //                 ['name' => 'Manage Roles',       'route' => null],
            //                 ['name' => 'Manage Permissions', 'route' => null],
            //                 ['name' => 'Assign Roles',       'route' => null],
            //             ],
            //         ],
            //         [
            //             'name'  => 'Departments',
            //             'route' => null,
            //             'icon'  => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />',
            //         ],
            //     ],
            // ],
        ],
    ],

    [
        'group' => 'Transactions',
        'items' => [
            [
                'name'   => 'Transactions',
                'route'  => 'transactions.index',
                'active' => 'transactions.*',
                'icon'   => 'receipt',
                'permission' => 'manage_orders',
            ],
            [
                'name'   => 'Return Requests',
                'route'  => 'return-requests.index',
                'active' => 'return-requests.*',
                'icon'   => 'rotate-ccw',
                'permission' => 'manage_orders',
            ],
            [
                'name'   => 'Product Reviews',
                'route'  => 'product-reviews.index',
                'active' => 'product-reviews.*',
                'icon'   => 'star',
                'permission' => 'manage_product_reviews',
            ],
            [
                'name'   => 'Sales Report',
                'route'  => 'reports.sales',
                'active' => 'reports.*',
                'icon'   => 'bar-chart-3',
                'permission' => 'view_reports',
            ],
        ],
    ],
    [
        'group' => 'Master Data',
        'items' => [
            [
                'name'   => 'Customers',
                'route'  => 'users.index',
                'active' => 'users.*',
                'icon'   => 'users',
                'permission' => 'view_customers',
            ],
            [
                'name'   => 'Products',
                'route'  => 'products.index',
                'active' => 'products.*',
                'icon'   => 'package',
                'permission' => 'manage_catalog',
            ],
            [
                'name'   => 'Categories',
                'icon'   => 'folder',
                'permission' => 'manage_catalog',
                'children' => [
                    [
                        'name'   => 'Main Categories',
                        'route'  => 'main-categories.index',
                        'active' => 'main-categories.*',
                        'permission' => 'manage_catalog',
                    ],
                    [
                        'name'   => 'Category Details',
                        'route'  => 'category-details.index',
                        'active' => 'category-details.*',
                        'permission' => 'manage_catalog',
                    ],
                ],
            ],
            [
                'name'   => 'Variants',
                'route'  => 'variants.index',
                'active' => 'variants.*',
                'icon'   => 'shopping-cart',
                'permission' => 'manage_catalog',
            ],
            [
                'name'   => 'Stock',
                'route'  => 'stocks.index',
                'active' => 'stocks.*',
                'icon'   => 'archive',
                'permission' => 'manage_catalog',
            ],
            [
                'name'   => 'Flash Sale',
                'route'  => 'flash-sales.index',
                'active' => 'flash-sales.*',
                'icon'   => 'zap',
                'permission' => 'manage_catalog',
            ],
            [
                'name'   => 'Coupons',
                'route'  => 'coupons.index',
                'active' => 'coupons.*',
                'icon'   => 'ticket-percent',
                'permission' => 'manage_catalog',
            ],
        ],
    ],
    [
        'group' => 'Settings',
        'items' => [
            [
                'name'   => 'Store Settings',
                'route'  => 'pages.settings',
                'active' => 'pages.settings',
                'icon'   => 'settings',
                'permission' => 'manage_store_settings',
            ],
            [
                'name'   => 'Banners',
                'route'  => 'banners.index',
                'active' => 'banners.*',
                'icon'   => 'image',
                'permission' => 'manage_banners',
            ],
            [
                'name'   => 'Admin Users',
                'route'  => 'admin-users.index',
                'active' => 'admin-users.*',
                'icon'   => 'shield-user',
                'permission' => 'manage_admin_users',
            ],
            [
                'name'   => 'Roles & Permissions',
                'route'  => 'admin-roles.index',
                'active' => 'admin-roles.*',
                'icon'   => 'shield-check',
                'permission' => 'manage_roles_permissions',
            ],
        ],
    ],

    // [
    //     'group' => 'Account',
    //     'items' => [
    //         [
    //             'name'   => 'Profile',
    //             'route'  => null,
    //             'active' => null,
    //             'icon'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />',
    //         ],
    //         [
    //             'name'   => 'Logout',
    //             'route'  => 'logout',
    //             'active' => null,
    //             'icon'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" />',
    //             'logout' => true,
    //         ],
    //     ],
    // ],
];
