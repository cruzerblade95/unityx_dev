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

        // Determine limits based on withdrawal type
        $minAmount = 100;
        $maxAmount = $maxBalance;
        
        switch($this->withdrawal_type) {
            case 'crypto':
                $minAmount = 50;
                $maxAmount = min(300, $maxBalance);
                break;
            case 'etc_black':
                $minAmount = 100;
                $maxAmount = min(300, $maxBalance);
                break;
            case 'gold':
                $minAmount = 300;
                $maxAmount = min(3000, $maxBalance);
                break;
        }

        $rules = [
            'amount' => [
                'required',
                'numeric',
                "min:{$minAmount}",
                "max:{$maxAmount}"
            ],
            'withdrawal_type' => 'required|string|in:crypto,etc_black,gold',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ];

        // Add conditional rules based on withdrawal type
        if ($this->withdrawal_type === 'crypto') {
            $rules['currency'] = 'required|string|in:USDTBEP20,USDCBEP20,FDUSDBEP20';
            $rules['wallet_address'] = 'required|string|max:255';
        } elseif ($this->withdrawal_type === 'gold') {
            $rules['branch_name'] = 'required|string|in:SHAH ALAM,BANGI,BANTING,ALOR SETAR,JELAWAT,KOTA TINGGI,BANDAR BARU UDA';
        }

        return $rules;
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

        // Determine limits based on withdrawal type
        $minAmount = 100;
        $maxAmount = $maxBalance;
        
        switch($this->withdrawal_type) {
            case 'crypto':
                $minAmount = 50;
                $maxAmount = min(300, $maxBalance);
                break;
            case 'etc_black':
                $minAmount = 100;
                $maxAmount = min(300, $maxBalance);
                break;
            case 'gold':
                $minAmount = 300;
                $maxAmount = min(3000, $maxBalance);
                break;
        }

        return [
            'amount.max' => "The withdrawal amount cannot exceed $" . number_format($maxAmount, 2),
            'amount.min' => "The minimum withdrawal amount is $" . number_format($minAmount, 2),
            'withdrawal_type.required' => 'Please select a withdrawal type',
            'currency.required' => 'Please select a currency',
            'currency.in' => 'Invalid currency selected',
            'wallet_address.required' => 'Wallet address is required',
            'branch_name.required' => 'Please select a branch for gold pickup',
            'branch_name.in' => 'Invalid branch selected',
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
