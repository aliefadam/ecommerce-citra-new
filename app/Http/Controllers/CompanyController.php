<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->withCount(['products', 'transactions'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('backend.companies.index', [
            'companies' => $companies,
        ]);
    }

    public function create()
    {
        return view('backend.companies.create', [
            'company' => new Company(),
        ]);
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validateCompany($request);

        $validated['logo_path'] = $request->hasFile('logo')
            ? $imageOptimizer->storeWebp($request->file('logo'), 'companies', 512, 512, 82)
            : null;

        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function edit(Company $company)
    {
        return view('backend.companies.edit', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validateCompany($request, $company);

        if ($request->hasFile('logo')) {
            $imageOptimizer->deletePublicFile($company->logo_path);
            $validated['logo_path'] = $imageOptimizer->storeWebp($request->file('logo'), 'companies', 512, 512, 82);
        }

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil diperbarui.');
    }

    /**
     * Companies punya banyak data anak (products, transactions, dst) yang wajib dipertahankan
     * secara historis -- jadi "hapus" di sini berarti nonaktifkan, bukan hapus baris (lihat
     * docs/prd-multi-company-foundation.md §Scope Functional 1).
     */
    public function destroy(Company $company)
    {
        $company->update(['is_active' => false]);

        return redirect()->route('companies.index')->with('success', 'Perusahaan dinonaktifkan.');
    }

    public function switch(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $companyId = (int) $validated['company_id'];
        $accessible = collect($request->attributes->get('accessible_companies', []));

        if (!$accessible->contains('id', $companyId)) {
            return back()->with('error', 'Anda tidak punya akses ke perusahaan tersebut.');
        }

        session(['admin_active_company_id' => $companyId]);

        return back()->with('success', 'Perusahaan aktif diperbarui.');
    }

    private function validateCompany(Request $request, ?Company $company = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'invoice_prefix' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('companies', 'invoice_prefix')->ignore($company?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($validated['logo']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['slug'] = $company?->slug ?? $this->uniqueSlug($validated['name']);

        return $validated;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (Company::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
