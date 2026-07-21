<?php

return [
    'actions' => [
        'index' => 'Index',
        'show' => 'Show',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'process' => 'Process',
        'reject' => 'Reject',
        'upload' => 'Upload',
        'send' => 'Send',
        'view_sensitive' => 'Sensitive',
        'override' => 'Override',
        'convert' => 'Convert',
        'close' => 'Close',
        'cancel' => 'Cancel',
        'record_payment' => 'Record Payment',
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
                        'create' => ['key' => 'transactions.create', 'label' => 'Create manual transactions'],
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
                'tax_invoices' => [
                    'label' => 'Faktur Pajak',
                    'permissions' => [
                        'index' => ['key' => 'tax_invoices.index', 'label' => 'List tax invoice requests'],
                        'show' => ['key' => 'tax_invoices.show', 'label' => 'View tax invoice detail'],
                        'process' => ['key' => 'tax_invoices.process', 'label' => 'Mark tax invoices as processing'],
                        'reject' => ['key' => 'tax_invoices.reject', 'label' => 'Reject tax invoice requests'],
                        'upload' => ['key' => 'tax_invoices.upload', 'label' => 'Upload tax invoice PDFs'],
                        'send' => ['key' => 'tax_invoices.send', 'label' => 'Send tax invoice emails'],
                        'view_sensitive' => ['key' => 'tax_invoices.view_sensitive', 'label' => 'View full NPWP numbers'],
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
                'api_docs' => [
                    'label' => 'API Katalog',
                    'permissions' => [
                        'index' => ['key' => 'api_docs.index', 'label' => 'Open catalog API documentation'],
                    ],
                ],
            ],
        ],

        'b2b' => [
            'label' => 'B2B Sales',
            'icon' => 'file-text',
            'modules' => [
                'quotations' => [
                    'label' => 'Quotations',
                    'permissions' => [
                        'index' => ['key' => 'quotations.index', 'label' => 'List quotations'],
                        'create' => ['key' => 'quotations.create', 'label' => 'Create quotations'],
                        'show' => ['key' => 'quotations.show', 'label' => 'View quotation detail'],
                        'edit' => ['key' => 'quotations.edit', 'label' => 'Edit quotations'],
                        'send' => ['key' => 'quotations.send', 'label' => 'Change status to sent'],
                        'convert' => ['key' => 'quotations.convert', 'label' => 'Convert to sales order'],
                        'close' => ['key' => 'quotations.close', 'label' => 'Manually close remaining qty'],
                    ],
                ],
                'sales_orders' => [
                    'label' => 'Sales Orders',
                    'permissions' => [
                        'index' => ['key' => 'sales_orders.index', 'label' => 'List sales orders'],
                        'create' => ['key' => 'sales_orders.create', 'label' => 'Create sales order directly (without quotation)'],
                        'show' => ['key' => 'sales_orders.show', 'label' => 'View sales order detail'],
                        'cancel' => ['key' => 'sales_orders.cancel', 'label' => 'Cancel sales orders'],
                    ],
                ],
                'proforma_invoices' => [
                    'label' => 'Proforma Invoices',
                    'permissions' => [
                        'index' => ['key' => 'proforma_invoices.index', 'label' => 'List proforma invoices'],
                        'create' => ['key' => 'proforma_invoices.create', 'label' => 'Issue proforma invoices'],
                        'show' => ['key' => 'proforma_invoices.show', 'label' => 'View proforma invoice detail'],
                        'cancel' => ['key' => 'proforma_invoices.cancel', 'label' => 'Cancel proforma invoices'],
                        'record_payment' => ['key' => 'proforma_invoices.record_payment', 'label' => 'Record DP payments'],
                    ],
                ],
                'delivery_notes' => [
                    'label' => 'Surat Jalan',
                    'permissions' => [
                        'index' => ['key' => 'delivery_notes.index', 'label' => 'List delivery notes'],
                        'create' => ['key' => 'delivery_notes.create', 'label' => 'Create delivery notes'],
                        'show' => ['key' => 'delivery_notes.show', 'label' => 'View delivery note detail'],
                        'process' => ['key' => 'delivery_notes.process', 'label' => 'Mark shipped/delivered'],
                    ],
                ],
                'packing_lists' => [
                    'label' => 'Packing Lists',
                    'permissions' => [
                        'index' => ['key' => 'packing_lists.index', 'label' => 'List packing lists'],
                        'show' => ['key' => 'packing_lists.show', 'label' => 'View packing list detail'],
                    ],
                ],
                'b2b_invoices' => [
                    'label' => 'Invoice B2B',
                    'permissions' => [
                        'index' => ['key' => 'b2b_invoices.index', 'label' => 'List B2B invoices'],
                        'create' => ['key' => 'b2b_invoices.create', 'label' => 'Create B2B invoices'],
                        'show' => ['key' => 'b2b_invoices.show', 'label' => 'View B2B invoice detail'],
                        'cancel' => ['key' => 'b2b_invoices.cancel', 'label' => 'Cancel B2B invoices'],
                        'record_payment' => ['key' => 'b2b_invoices.record_payment', 'label' => 'Record invoice payments'],
                    ],
                ],
            ],
        ],

        'administration' => [
            'label' => 'Administration',
            'icon' => 'shield-check',
            'modules' => [
                'companies' => [
                    'label' => 'Companies',
                    'permissions' => [
                        'index' => ['key' => 'companies.index', 'label' => 'List companies'],
                        'create' => ['key' => 'companies.create', 'label' => 'Create companies'],
                        'edit' => ['key' => 'companies.edit', 'label' => 'Edit companies'],
                        'delete' => ['key' => 'companies.delete', 'label' => 'Deactivate companies'],
                    ],
                ],
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
