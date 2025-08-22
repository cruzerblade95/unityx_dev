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
        $maxBalance = $user ? $user->getWithdrawableBalance() : 0;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:100',
                "max:{$maxBalance}"
            ],
            'currency' => 'required|string|in:USDT,USDTBEP20,USDC,FDUSD',
            'wallet_address' => 'required|string|max:255',
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
        $maxBalance = $user ? $user->getWithdrawableBalance() : 0;

        return [
            'amount.max' => "The withdrawal amount cannot exceed your available profit balance of $" . number_format($maxBalance, 2),
            'amount.min' => 'The minimum withdrawal amount is $100.00',
            'currency.required' => 'Please select a currency',
            'currency.in' => 'Invalid currency selected',
            'wallet_address.required' => 'Wallet address is required',
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
