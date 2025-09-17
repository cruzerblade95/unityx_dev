@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Global Withdrawal Defaults')

@section('vendor-style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection

@section('content')
    <div class="row g-6">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Global Withdrawal Default Settings</h4>
                    <a href="{{ route('admin.withdrawal-settings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to User Settings
                    </a>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        These are the default settings that will be applied to all users who don't have custom withdrawal
                        settings.
                    </div>

                    <div class="row">
                        <!-- Cryptocurrency Settings -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bitcoin"></i> Cryptocurrency
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="cryptoForm">
                                        <input type="hidden" value="crypto" id="crypto-type">

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crypto-enabled"
                                                    {{ $globalDefaults['crypto']->is_enabled ?? true ? 'checked' : '' }}>
                                                <label class="form-check-label" for="crypto-enabled">
                                                    Enable Cryptocurrency Withdrawals
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount</label>
                                            <input type="number" class="form-control" id="crypto-min"
                                                value="{{ $globalDefaults['crypto']->min_amount ?? 10.0 }}" step="0.01"
                                                min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount</label>
                                            <input type="number" class="form-control" id="crypto-max"
                                                value="{{ $globalDefaults['crypto']->max_amount ?? 10000.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Processing Fee (%)</label>
                                            <input type="number" class="form-control" id="crypto-fee-percent"
                                                value="{{ $globalDefaults['crypto']->processing_fee_percent ?? 4.75 }}"
                                                step="0.01" min="0" max="100" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Fixed Processing Fee</label>
                                            <input type="number" class="form-control" id="crypto-fee-fixed"
                                                value="{{ $globalDefaults['crypto']->processing_fee_fixed ?? 0.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cooldown Period (Hours)</label>
                                            <input type="number" class="form-control" id="crypto-cooldown"
                                                value="{{ $globalDefaults['crypto']->cooldown_hours ?? 30 }}" min="0"
                                                required>
                                        </div>

                                        <button type="button" class="btn btn-primary w-100"
                                            onclick="saveGlobalDefaults('crypto')">
                                            <i class="fas fa-save"></i> Save Crypto Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Crypto Bank Card Settings -->
                        <div class="col-md-4">
                            <div class="card border-dark">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-circle"></i> Crypto Bank Card
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="etcBlackForm">
                                        <input type="hidden" value="etc_black" id="etc-black-type">

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="etc-black-enabled"
                                                    {{ $globalDefaults['etc_black']->is_enabled ?? true ? 'checked' : '' }}>
                                                <label class="form-check-label" for="etc-black-enabled">
                                                    Enable Crypto Bank Card Withdrawals
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount</label>
                                            <input type="number" class="form-control" id="etc-black-min"
                                                value="{{ $globalDefaults['etc_black']->min_amount ?? 10.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount</label>
                                            <input type="number" class="form-control" id="etc-black-max"
                                                value="{{ $globalDefaults['etc_black']->max_amount ?? 10000.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Processing Fee (%)</label>
                                            <input type="number" class="form-control" id="etc-black-fee-percent"
                                                value="{{ $globalDefaults['etc_black']->processing_fee_percent ?? 4.75 }}"
                                                step="0.01" min="0" max="100" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Fixed Processing Fee</label>
                                            <input type="number" class="form-control" id="etc-black-fee-fixed"
                                                value="{{ $globalDefaults['etc_black']->processing_fee_fixed ?? 0.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cooldown Period (Hours)</label>
                                            <input type="number" class="form-control" id="etc-black-cooldown"
                                                value="{{ $globalDefaults['etc_black']->cooldown_hours ?? 30 }}"
                                                min="0" required>
                                        </div>

                                        <button type="button" class="btn btn-dark w-100"
                                            onclick="saveGlobalDefaults('etc_black')">
                                            <i class="fas fa-save"></i> Save Crypto Bank Card Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Gold Settings -->
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-coins"></i> Gold
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="goldForm">
                                        <input type="hidden" value="gold" id="gold-type">

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gold-enabled"
                                                    {{ $globalDefaults['gold']->is_enabled ?? true ? 'checked' : '' }}>
                                                <label class="form-check-label" for="gold-enabled">
                                                    Enable Gold Withdrawals
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount</label>
                                            <input type="number" class="form-control" id="gold-min"
                                                value="{{ $globalDefaults['gold']->min_amount ?? 10.0 }}" step="0.01"
                                                min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount</label>
                                            <input type="number" class="form-control" id="gold-max"
                                                value="{{ $globalDefaults['gold']->max_amount ?? 10000.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Processing Fee (%)</label>
                                            <input type="number" class="form-control" id="gold-fee-percent"
                                                value="{{ $globalDefaults['gold']->processing_fee_percent ?? 4.75 }}"
                                                step="0.01" min="0" max="100" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Fixed Processing Fee</label>
                                            <input type="number" class="form-control" id="gold-fee-fixed"
                                                value="{{ $globalDefaults['gold']->processing_fee_fixed ?? 0.0 }}"
                                                step="0.01" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cooldown Period (Hours)</label>
                                            <input type="number" class="form-control" id="gold-cooldown"
                                                value="{{ $globalDefaults['gold']->cooldown_hours ?? 30 }}"
                                                min="0" required>
                                        </div>

                                        <button type="button" class="btn btn-warning w-100"
                                            onclick="saveGlobalDefaults('gold')">
                                            <i class="fas fa-save"></i> Save Gold Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-info" onclick="setStandardDefaults()">
                                            <i class="fas fa-magic"></i> Set Standard Defaults
                                        </button>
                                        <button class="btn btn-success" onclick="enableAllWithdrawals()">
                                            <i class="fas fa-check-circle"></i> Enable All Withdrawals
                                        </button>
                                        <button class="btn btn-danger" onclick="disableAllWithdrawals()">
                                            <i class="fas fa-ban"></i> Disable All Withdrawals
                                        </button>
                                        <button class="btn btn-warning" onclick="setHighSecurityDefaults()">
                                            <i class="fas fa-shield-alt"></i> High Security Mode
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('page-script')
    <script>
        function saveGlobalDefaults(type) {
            const enabledCheckbox = document.getElementById(`${type.replace('_', '-')}-enabled`);

            const formData = {
                withdrawal_type: type,
                min_amount: parseFloat(document.getElementById(`${type.replace('_', '-')}-min`).value),
                max_amount: parseFloat(document.getElementById(`${type.replace('_', '-')}-max`).value),
                processing_fee_percent: parseFloat(document.getElementById(`${type.replace('_', '-')}-fee-percent`).value),
                processing_fee_fixed: parseFloat(document.getElementById(`${type.replace('_', '-')}-fee-fixed`).value),
                cooldown_hours: parseInt(document.getElementById(`${type.replace('_', '-')}-cooldown`).value),
                is_enabled: enabledCheckbox ? enabledCheckbox.checked : false
            };

            console.log('Saving settings for', type, ':', formData);

            fetch('/admin/withdrawal-settings/global-defaults/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(`${type.toUpperCase()} default settings updated successfully!`);
                    } else {
                        alert(`Error updating settings: ${data.message || 'Unknown error'}`);
                    }
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    alert(`Error saving settings: ${error.message}`);
                });
        }

        function setStandardDefaults() {
            if (!confirm('This will set standard default values for all withdrawal types. Continue?')) {
                return;
            }

            const standardSettings = {
                crypto: {
                    min: 10,
                    max: 10000,
                    feePercent: 4.75,
                    feeFixed: 0,
                    cooldown: 30,
                    enabled: true
                },
                etc_black: {
                    min: 10,
                    max: 10000,
                    feePercent: 4.75,
                    feeFixed: 0,
                    cooldown: 30,
                    enabled: true
                },
                gold: {
                    min: 10,
                    max: 10000,
                    feePercent: 4.75,
                    feeFixed: 0,
                    cooldown: 30,
                    enabled: true
                }
            };

            Object.keys(standardSettings).forEach(type => {
                const settings = standardSettings[type];
                const prefix = type.replace('_', '-');

                document.getElementById(`${prefix}-min`).value = settings.min;
                document.getElementById(`${prefix}-max`).value = settings.max;
                document.getElementById(`${prefix}-fee-percent`).value = settings.feePercent;
                document.getElementById(`${prefix}-fee-fixed`).value = settings.feeFixed;
                document.getElementById(`${prefix}-cooldown`).value = settings.cooldown;
                document.getElementById(`${prefix}-enabled`).checked = settings.enabled;

                saveGlobalDefaults(type);
            });
        }

        function enableAllWithdrawals() {
            if (!confirm('This will enable all withdrawal types. Continue?')) {
                return;
            }

            ['crypto', 'etc-black', 'gold'].forEach(type => {
                document.getElementById(`${type}-enabled`).checked = true;
                saveGlobalDefaults(type.replace('-', '_'));
            });
        }

        function disableAllWithdrawals() {
            if (!confirm('This will disable all withdrawal types. Continue?')) {
                return;
            }

            ['crypto', 'etc-black', 'gold'].forEach(type => {
                document.getElementById(`${type}-enabled`).checked = false;
                saveGlobalDefaults(type.replace('-', '_'));
            });
        }

        function setHighSecurityDefaults() {
            if (!confirm('This will set high security defaults (higher fees, longer cooldowns). Continue?')) {
                return;
            }

            const securitySettings = {
                crypto: {
                    min: 50,
                    max: 5000,
                    feePercent: 7.5,
                    feeFixed: 5,
                    cooldown: 72,
                    enabled: true
                },
                etc_black: {
                    min: 50,
                    max: 5000,
                    feePercent: 7.5,
                    feeFixed: 5,
                    cooldown: 72,
                    enabled: true
                },
                gold: {
                    min: 50,
                    max: 5000,
                    feePercent: 7.5,
                    feeFixed: 5,
                    cooldown: 72,
                    enabled: true
                }
            };

            Object.keys(securitySettings).forEach(type => {
                const settings = securitySettings[type];
                const prefix = type.replace('_', '-');

                document.getElementById(`${prefix}-min`).value = settings.min;
                document.getElementById(`${prefix}-max`).value = settings.max;
                document.getElementById(`${prefix}-fee-percent`).value = settings.feePercent;
                document.getElementById(`${prefix}-fee-fixed`).value = settings.feeFixed;
                document.getElementById(`${prefix}-cooldown`).value = settings.cooldown;
                document.getElementById(`${prefix}-enabled`).checked = settings.enabled;

                saveGlobalDefaults(type);
            });
        }

        // Add event listeners for automatic saving when checkboxes are changed
        document.addEventListener('DOMContentLoaded', function() {
            // Add change event listeners to all enabled checkboxes
            ['crypto', 'etc-black', 'gold'].forEach(type => {
                const checkbox = document.getElementById(`${type}-enabled`);
                if (checkbox) {
                    checkbox.addEventListener('change', function() {
                        const withdrawalType = type.replace('-', '_');
                        console.log(`Checkbox changed for ${withdrawalType}:`, this.checked);
                        saveGlobalDefaults(withdrawalType);
                    });
                }
            });
        });
    </script>
@endsection
