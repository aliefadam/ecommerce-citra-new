<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReturnRequestController extends Controller
{
    public function index()
    {
        $returnRequests = ReturnRequest::query()
            ->with(['user', 'transaction', 'items'])
            ->latest()
            ->get();

        return view('backend.return-requests.index', compact('returnRequests'));
    }

    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['menunggu', 'disetujui', 'ditolak', 'diproses', 'selesai'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $previousStatus = (string) $returnRequest->status;
        $newStatus = (string) $validated['status'];

        $returnRequest->status = $newStatus;
        $returnRequest->admin_note = $validated['admin_note'] ?? null;

        if ($newStatus === 'disetujui' && !$returnRequest->approved_at) {
            $returnRequest->approved_at = now();
            $returnRequest->rejected_at = null;
        }

        if ($newStatus === 'ditolak' && !$returnRequest->rejected_at) {
            $returnRequest->rejected_at = now();
        }

        if ($newStatus === 'selesai' && !$returnRequest->completed_at) {
            $returnRequest->completed_at = now();
        }

        $returnRequest->save();

        if ($returnRequest->user_id && $previousStatus !== $newStatus) {
            $typeLabel = $returnRequest->type === 'refund' ? 'refund uang' : 'ganti barang';
            UserNotification::create([
                'user_id' => $returnRequest->user_id,
                'type' => 'return_request_updated',
                'title' => 'Status Return/Refund Diperbarui',
                'body' => 'Pengajuan ' . $typeLabel . ' ' . $returnRequest->request_no . ' sekarang berstatus ' . $newStatus . '.',
                'url' => route('frontend.profil') . '?tab=pesanan',
            ]);
        }

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
