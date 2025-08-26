<style>
    /* Enhanced withdrawal card styling - Theme adaptive */
    .withdrawal-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        min-height: 160px;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .withdrawal-type-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-border-color), var(--bs-border-color-translucent));
        transition: all 0.3s ease;
    }

    .withdrawal-type-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    }

    .withdrawal-type-card.crypto-card {
        border-color: var(--bs-success);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }

    .withdrawal-type-card.crypto-card::before {
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    .withdrawal-type-card.crypto-card:hover {
        border-color: rgba(var(--bs-success-rgb), 0.8);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.3);
    }

    .withdrawal-type-card.crypto-card.border-primary {
        border-color: var(--bs-success) !important;
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        box-shadow: 0 8px 30px rgba(var(--bs-success-rgb), 0.4);
    }

    .withdrawal-type-card.etc-card {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
        box-shadow: 0 4px 15px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.15);
    }

    .withdrawal-type-card.etc-card::before {
        background: linear-gradient(90deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 1), var(--bs-secondary));
    }

    .withdrawal-type-card.etc-card:hover {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        background: linear-gradient(135deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 0.1) 0%, var(--bs-secondary-bg-subtle) 100%);
        box-shadow: 0 8px 25px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.3);
    }

    .withdrawal-type-card.etc-card.border-primary {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1) !important;
        background: linear-gradient(135deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 0.1) 0%, var(--bs-secondary-bg-subtle) 100%);
        box-shadow: 0 8px 30px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.4);
    }

    .withdrawal-type-card.gold-card {
        border-color: #fd7e14;
        box-shadow: 0 4px 15px rgba(253, 126, 20, 0.15);
    }

    .withdrawal-type-card.gold-card::before {
        background: linear-gradient(90deg, #fd7e14, #ffc107);
    }

    .withdrawal-type-card.gold-card:hover {
        border-color: #e55a00;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
        box-shadow: 0 8px 25px rgba(253, 126, 20, 0.3);
    }

    .withdrawal-type-card.gold-card.border-primary {
        border-color: #e55a00 !important;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
        box-shadow: 0 8px 30px rgba(253, 126, 20, 0.4);
    }

    .withdrawal-type-card .card-body {
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 1;
    }

    .withdrawal-type-card .card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .crypto-card .card-icon {
        color: var(--bs-success);
    }

    .crypto-card:hover .card-icon,
    .crypto-card.border-primary .card-icon {
        color: rgba(var(--bs-success-rgb), 0.8);
        transform: scale(1.1);
    }

    .etc-card .card-icon {
        color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
    }

    .etc-card:hover .card-icon,
    .etc-card.border-primary .card-icon {
        color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        transform: scale(1.1);
    }

    .gold-card .card-icon {
        color: #fd7e14;
    }

    .gold-card:hover .card-icon,
    .gold-card.border-primary .card-icon {
        color: #e55a00;
        transform: scale(1.1);
    }

    .withdrawal-type-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--bs-emphasis-color);
    }

    .withdrawal-type-card .card-text {
        color: var(--bs-secondary-color);
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .withdrawal-type-card .fee-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 15px;
        font-weight: 700;
        opacity: 1;
        border: 2px solid rgba(255, 255, 255, 0.8);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .crypto-card .fee-badge {
        background-color: rgba(var(--bs-success-rgb), 0.8);
        color: white;
        box-shadow: 0 2px 8px rgba(var(--bs-success-rgb), 0.3);
    }

    .etc-card .fee-badge {
        background-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        color: white;
        box-shadow: 0 2px 8px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.3);
    }

    .gold-card .fee-badge {
        background-color: #e55a00;
        color: white;
        box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);
    }

    /* Withdrawal History Table Styles - Theme adaptive */
    .withdrawal-history-table {
        font-size: 0.9rem;
    }

    .withdrawal-history-table th {
        background-color: var(--bs-secondary-bg);
        border-bottom: 2px solid var(--bs-border-color);
        font-weight: 600;
        color: var(--bs-emphasis-color);
        white-space: nowrap;
    }

    .withdrawal-history-table td {
        vertical-align: middle;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        color: var(--bs-body-color);
    }

    .withdrawal-history-table .type-badge {
        min-width: 110px;
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }

    .withdrawal-history-table .payment-details {
        max-width: 200px;
        font-size: 0.8rem;
    }

    .withdrawal-history-table .amount-cell {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-weight: 600;
    }

    .withdrawal-history-table .status-cell {
        min-width: 100px;
    }

    .withdrawal-history-table tbody tr:hover {
        background-color: var(--bs-tertiary-bg);
        transition: background-color 0.2s ease;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .withdrawal-type-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.crypto-card:hover {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.crypto-card.border-primary {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.etc-card:hover {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.etc-card.border-primary {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.gold-card:hover {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(253, 126, 20, 0.08) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.gold-card.border-primary {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(253, 126, 20, 0.08) 100%);
    }

    /* Page-specific enhancements */
    .withdrawal-page-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .withdrawal-page-header .back-button {
        margin-right: 1rem;
        color: var(--bs-secondary);
        text-decoration: none;
        font-size: 1.2rem;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .withdrawal-page-header .back-button:hover {
        color: var(--bs-primary);
        background-color: var(--bs-tertiary-bg);
    }

    .withdrawal-form-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .withdrawal-type-banner {
        text-align: center;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .withdrawal-type-banner.crypto-banner {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--bs-success-rgb), 0.2);
    }

    .withdrawal-type-banner.etc-banner {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(111, 66, 193, 0.05) 100%);
        border: 2px solid rgba(111, 66, 193, 0.2);
    }

    .withdrawal-type-banner.gold-banner {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
        border: 2px solid rgba(253, 126, 20, 0.2);
    }

    .withdrawal-type-banner .banner-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .withdrawal-type-banner.crypto-banner .banner-icon {
        color: var(--bs-success);
    }

    .withdrawal-type-banner.etc-banner .banner-icon {
        color: rgba(111, 66, 193, 1);
    }

    .withdrawal-type-banner.gold-banner .banner-icon {
        color: #fd7e14;
    }
</style>
