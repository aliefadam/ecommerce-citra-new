<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: white;
        position: relative;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b;
        font-size: 0.875rem;
        line-height: 42px;
        padding-left: 1rem;
        padding-right: 3rem;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        width: 28px;
        position: absolute;
        top: 0;
        right: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        top: 50%;
        right: 28px;
        transform: translateY(-50%);
        float: none;
        margin: 0;
        font-size: 16px;
        line-height: 1;
        color: #94a3b8;
        padding: 0 4px;
        font-weight: bold;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #ef4444;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #3b82f6;
    }
    .select2-dropdown {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 6px 10px;
        font-size: 0.875rem;
    }
    .select2-results__option {
        font-size: 0.875rem;
        padding: 8px 12px;
    }
    .dark .select2-container--default .select2-selection--single {
        background: #334155;
        border-color: #475569;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #e2e8f0;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__clear {
        color: #64748b;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #f87171;
    }
    .dark .select2-dropdown {
        background: #1e293b;
        border-color: #475569;
    }
    .dark .select2-search--dropdown .select2-search__field {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark .select2-results__option {
        color: #e2e8f0;
        background-color: #1e293b;
    }
    .dark .select2-results__option:hover {
        background-color: #334155;
    }
    .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #3b82f6;
        color: white;
    }
    .product-col { width: 300px; min-width: 300px; max-width: 300px; }
    .product-col .select2-container { width: 100% !important; }
</style>
