<?php

namespace App\Http\Controllers;

use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
use App\Models\SpecificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SpecificationTemplateController extends Controller
{
    public function index()
    {
        $templates = SpecificationTemplate::query()
            ->withCount(['fields', 'categoryDetails', 'defaultMainCategories'])
            ->orderBy('name')->get();

        return view('backend.specification-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backend.specification-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('specification_templates', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $baseCode = $validated['code'] ?: (Str::slug($validated['name'], '_') ?: 'template');
        $code = $baseCode;
        $suffix = 2;
        while (SpecificationTemplate::query()->where('code', $code)->exists()) {
            $code = $baseCode.'_'.$suffix++;
        }

        $template = SpecificationTemplate::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('specification-templates.edit', $template)
            ->with('success', 'Template dibuat. Tambahkan field spesifikasi di bawah ini.');
    }

    public function edit(SpecificationTemplate $specificationTemplate)
    {
        $specificationTemplate->load(['fields.definition', 'options']);
        $definitions = AttributeDefinition::query()->orderBy('name')->get();

        return view('backend.specification-templates.edit', compact('specificationTemplate', 'definitions'));
    }

    public function update(Request $request, SpecificationTemplate $specificationTemplate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('specification_templates', 'code')->ignore($specificationTemplate->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.enabled' => ['nullable', 'boolean'],
            'fields.*.input_type' => ['nullable', Rule::in(['text', 'number', 'decimal', 'select'])],
            'fields.*.label_override' => ['nullable', 'string', 'max:100'],
            'fields.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.is_filterable' => ['nullable', 'boolean'],
            'fields.*.affects_variant' => ['nullable', 'boolean'],
            'fields.*.allow_custom_value' => ['nullable', 'boolean'],
            'fields.*.options' => ['nullable', 'string', 'max:10000'],
        ]);

        $definitions = AttributeDefinition::query()->get()->keyBy('id');

        DB::transaction(function () use ($validated, $definitions, $specificationTemplate) {
            $specificationTemplate->update([
                'name' => trim($validated['name']),
                'code' => trim($validated['code']),
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $submittedIds = [];
            foreach ($validated['fields'] ?? [] as $definitionId => $config) {
                $definitionId = (int) $definitionId;
                if (! $definitions->has($definitionId) || empty($config['enabled'])) {
                    continue;
                }
                $submittedIds[] = $definitionId;
                $specificationTemplate->fields()->updateOrCreate(
                    ['attribute_definition_id' => $definitionId],
                    [
                        'label_override' => filled($config['label_override'] ?? null) ? trim($config['label_override']) : null,
                        'input_type' => $config['input_type'] ?? ($definitions[$definitionId]->data_type === 'number' ? 'number' : 'text'),
                        'is_required' => (bool) ($config['is_required'] ?? false),
                        'is_filterable' => (bool) ($config['is_filterable'] ?? false),
                        'affects_variant' => (bool) ($config['affects_variant'] ?? false),
                        'allow_custom_value' => (bool) ($config['allow_custom_value'] ?? false),
                        'sort_order' => (int) ($config['sort_order'] ?? 0),
                        'is_active' => true,
                    ]
                );

                $values = collect(preg_split('/\R/', (string) ($config['options'] ?? '')))
                    ->map(fn ($value) => trim($value))->filter()->unique(fn ($value) => mb_strtolower($value))->values();
                AttributeOption::query()
                    ->where('specification_template_id', $specificationTemplate->id)
                    ->where('attribute_definition_id', $definitionId)
                    ->update(['is_active' => false]);
                foreach ($values as $position => $value) {
                    AttributeOption::query()->updateOrCreate(
                        ['specification_template_id' => $specificationTemplate->id, 'attribute_definition_id' => $definitionId, 'value' => $value],
                        ['sort_order' => ($position + 1) * 10, 'is_active' => true]
                    );
                }
            }

            $specificationTemplate->fields()
                ->when($submittedIds !== [], fn ($query) => $query->whereNotIn('attribute_definition_id', $submittedIds))
                ->when($submittedIds === [], fn ($query) => $query)
                ->update(['is_active' => false]);
        });

        return back()->with('success', 'Template spesifikasi berhasil diperbarui.');
    }

    public function storeAttribute(Request $request, SpecificationTemplate $specificationTemplate)
    {
        $validated = $request->validate([
            'attribute_name' => ['required', 'string', 'max:100'],
            'attribute_code' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('attribute_definitions', 'code')],
            'data_type' => ['required', Rule::in(['text', 'number'])],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $specificationTemplate) {
            $definition = AttributeDefinition::create([
                'name' => trim($validated['attribute_name']),
                'code' => trim($validated['attribute_code']),
                'data_type' => $validated['data_type'],
                'unit' => filled($validated['unit'] ?? null) ? trim($validated['unit']) : null,
                'is_filterable' => true,
                'sort_order' => ((int) $specificationTemplate->fields()->max('sort_order')) + 10,
            ]);
            $specificationTemplate->fields()->create([
                'attribute_definition_id' => $definition->id,
                'input_type' => $definition->data_type === 'number' ? 'decimal' : 'text',
                'is_required' => false,
                'is_filterable' => true,
                'affects_variant' => true,
                'allow_custom_value' => true,
                'sort_order' => $definition->sort_order,
                'is_active' => true,
            ]);
        });

        return back()->with('success', 'Field baru ditambahkan. Lengkapi aturannya lalu simpan konfigurasi.');
    }
}
