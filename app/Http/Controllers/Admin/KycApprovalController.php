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
        return view('admin.kyc.index');
    }

    public function getKycData(Request $request)
    {
        // Clean any output buffer to prevent BOM characters in JSON response
        if (ob_get_level()) {
            ob_end_clean();
        }

        $query = KycVerification::with('user')
            ->where('kyc_status', KycVerification::STATUS_PENDING)
            ->orderBy('created_at', 'desc');

        return datatables($query)
            ->addColumn('user_info', function ($kyc) {
                return [
                    'firstname' => $kyc->user->firstname,
                    'lastname' => $kyc->user->lastname,
                    'username' => $kyc->user->username,
                    'email' => $kyc->user->email,
                    'full_name' => $kyc->user->firstname . ' ' . $kyc->user->lastname
                ];
            })
            ->addColumn('documents', function ($kyc) {
                $badges = [];
                if ($kyc->kyc_doc1) {
                    $badges[] = '<span class="badge bg-primary">ID Front</span>';
                }
                if ($kyc->kyc_doc2) {
                    $badges[] = '<span class="badge bg-primary">ID Back</span>';
                }
                if ($kyc->kyc_doc3) {
                    $badges[] = '<span class="badge bg-info">Passport</span>';
                }
                return '<div class="d-flex gap-1">' . implode(' ', $badges) . '</div>';
            })
            ->addColumn('status', function ($kyc) {
                return '<span class="badge bg-warning">' . $kyc->status_text . '</span>';
            })
            ->addColumn('submitted_at', function ($kyc) {
                return $kyc->created_at->format('M d, Y H:i');
            })
            ->addColumn('actions', function ($kyc) {
                return '
                    <div class="d-flex gap-2">
                        <a href="' . route('admin.kyc.show', $kyc->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Review
                        </a>
                        <button type="button" class="btn btn-sm btn-success" onclick="quickApprove(' . $kyc->id . ')">
                            <i class="fas fa-check me-1"></i>Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="showRejectModal(' . $kyc->id . ', \'' . $kyc->user->username . '\')">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                ';
            })
            ->filterColumn('user_info', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('firstname', 'like', "%{$keyword}%")
                      ->orWhere('lastname', 'like', "%{$keyword}%")
                      ->orWhere('username', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['documents', 'status', 'actions'])
            ->make(true);
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
