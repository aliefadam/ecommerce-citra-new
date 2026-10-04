<?php

$store = (string) config('app.name');

return [
    'home' => 'Home',
    'help_center' => 'Help Center',
    'hero_label' => 'Information & Guides',
    'customer_information' => 'Customer Information',
    'customer_information_navigation' => 'Customer information pages',
    'still_need_help' => 'Still need help?',
    'help_copy' => 'Find more answers in our Help Center.',
    'open_help_center' => 'Open Help Center',
    'more_questions' => 'Have another question?',
    'support_copy' => 'Our team can help with orders, products, payments, and shipping.',
    'visit_help_center' => 'Visit the Help Center',
    'content_unavailable' => 'Content is not available yet.',
    'open_image' => 'open the full-size image',
    'shopping_gallery_title' => 'Visual shopping guide',
    'shopping_gallery_intro' => 'Follow these six steps from choosing a product until your order is created and ready to track.',
    'shopping_images' => [
        ['title' => '1. Find a product', 'caption' => 'Use categories and filters to narrow down the products you need.'],
        ['title' => '2. Review product details', 'caption' => 'Confirm the specifications, stock, price, and quantity before adding the product.'],
        ['title' => '3. Review your cart', 'caption' => 'Check the selected products and quantities before continuing to checkout.'],
        ['title' => '4. Complete checkout', 'caption' => 'Confirm the address, shipping service, and payment method before placing the order.'],
        ['title' => '5. Order created successfully', 'caption' => 'Save the order number and open the payment details to follow the next instructions.'],
        ['title' => '6. Track your order', 'caption' => 'Enter your email and order number to view payment and shipping updates.'],
    ],
    'pages' => [
        'technical' => [
            'label' => 'Technical',
            'title' => 'Technical',
            'excerpt' => 'Guidance for understanding technical product specifications, selection, and use.',
            'meta_description' => 'Technical product and specification guidance from '.$store.'.',
            'content' => '<h2>Technical product information</h2><p>Use this page as a starting point for understanding product dimensions, materials, finishes, standards, and selling units. Available details may vary by category and variant.</p><h2>Before choosing a product</h2><ul><li>Match the dimensions and specifications to your application.</li><li>Review the material, finish, standard, and operating environment.</li><li>Confirm the variant, quantity, and selling unit before ordering.</li></ul><h2>Need confirmation?</h2><p>Prepare the product name or SKU and its intended application, then contact our team through the Help Center.</p>',
        ],
        'project' => [
            'label' => 'Project',
            'title' => 'Project',
            'excerpt' => 'Technical product procurement support for projects and volume purchases.',
            'meta_description' => 'Project procurement and technical product support from '.$store.'.',
            'content' => '<h2>Project procurement support</h2><p>We support technical product procurement for construction, manufacturing, maintenance, workshops, and other operational needs.</p><h2>Information to prepare</h2><ul><li>The required product list or specifications.</li><li>Quantities, units, and target procurement date.</li><li>Delivery location and supporting document requirements.</li></ul><h2>Consultation process</h2><p>Send your project requirements through our official channels. Our team will help match products, check availability, and prepare a quotation from the information provided.</p>',
        ],
        'cara-belanja' => [
            'label' => 'How to Shop',
            'title' => 'How to Shop',
            'excerpt' => 'A step-by-step guide to ordering technical products, from search to delivery.',
            'meta_description' => 'A guide to shopping at '.$store.'.',
            'content' => '<h2>1. Find a product</h2><p>Use search or browse categories, then match the SKU, size, material, variant, and selling unit.</p><h2>2. Review product details</h2><p>Confirm the specifications, stock, price, and quantity before adding the product to your cart.</p><h2>3. Review your cart</h2><p>Check the selected products and quantities before continuing to checkout.</p><h2>4. Complete checkout</h2><p>Confirm the address, shipping service, and payment method, then place the order.</p><h2>5. Order created successfully</h2><p>Follow the payment instructions shown and keep your order number.</p><h2>6. Track your order</h2><p>Use the purchaser email and order number on the Track Order page to view payment and shipping updates.</p>',
        ],
        'tentang-boq' => [
            'label' => 'About BOQ',
            'title' => 'About BOQ',
            'excerpt' => 'Learn about Bills of Quantities and how to organize procurement requirements.',
            'meta_description' => 'Bill of Quantity and project procurement information from '.$store.'.',
            'content' => '<h2>What is a BOQ?</h2><p>A BOQ, or Bill of Quantity, is a structured list of the products, specifications, units, and quantities required for a job or project.</p><h2>Why is a BOQ important?</h2><ul><li>It makes requirement completeness easier to review.</li><li>It reduces the risk of specification and quantity mismatches.</li><li>It supports quotation requests and procurement planning.</li></ul><h2>Preparing a BOQ</h2><p>Include product names, main specifications, required brands, units, quantities, and technical notes. Our team can help match the list to available products.</p>',
        ],
        'kebijakan-privasi' => [
            'label' => 'Privacy Policy',
            'title' => 'Privacy Policy',
            'excerpt' => 'A summary of how customer data is used to provide store services.',
            'meta_description' => $store.' customer privacy policy.',
            'content' => '<h2>Data we process</h2><p>We process account, contact, address, and transaction data needed to provide services, fulfill orders, prevent abuse, and meet legal obligations.</p><h2>Use and security</h2><p>Data is used for service purposes and shared with payment, shipping, or support providers only as needed. Contact an official support channel for requests concerning personal data.</p><h2>Updates</h2><p>The version published at this URL is the current policy. Store administrators may update it when a final policy becomes available.</p>',
        ],
        'syarat-ketentuan' => [
            'label' => 'Terms & Conditions',
            'title' => 'Terms & Conditions',
            'excerpt' => 'Basic terms for using the service and ordering products.',
            'meta_description' => 'Terms and conditions for using '.$store.'.',
            'content' => '<h2>Product information</h2><p>Customers are responsible for reviewing the product name, SKU, specifications, variant, selling unit, quantity, and selling company before confirming an order.</p><h2>Prices and payments</h2><p>Applicable prices, taxes, discounts, shipping fees, and totals are shown during checkout. Orders are processed according to payment status and stock availability.</p><h2>Shipping and returns</h2><p>Delivery estimates may change due to courier operations. Return requests remain subject to eligibility and the evidence required in the order flow.</p>',
        ],
    ],
];
