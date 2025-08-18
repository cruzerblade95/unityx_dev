<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class KycApprovalController extends Controller
{
    public function index()
    {
        // Get all KYC verifications with pending status (1)
        $pendingKycs = KycVerification::with('user')
            ->where('kyc_status', KycVerification::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.kyc.index', compact('pendingKycs'));
    }

    public function show($id)
    {
        $kycVerification = KycVerification::with('user')->findOrFail($id);
        
        // Get document URLs if they exist
        $documents = [];
        if ($kycVerification->kyc_doc1) {
            $documents['kyc_doc1'] = Storage::url($kycVerification->kyc_doc1);
        }
        if ($kycVerification->kyc_doc2) {
            $documents['kyc_doc2'] = Storage::url($kycVerification->kyc_doc2);
        }
        if ($kycVerification->kyc_doc3) {
            $documents['kyc_doc3'] = Storage::url($kycVerification->kyc_doc3);
        }

        return view('admin.kyc.show', compact('kycVerification', 'documents'));
    }

    public function approve(Request $request, $id)
    {
        $kycVerification = KycVerification::findOrFail($id);
        
        // Update status to approved (2)
        $kycVerification->update([
            'kyc_status' => KycVerification::STATUS_APPROVED,
            'kyc_reason' => $request->reason ?? 'KYC documents approved by admin'
        ]);

        return redirect()->route('admin.kyc.index')
            ->with('success', 'KYC verification approved successfully for user: ' . $kycVerification->user->username);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $kycVerification = KycVerification::findOrFail($id);
        
        // Delete uploaded files before rejecting
        if ($kycVerification->kyc_doc1 && Storage::exists($kycVerification->kyc_doc1)) {
            Storage::delete($kycVerification->kyc_doc1);
        }
        if ($kycVerification->kyc_doc2 && Storage::exists($kycVerification->kyc_doc2)) {
            Storage::delete($kycVerification->kyc_doc2);
        }
        if ($kycVerification->kyc_doc3 && Storage::exists($kycVerification->kyc_doc3)) {
            Storage::delete($kycVerification->kyc_doc3);
        }
        
        // Update status to not submitted (0) and clear document fields
        $kycVerification->update([
            'kyc_status' => KycVerification::STATUS_NOT_SUBMITTED,
            'kyc_reason' => $request->reason,
            'kyc_doc1' => null,
            'kyc_doc2' => null,
            'kyc_doc3' => null
        ]);

        return redirect()->route('admin.kyc.index')
            ->with('success', 'KYC verification rejected and documents deleted. User can resubmit new documents.');
    }
}
