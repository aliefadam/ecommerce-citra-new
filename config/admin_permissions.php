<?php

return [
    'actions' => [
        'index' => 'Index',
        'show' => 'Show',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'override' => 'Override',
    ],

    'groups' => [
        'main' => [
            'label' => 'Menu Utama',
            'icon' => 'layout-grid',
            'modules' => [
                'dashboard' => [
                    'label' => 'Dashboard',
                    'permissions' => [
                        'index' => ['key' => 'dashboard.index', 'label' => 'Open dashboard'],
                    ],
                ],
            ],
        ],

        'reports' => [
            'label' => 'Reports',
            'icon' => 'bar-chart-3',
            'modules' => [
                'reports' => [
                    'label' => 'Report Center',
                    'permissions' => [
                        'index' => ['key' => 'reports.index', 'label' => 'Open report center'],
                    ],
                ],
                'report_owner' => [
                    'label' => 'Owner Overview',
                    'permissions' => [
                        'show' => ['key' => 'reports.owner', 'label' => 'View owner overview'],
                    ],
                ],
                'report_sales' => [
                    'label' => 'Sales Report',
                    'permissions' => [
                        'show' => ['key' => 'reports.sales', 'label' => 'View sales report'],
                        'override' => ['key' => 'reports.sales.export', 'label' => 'Export sales CSV'],
                    ],
                ],
                'report_stock' => [
                    'label' => 'Stock Report',
                    'permissions' => [
                        'show' => ['key' => 'reports.stock', 'label' => 'View stock report'],
                    ],
                ],
                'report_products' => [
                    'label' => 'Product Performance',
                    'permissions' => [
                        'show' => ['key' => 'reports.products', 'label' => 'View product performance'],
                    ],
                ],
                'report_payments' => [
                    'label' => 'Payment dan Fulfillment',
                    'permissions' => [
                        'show' => ['key' => 'reports.payments', 'label' => 'View payment and fulfillment report'],
                    ],
                ],
                'report_customers' => [
                    'label' => 'Customer Report',
                    'permissions' => [
                        'show' => ['key' => 'reports.customers', 'label' => 'View customer report'],
                    ],
                ],
                'report_promos' => [
                    'label' => 'Promo dan Coupon',
                    'permissions' => [
                        'show' => ['key' => 'reports.promos', 'label' => 'View promo and coupon report'],
                    ],
                ],
                'report_returns' => [
                    'label' => 'Return dan Refund',
                    'permissions' => [
                        'show' => ['key' => 'reports.returns', 'label' => 'View return and refund report'],
                    ],
                ],
            ],
        ],

        'transactions' => [
            'label' => 'Transaction',
            'icon' => 'receipt',
            'modules' => [
                'transactions' => [
                    'label' => 'Transactions',
                    'permissions' => [
                        'index' => ['key' => 'transactions.index', 'label' => 'List transactions'],
                        'show' => ['key' => 'transactions.show', 'label' => 'View transaction detail'],
                        'edit' => ['key' => 'transactions.edit', 'label' => 'Process and ship orders'],
                        'override' => ['key' => 'transactions.verify_payment', 'label' => 'Verify manual payment'],
                    ],
                ],
                'return_requests' => [
                    'label' => 'Return Requests',
                    'permissions' => [
                        'index' => ['key' => 'return_requests.index', 'label' => 'List return requests'],
                        'edit' => ['key' => 'return_requests.edit', 'label' => 'Approve or reject return requests'],
                    ],
                ],
                'product_reviews' => [
                    'label' => 'Product Reviews',
                    'permissions' => [
                        'index' => ['key' => 'product_reviews.index', 'label' => 'List product reviews'],
                        'edit' => ['key' => 'product_reviews.edit', 'label' => 'Hide or show product reviews'],
                        'delete' => ['key' => 'product_reviews.delete', 'label' => 'Delete product reviews'],
                    ],
                ],
            ],
        ],

        'master_data' => [
            'label' => 'Master Data',
            'icon' => 'database',
            'modules' => [
                'customers' => [
                    'label' => 'Customers',
                    'permissions' => [
                        'index' => ['key' => 'customers.index', 'label' => 'List customers'],
                    ],
                ],
                'member_tiers' => [
                    'label' => 'Membership Tiers',
                    'permissions' => [
                        'index' => ['key' => 'member_tiers.index', 'label' => 'List membership tiers'],
                        'create' => ['key' => 'member_tiers.create', 'label' => 'Create membership tiers'],
                        'edit' => ['key' => 'member_tiers.edit', 'label' => 'Edit membership tiers'],
                        'delete' => ['key' => 'member_tiers.delete', 'label' => 'Delete membership tiers'],
                    ],
                ],
            ],
        ],

        'catalog' => [
            'label' => 'Catalog',
            'icon' => 'package',
            'modules' => [
                'products' => [
                    'label' => 'Products',
                    'permissions' => [
                        'index' => ['key' => 'products.index', 'label' => 'List products'],
                        'create' => ['key' => 'products.create', 'label' => 'Create products'],
                        'edit' => ['key' => 'products.edit', 'label' => 'Edit products'],
                        'delete' => ['key' => 'products.delete', 'label' => 'Delete products'],
                        'override' => ['key' => 'products.import', 'label' => 'Import products'],
                    ],
                ],
                'categories' => [
                    'label' => 'Categories',
                    'permissions' => [
                        'index' => ['key' => 'categories.index', 'label' => 'List categories'],
                        'create' => ['key' => 'categories.create', 'label' => 'Create categories'],
                        'edit' => ['key' => 'categories.edit', 'label' => 'Edit categories'],
                        'delete' => ['key' => 'categories.delete', 'label' => 'Delete categories'],
                    ],
                ],
                'variants' => [
                    'label' => 'Variants',
                    'permissions' => [
                        'index' => ['key' => 'variants.index', 'label' => 'List variants'],
                        'create' => ['key' => 'variants.create', 'label' => 'Create variants'],
                        'edit' => ['key' => 'variants.edit', 'label' => 'Edit variants'],
                        'delete' => ['key' => 'variants.delete', 'label' => 'Delete variants'],
                    ],
                ],
                'stock' => [
                    'label' => 'Stock',
                    'permissions' => [
                        'index' => ['key' => 'stock.index', 'label' => 'View stock'],
                        'edit' => ['key' => 'stock.edit', 'label' => 'Adjust stock and thresholds'],
                    ],
                ],
                'flash_sales' => [
                    'label' => 'Flash Sale',
                    'permissions' => [
                        'index' => ['key' => 'flash_sales.index', 'label' => 'List flash sales'],
                        'create' => ['key' => 'flash_sales.create', 'label' => 'Create flash sales'],
                        'edit' => ['key' => 'flash_sales.edit', 'label' => 'Edit flash sales'],
                        'delete' => ['key' => 'flash_sales.delete', 'label' => 'Delete flash sales'],
                    ],
                ],
                'coupons' => [
                    'label' => 'Coupons',
                    'permissions' => [
                        'index' => ['key' => 'coupons.index', 'label' => 'List coupons'],
                        'create' => ['key' => 'coupons.create', 'label' => 'Create coupons'],
                        'edit' => ['key' => 'coupons.edit', 'label' => 'Edit coupons'],
                        'delete' => ['key' => 'coupons.delete', 'label' => 'Delete coupons'],
                    ],
                ],
            ],
        ],

        'settings' => [
            'label' => 'Settings & Content',
            'icon' => 'settings',
            'modules' => [
                'store_settings' => [
                    'label' => 'Store Settings',
                    'permissions' => [
                        'index' => ['key' => 'store_settings.index', 'label' => 'Open store settings'],
                        'edit' => ['key' => 'store_settings.edit', 'label' => 'Edit store settings and location'],
                    ],
                ],
                'banners' => [
                    'label' => 'Banners',
                    'permissions' => [
                        'index' => ['key' => 'banners.index', 'label' => 'List banners'],
                        'create' => ['key' => 'banners.create', 'label' => 'Create banners'],
                        'edit' => ['key' => 'banners.edit', 'label' => 'Edit banners'],
                        'delete' => ['key' => 'banners.delete', 'label' => 'Delete banners'],
                    ],
                ],
                'newsletter' => [
                    'label' => 'Newsletter',
                    'permissions' => [
                        'index' => ['key' => 'newsletter.index', 'label' => 'List subscribers'],
                        'override' => ['key' => 'newsletter.send', 'label' => 'Send and export newsletter'],
                        'delete' => ['key' => 'newsletter.delete', 'label' => 'Delete subscribers'],
                    ],
                ],
                'promo_pages' => [
                    'label' => 'Promo Pages',
                    'permissions' => [
                        'index' => ['key' => 'promo_pages.index', 'label' => 'List promo pages'],
                        'create' => ['key' => 'promo_pages.create', 'label' => 'Create promo pages'],
                        'edit' => ['key' => 'promo_pages.edit', 'label' => 'Edit promo pages'],
                        'delete' => ['key' => 'promo_pages.delete', 'label' => 'Delete promo pages'],
                    ],
                ],
                'content_pages' => [
                    'label' => 'Website Content',
                    'permissions' => [
                        'index' => ['key' => 'content_pages.index', 'label' => 'List pages and blog posts'],
                        'create' => ['key' => 'content_pages.create', 'label' => 'Create pages and blog posts'],
                        'edit' => ['key' => 'content_pages.edit', 'label' => 'Edit pages and blog posts'],
                        'delete' => ['key' => 'content_pages.delete', 'label' => 'Delete pages and blog posts'],
                    ],
                ],
            ],
        ],

        'administration' => [
            'label' => 'Administration',
            'icon' => 'shield-check',
            'modules' => [
                'admin_users' => [
                    'label' => 'Admin Users',
                    'permissions' => [
                        'index' => ['key' => 'admin_users.index', 'label' => 'List admin users'],
                        'create' => ['key' => 'admin_users.create', 'label' => 'Create admin users'],
                        'edit' => ['key' => 'admin_users.edit', 'label' => 'Edit admin users'],
                        'delete' => ['key' => 'admin_users.delete', 'label' => 'Delete admin users'],
                    ],
                ],
                'admin_roles' => [
                    'label' => 'Roles & Permissions',
                    'permissions' => [
                        'index' => ['key' => 'admin_roles.index', 'label' => 'List roles'],
                        'create' => ['key' => 'admin_roles.create', 'label' => 'Create roles'],
                        'edit' => ['key' => 'admin_roles.edit', 'label' => 'Edit roles'],
                        'delete' => ['key' => 'admin_roles.delete', 'label' => 'Delete roles'],
                    ],
                ],
            ],
        ],
    ],
];
