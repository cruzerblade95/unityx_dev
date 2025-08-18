<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KycVerification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $kycVerification = $user->kycVerification;
        
        return view('user.kyc.index', compact('kycVerification'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user already has KYC submission
        if ($user->kycVerification && $user->kycVerification->kyc_status === KycVerification::STATUS_APPROVED) {
            return redirect()->back()->with('error', 'Your KYC is already approved.');
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:national_id,passport',
            'kyc_doc1' => 'required_if:document_type,national_id|image|mimes:jpeg,png,jpg|max:2048',
            'kyc_doc2' => 'required_if:document_type,national_id|image|mimes:jpeg,png,jpg|max:2048',
            'kyc_doc3' => 'required_if:document_type,passport|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $kycData = [
            'user_id' => $user->id,
            'kyc_status' => KycVerification::STATUS_PENDING, // This sets status to 1 (pending)
        ];

        if ($request->document_type === 'national_id') {
            // Store national ID documents
            $kycData['kyc_doc1'] = $request->file('kyc_doc1')->store('kyc_documents', 'public');
            $kycData['kyc_doc2'] = $request->file('kyc_doc2')->store('kyc_documents', 'public');
        } else {
            // Store passport document
            $kycData['kyc_doc3'] = $request->file('kyc_doc3')->store('kyc_documents', 'public');
        }

        // Update or create KYC record
        KycVerification::updateOrCreate(
            ['user_id' => $user->id],
            $kycData
        );

        return redirect()->back()->with('success', 'KYC documents submitted successfully. Your submission is now pending review.');
    }
}
