<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    use ScopesToActiveCompany;

    public function index()
    {
        $vehicles = Vehicle::query()
            ->where('company_id', $this->activeCompanyId())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('backend.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('backend.vehicles.create', ['vehicle' => new Vehicle()]);
    }

    public function store(Request $request)
    {
        Vehicle::query()->create([
            ...$this->validated($request),
            'company_id' => $this->activeCompanyId(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->guardCompanyOwnership($vehicle->company_id);

        return view('backend.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->guardCompanyOwnership($vehicle->company_id);
        $vehicle->update([
            ...$this->validated($request, $vehicle),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->guardCompanyOwnership($vehicle->company_id);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dihapus. Histori ongkir transaksi tetap tersimpan.');
    }

    private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        $companyId = $this->activeCompanyId();

        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('vehicles', 'name')->where('company_id', $companyId)->ignore($vehicle?->id),
            ],
            'type' => ['required', Rule::in(array_keys(Vehicle::TYPES))],
            'plate_number' => ['nullable', 'string', 'max:30'],
            'capacity_kg' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'rate_per_kg' => ['required', 'integer', 'min:0', 'max:999999999'],
            'distance_block_km' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'rate_per_distance_block' => ['required', 'integer', 'min:0', 'max:999999999'],
            'max_distance_km' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}
