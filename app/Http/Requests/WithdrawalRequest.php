<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\KycVerification;

class WithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has approved KYC
        $user = $this->user();
        return $user && $user->kycVerification && $user->kycVerification->kyc_status === KycVerification::STATUS_APPROVED;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $maxBalance = $user && $user->wallet ? $user->wallet->total_balance : 0;
        
        return [
            'amount' => [
                'required',
                'numeric',
                'min:1',
                "max:{$maxBalance}"
            ],
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $user = $this->user();
        $maxBalance = $user && $user->wallet ? $user->wallet->total_balance : 0;
        
        return [
            'amount.max' => "The withdrawal amount cannot exceed your available balance of $" . number_format($maxBalance, 2),
            'amount.min' => 'The minimum withdrawal amount is $1.00',
            'bank_name.required' => 'Bank name is required',
            'account_number.required' => 'Account number is required',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('Your KYC verification must be approved before you can make withdrawal requests.');
    }
}
