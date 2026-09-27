<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Read-only inventory: no product, customer, credential, or category mutations.
$rows = App\Models\CategoryDetail::with(['specificationTemplate:id,code', 'mainCategory:id,name,default_specification_template_id'])
    ->withCount('products')->get(['id', 'name', 'main_category_id', 'specification_template_id']);
foreach ($rows as $row) {
    if (!$row->specification_template_id || preg_match('/klem|clamp|bracket/i', $row->name)) {
        echo json_encode([
            'id' => $row->id, 'name' => $row->name, 'products' => $row->products_count,
            'template' => $row->specificationTemplate?->code,
            'fallback_template_id' => $row->mainCategory?->default_specification_template_id,
        ], JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}
