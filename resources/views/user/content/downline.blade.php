@extends('user.layouts.layoutMaster')

@section('title', 'My Downline')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite('resources/assets/vendor/libs/jstree/jstree.scss')
  <style>
    /* Custom JSTree Downline Styles */
    #jstree-downline .jstree-node {
        margin: 4px 0;
    }

    #jstree-downline .jstree-anchor {
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    #jstree-downline .jstree-anchor:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        border-color: rgba(var(--bs-primary-rgb), 0.2);
        transform: translateY(-1px);
    }

    #jstree-downline .jstree-clicked {
        background-color: rgba(var(--bs-primary-rgb), 0.15) !important;
        border-color: var(--bs-primary);
    }

    .downline-node {
        width: 100%;
        min-height: 50px;
    }

    .node-avatar .avatar-initial {
        width: 32px;
        height: 32px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .node-info {
        flex: 1;
        min-width: 0;
    }

    .node-username {
        font-size: 14px;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .node-code, .node-level {
        font-size: 12px;
        line-height: 1.1;
    }

    .node-level {
        font-weight: 500;
    }

    /* JSTree specific overrides */
    #jstree-downline .jstree-icon {
        display: none !important;
    }

    #jstree-downline .jstree-ocl {
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: none !important;
        border: none !important;
        display: none !important;
    }

    /* Hide default JSTree lines */
    #jstree-downline .jstree-node,
    #jstree-downline .jstree-children,
    #jstree-downline .jstree-container-ul > .jstree-node {
        background-image: none !important;
        background-repeat: no-repeat;
        background-position: left center;
    }

    /* Hide left tree lines but keep our custom ones */
    #jstree-downline .jstree-node:before,
    #jstree-downline .jstree-children:before {
        display: none !important;
    }

    #jstree-downline .jstree-ocl:hover {
        background: none !important;
        border: none !important;
    }

    /* Remove default JSTree connecting lines */
    #jstree-downline .jstree-node .jstree-ocl:before {
        display: none !important;
    }

    #jstree-downline .jstree-leaf > .jstree-ocl:before {
        content: '•';
        color: var(--bs-success);
    }

    /* JSTree specific styling fixes */
    #jstree-downline {
        background: transparent;
        border: none;
    }

    #jstree-downline .jstree-node {
        min-height: 60px;
        line-height: normal;
        margin-bottom: 8px;
    }

    #jstree-downline .jstree-anchor {
        height: auto;
        line-height: normal;
        padding: 0;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        display: block;
    }

    #jstree-downline .jstree-anchor:hover {
        background: none;
        text-decoration: none;
    }

    /* Make the entire card clickable */
    #jstree-downline .jstree-anchor .card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #jstree-downline .jstree-anchor .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    #jstree-downline .jstree-clicked {
        background: none !important;
        box-shadow: none;
    }

    #jstree-downline .jstree-wholerow {
        height: auto;
        min-height: 60px;
    }

    #jstree-downline .jstree-wholerow-hovered {
        background: rgba(var(--bs-primary-rgb), 0.05);
    }

    #jstree-downline .jstree-wholerow-clicked {
        background: rgba(var(--bs-primary-rgb), 0.1);
    }

    /* Tree lines and connectors */
    #jstree-downline .jstree-node,
    #jstree-downline .jstree-children,
    #jstree-downline .jstree-container-ul {
        background: transparent;
    }

    /* Enable tree lines and proper indentation */
    #jstree-downline .jstree-container-ul .jstree-container-ul {
        margin-left: 24px;
        position: relative;
    }

    /* Vertical connecting lines */
    #jstree-downline .jstree-container-ul .jstree-container-ul:before {
        content: '';
        position: absolute;
        left: -12px;
        top: -8px;
        bottom: 8px;
        width: 1px;
        background: #d1d5db;
        z-index: 1;
    }

    /* Horizontal connecting lines */
    #jstree-downline .jstree-node:before {
        content: '';
        position: absolute;
        left: -12px;
        top: 30px;
        width: 12px;
        height: 1px;
        background: #d1d5db;
        z-index: 1;
    }

    /* Last child styling - end the vertical line */
    #jstree-downline .jstree-node:last-child:after {
        content: '';
        position: absolute;
        left: -12px;
        top: 30px;
        bottom: -8px;
        width: 1px;
        background: white;
        z-index: 2;
    }

    /* Root level nodes don't need connecting lines */
    #jstree-downline > .jstree-container-ul > .jstree-node:before,
    #jstree-downline > .jstree-container-ul > .jstree-node:after {
        display: none;
    }

    /* Level-based indentation and visual hierarchy */
    #jstree-downline .jstree-node {
        position: relative;
        margin-left: 0;
    }

    /* Progressive indentation for each level */
    #jstree-downline .jstree-container-ul .jstree-node {
        margin-left: 20px;
    }

    #jstree-downline .jstree-container-ul .jstree-container-ul .jstree-node {
        margin-left: 20px;
    }

    #jstree-downline .jstree-container-ul .jstree-container-ul .jstree-container-ul .jstree-node {
        margin-left: 20px;
    }

    /* Enhanced tree lines for better visibility */
    #jstree-downline .jstree-children {
        position: relative;
    }

    #jstree-downline .jstree-children:before {
        content: '';
        position: absolute;
        left: -12px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: linear-gradient(to bottom, #d1d5db 0%, #d1d5db 90%, transparent 90%);
        z-index: 1;
    }

    #jstree-downline .jstree-node .jstree-ocl {
        width: 18px;
        height: 18px;
        line-height: 18px;
        margin-top: 20px;
        background: var(--bs-primary);
        border-radius: 50%;
        color: white;
        text-align: center;
        font-size: 12px;
    }

    #jstree-downline .jstree-node .jstree-ocl:before {
        content: "+";
        font-weight: bold;
    }

    #jstree-downline .jstree-open > .jstree-ocl:before {
        content: "−";
    }

    #jstree-downline .jstree-leaf > .jstree-ocl {
        background: var(--bs-light);
        color: var(--bs-muted);
    }

    #jstree-downline .jstree-leaf > .jstree-ocl:before {
        content: "•";
    }

    /* Level-based styling */
    .downline-node.level-1 { border-left: 3px solid var(--bs-primary); }
    .downline-node.level-2 { border-left: 3px solid var(--bs-info); }
    .downline-node.level-3 { border-left: 3px solid var(--bs-success); }
    .downline-node.level-4 { border-left: 3px solid var(--bs-warning); }
    .downline-node.level-5 { border-left: 3px solid var(--bs-orange); }
    .downline-node.level-6 { border-left: 3px solid var(--bs-danger); }
    .downline-node.level-7 { border-left: 3px solid var(--bs-purple); }
    .downline-node.level-8 { border-left: 3px solid var(--bs-pink); }
    .downline-node.level-9 { border-left: 3px solid var(--bs-teal); }
    .downline-node.level-10 { border-left: 3px solid var(--bs-cyan); }

    .downline-node {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin: 6px 0;
        transition: all 0.2s ease;
        position: relative;
    }

    .downline-node:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    /* Tree connector styling */
    .tree-connector {
        position: absolute;
        left: -15px;
        top: 50%;
        width: 15px;
        height: 1px;
        background: #d1d5db;
        z-index: 1;
    }

    .tree-connector:before {
        content: '';
        position: absolute;
        left: -1px;
        top: -25px;
        width: 1px;
        height: 25px;
        background: #d1d5db;
        z-index: 1;
    }

    /* Different connector styles for each level */
    .tree-connector.level-1:before {
        background: var(--bs-primary);
        opacity: 0.3;
    }

    .tree-connector.level-2:before {
        background: var(--bs-info);
        opacity: 0.3;
    }

    .tree-connector.level-3:before {
        background: var(--bs-success);
        opacity: 0.3;
    }

    .tree-connector.level-4:before {
        background: var(--bs-warning);
        opacity: 0.3;
    }

    /* Enhanced tree level indicators */
    .tree-level-0 {
        margin-left: 0 !important;
    }

    .tree-level-1 {
        margin-left: 20px !important;
        position: relative;
    }

    .tree-level-2 {
        margin-left: 40px !important;
        position: relative;
    }

    .tree-level-3 {
        margin-left: 60px !important;
        position: relative;
    }

    .tree-level-4 {
        margin-left: 80px !important;
        position: relative;
    }

    .tree-level-5 {
        margin-left: 100px !important;
        position: relative;
    }

    /* Visual hierarchy enhancement */
    .downline-node .node-info {
        position: relative;
        z-index: 2;
    }

    /* Level-based badge colors */
    .level-1 .badge.bg-label-primary { --bs-bg-opacity: 1; }
    .level-2 .badge.bg-label-info { --bs-bg-opacity: 1; }
    .level-3 .badge.bg-label-success { --bs-bg-opacity: 1; }

    /* Empty state styling */
    .downline-empty-state {
        padding: 3rem 2rem;
    }

    .avatar-lg {
        width: 4rem;
        height: 4rem;
    }

    .avatar-lg .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .downline-node {
            min-height: 45px;
        }

        .node-avatar .avatar-initial {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }

        .node-username {
            font-size: 13px;
        }

        .node-code, .node-level {
            font-size: 11px;
        }
    }

    /* Mobile Responsive Improvements */
    @media (max-width: 768px) {
        /* Container and wrapper improvements */
        .card-body {
            padding: 0.75rem !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #jstree-downline {
            min-width: 100%;
            overflow-x: visible;
        }

        /* Reduce indentation on mobile */
        #jstree-downline .jstree-container-ul .jstree-container-ul {
            margin-left: 16px !important;
        }

        /* Reduce connecting line spacing */
        #jstree-downline .jstree-container-ul .jstree-container-ul:before {
            left: -8px !important;
        }

        #jstree-downline .jstree-node:before {
            left: -8px !important;
            width: 8px !important;
        }

        /* Make cards more compact on mobile */
        #jstree-downline .jstree-node {
            min-height: 50px !important;
            margin-bottom: 6px !important;
        }

        .downline-card {
            padding: 8px 12px !important;
            min-height: 50px !important;
        }

        .node-avatar .avatar-initial {
            width: 28px !important;
            height: 28px !important;
            font-size: 12px !important;
        }

        .node-username {
            font-size: 13px !important;
            line-height: 1.1 !important;
            margin-bottom: 1px !important;
        }

        .node-code, .node-level {
            font-size: 10px !important;
        }

        /* Adjust referral count badge */
        .badge {
            font-size: 9px !important;
            padding: 2px 6px !important;
        }

        /* Improve tree container width management */
        .tree-level-1 { margin-left: 16px !important; }
        .tree-level-2 { margin-left: 32px !important; }
        .tree-level-3 { margin-left: 48px !important; }
        .tree-level-4 { margin-left: 64px !important; }
        .tree-level-5 { margin-left: 80px !important; }
    }

    /* Extra small screens (phones in portrait) */
    @media (max-width: 480px) {
        .card-body {
            padding: 0.5rem !important;
        }

        /* Further reduce indentation for very small screens */
        #jstree-downline .jstree-container-ul .jstree-container-ul {
            margin-left: 12px !important;
        }

        .tree-level-1 { margin-left: 12px !important; }
        .tree-level-2 { margin-left: 24px !important; }
        .tree-level-3 { margin-left: 36px !important; }
        .tree-level-4 { margin-left: 48px !important; }
        .tree-level-5 { margin-left: 60px !important; }

        .downline-card {
            padding: 6px 10px !important;
        }

        .node-avatar .avatar-initial {
            width: 24px !important;
            height: 24px !important;
            font-size: 11px !important;
        }

        .node-username {
            font-size: 12px !important;
        }

        .node-code, .node-level {
            font-size: 9px !important;
        }
    }

    /* Horizontal scroll container for tree overflow */
    .tree-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.3) transparent;
    }

    .tree-container::-webkit-scrollbar {
        height: 6px;
    }

    .tree-container::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.1);
        border-radius: 3px;
    }

    .tree-container::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.3);
        border-radius: 3px;
    }

    .tree-container::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.5);
    }
    }
  </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite('resources/assets/vendor/libs/jstree/jstree.js')
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite('resources/assets/js/extended-ui-treeview.js')

  <script>
    function buildDownlineTree(user, level = 0, maxLevel = 10) {
        // Stop recursion if we've reached the maximum level
        if (level >= maxLevel) {
            return null;
        }

        // Create level indicator for styling
        const levelClass = `level-${Math.min(level + 1, 10)}`;
        const levelColor = level === 0 ? 'primary' : level < 3 ? 'info' : level < 6 ? 'success' : 'warning';

        // Create responsive indentation padding based on level and screen size
        const baseIndent = window.innerWidth <= 480 ? 12 : window.innerWidth <= 768 ? 16 : 20;
        const indentPadding = level * baseIndent;
        const treeConnector = level > 0 ? `<span class="tree-connector level-${level}"></span>` : '';

        let node = {
            id: `user_${user.referral_code}_${level}`,
            text: `
                <div class="downline-card card border-0 shadow-sm mb-2" style="margin-left: ${indentPadding}px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            ${treeConnector}
                            <div class="node-avatar me-3">
                                <span class="avatar-initial rounded-circle bg-label-${levelColor} fs-6 fw-medium">
                                    ${user.username.charAt(0).toUpperCase()}
                                </span>
                            </div>
                            <div class="node-info flex-grow-1 min-width-0">
                                <div class="node-username fw-semibold text-body mb-1 text-truncate">${user.username}</div>
                                <div class="node-details d-flex flex-wrap gap-1 align-items-center">
                                    <span class="badge bg-label-secondary small">Code: ${user.referral_code}</span>
                                    <span class="badge bg-label-${levelColor} small">Level ${level + 1}</span>
                                    <span class="text-muted small d-flex align-items-center">
                                        <i class="ti ti-arrow-down-right me-1"></i>
                                        <span class="text-truncate">
                                            ${user.downlines && user.downlines.length > 0 ? user.downlines.length + ' ref' : 'No ref'}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            icon: false,
            children: [],
            state: {
                opened: level < 2 // Auto-open first 2 levels for better mobile performance
            },
            type: 'default',
            li_attr: {
                'data-level': level,
                'class': `tree-level-${level}`
            }
        };

        // Build children if they exist
        if (user.downlines && Array.isArray(user.downlines) && user.downlines.length > 0) {
            user.downlines.forEach(downline => {
                const childNode = buildDownlineTree(downline, level + 1, maxLevel);
                if (childNode) {
                    node.children.push(childNode);
                }
            });
        }

        // If no children, ensure children is empty array (not undefined)
        if (node.children.length === 0) {
            node.children = [];
        }

        return node;
    }

    @php
    function buildNestedDownlines($user, $level = 0, $maxLevel = 10) {
        if ($level >= $maxLevel) {
            return [];
        }

        $result = [
            'username' => $user->username,
            'referral_code' => $user->referral_code,
            'level' => $level,
            'downlines' => []
        ];

        if ($user->downlines && $user->downlines->count() > 0) {
            foreach ($user->downlines as $downline) {
                $childData = buildNestedDownlines($downline, $level + 1, $maxLevel);
                if (!empty($childData)) {
                    $result['downlines'][] = $childData;
                }
            }
        }

        return $result;
    }

    $downlineTreeData = [];
    foreach ($downlines as $downline) {
        $nestedData = buildNestedDownlines($downline, 0, 10);
        if (!empty($nestedData)) {
            $downlineTreeData[] = $nestedData;
        }
    }
    @endphp

    document.addEventListener('DOMContentLoaded', function() {
        let data = @json($downlineTreeData);

        if (data.length === 0) {
            $('#jstree-downline').html(`
                <div class="text-center py-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-label-secondary">
                            <i class="ti ti-users text-muted" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h6 class="mb-1">No Downlines Yet</h6>
                    <p class="text-muted small mb-0">Share your referral code to build your downline network</p>
                </div>
            `);
            return;
        }

        // Count total downlines and levels
        let totalDownlines = 0;
        let maxLevel = 0;

        function countDownlines(users, currentLevel = 0) {
            maxLevel = Math.max(maxLevel, currentLevel);
            users.forEach(user => {
                totalDownlines++;
                if (user.downlines && user.downlines.length > 0) {
                    countDownlines(user.downlines, currentLevel + 1);
                }
            });
        }

        countDownlines(data);

        // Destroy existing tree if it exists
        if ($.jstree.reference('#jstree-downline')) {
            $('#jstree-downline').jstree('destroy');
        }

        let treeData = data.map(user => buildDownlineTree(user));

        $('#jstree-downline').jstree({
            'core': {
                'data': treeData,
                'themes': {
                    'icons': false,
                    'stripes': false,
                    'responsive': true,
                    'dots': false,  // Disable tree connecting dots
                    'name': 'downline-tree'
                },
                'check_callback': true
            },
            'plugins': ['types', 'state', 'wholerow'],
            'types': {
                'default': {
                    'icon': false
                }
            },
            'state': {
                'key': 'downline_tree'
            },
            'wholerow': {
                'select_node': false
            }
        });

        // Auto-expand first few levels when tree is ready
        $('#jstree-downline').on('ready.jstree', function() {
            // Open first 2 levels automatically for better performance
            const tree = $(this).jstree(true);
            tree.open_node(tree.get_node('#'), false);

            // Enable single-click to expand/collapse nodes
            $(this).off('click.jstree', '.jstree-anchor').on('click.jstree', '.jstree-anchor', function (e) {
                var node = $(this).closest('.jstree-node');
                var nodeId = node.attr('id');

                if (node.hasClass('jstree-open')) {
                    tree.close_node(nodeId);
                } else if (node.hasClass('jstree-closed')) {
                    tree.open_node(nodeId);
                }
                e.preventDefault();
                return false;
            });

            // Expand root level nodes
            tree.get_children_dom('#').each(function() {
                tree.open_node(this, false);
            });

            // Update statistics
            updateDownlineStats(totalDownlines, maxLevel + 1);
        });

        // Add click handler for nodes
        $('#jstree-downline').on('select_node.jstree', function (e, data) {
            // Optional: Add custom actions when a node is clicked
            console.log('Node selected:', data.node.text);
        });

        // Handle tree loading
        $('#jstree-downline').on('loading.jstree', function() {
            console.log('Tree is loading...');
        });

        // Handle tree loaded
        $('#jstree-downline').on('loaded.jstree', function() {
            console.log('Tree loaded successfully');
        });
    });

    // Handle window resize for mobile responsiveness
    let resizeTimeout;
    $(window).on('resize orientationchange', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Refresh tree on orientation change for better mobile layout
            if ($('#jstree-downline').jstree(true)) {
                const tree = $('#jstree-downline').jstree(true);
                tree.refresh();
            }
        }, 300);
    });

    // Update downline statistics
    function updateDownlineStats(total, levels) {
        const statsHtml = `
            <div class="d-flex align-items-center justify-content-between text-sm">
                <div class="d-flex align-items-center">
                    <i class="ti ti-info-circle me-1 text-muted"></i>
                    <span class="text-muted">Click nodes to expand/collapse levels</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary">${total} Total</span>
                    <span class="badge bg-label-success">${levels} Level${levels !== 1 ? 's' : ''}</span>
                </div>
            </div>
        `;
        $('.card-footer').html(statsHtml);
    }

    // Copy referral code function
    function copyReferralCode() {
        const referralCode = '{{ auth()->user()->referral_code }}';
        navigator.clipboard.writeText(referralCode).then(function() {
            // Show success notification
            showNotification('Referral code copied to clipboard!', 'success');
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = referralCode;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Referral code copied to clipboard!', 'success');
        });
    }

    // Simple notification function
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' : 'alert-info';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
  </script>
@endsection

@section('content')
<div class="container py-4">
    {{-- <h2>My Downline Tree</h2> --}}
  <div id="downline-tree" class="d-none">
    @if($downlines->isEmpty())
      <p>No downlines found.</p>
    @else
      <ul>
        @foreach($downlines as $downline)
          @include('user.content.partials.downline_node', ['user' => $downline])
        @endforeach
      </ul>
    @endif
  </div>
</div>

<!-- JSTree -->
  <div class="row gy-6">
    <!-- View my Referral Code -->
    <div class="col-md-12 col-12">
      <div class="card">
        <div class="d-flex align-items-end row card-body">
          <div class="col-7">
            <h5 class="card-title mb-1">Share Your Network</h5>
            <p class="mb-3 text-muted">Send this code to your friends and grow your downline</p>
            <div class="d-flex align-items-center mb-2">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-primary">
                  <i class="ti ti-qrcode text-white"></i>
                </span>
              </div>
              <div>
                <div class="small text-muted">Your Referral Code</div>
                <h4 class="text-primary mb-0 fw-bold">{{ auth()->user()->referral_code }}</h4>
              </div>
            </div>
            <button class="btn btn-primary btn-sm" onclick="copyReferralCode()">
              <i class="ti ti-copy me-1"></i>Copy Code
            </button>
          </div>
          <div class="col-5 text-center">
            <img src="{{ asset('assets/svg/icons/undraw_referral_j2rw.svg')}}" class="img-fluid" alt="refer a friend" style="max-height: 120px;" />
          </div>
        </div>
      </div>
    </div>
    <!-- Basic -->
    <div class="col-md-12 col-12">
      <div class="card">
        @if(isset($upline) && $upline)
          <div class="alert alert-info mb-0 border-0 rounded-0">
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-info">
                  <i class="ti ti-arrow-up text-white"></i>
                </span>
              </div>
              <div>
                <strong>My Upline:</strong> {{ $upline->username }}
                <span class="badge bg-label-info ms-1">{{ $upline->referral_code }}</span>
              </div>
            </div>
          </div>
        @endif
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-1">My Downline Network</h5>
            <p class="card-subtitle text-muted mb-0">
              @if($downlines->count() > 0)
                {{ $downlines->count() }} direct referral{{ $downlines->count() !== 1 ? 's' : '' }} • Up to 10 levels
              @else
                No referrals yet
              @endif
            </p>
          </div>
          <div class="text-end">
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="ti ti-sitemap"></i>
              </span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="tree-container">
            <div id="jstree-downline"></div>
          </div>
        </div>
        @if($downlines->count() > 0)
          <div class="card-footer bg-label-light">
            <div class="d-flex align-items-center justify-content-between text-sm">
              <div class="d-flex align-items-center">
                <i class="ti ti-info-circle me-1 text-muted"></i>
                <span class="text-muted">Click nodes to expand/collapse levels</span>
              </div>
              <div class="d-flex align-items-center">
                <span class="badge bg-label-success">Active Network</span>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
    <!-- /Basic -->
  </div>
@endsection
