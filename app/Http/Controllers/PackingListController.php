<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\PackingList;
use Illuminate\Http\Request;

class PackingListController extends Controller
{
    use ScopesToActiveCompany;

    public function index(Request $request)
    {
        $packingLists = PackingList::query()
            ->with('deliveryNote.salesOrder')
            ->where('company_id', $this->activeCompanyId())
            ->latest()
            ->paginate(20);

        return view('backend.packing-lists.index', compact('packingLists'));
    }

    public function show(PackingList $packingList)
    {
        $this->guardCompanyOwnership($packingList->company_id);

        return redirect()->route('delivery-notes.show', $packingList->delivery_note_id);
    }
}
