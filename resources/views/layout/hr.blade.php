<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HR File System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root { --sidebar-width: 260px; }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: #f8f9fa;
            color: #1f2937;
            min-height: 100vh;
        }

        /* =================== SIDEBAR NAV =================== */
        nav.sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            display: flex;
            flex-direction: column;
            z-index: 100;
            border-right: 2px solid #003d82;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            transition: transform 0.25s ease;
        }

        nav .brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            letter-spacing: -0.3px;
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        nav .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        nav .brand-icon svg {
            width: 16px;
            height: 16px;
            color: #fff;
        }
        nav .brand-text {
            display: flex;
            flex-direction: column;
        }
        nav .brand-short { display: none; }

        /* Nav links */
        nav .nav-links {
            flex: 1;
            padding: 1.25rem 0;
        }
        nav .nav-section-label {
            padding: 0.5rem 1.5rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        nav a,
        nav .nav-link-btn {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.85rem 1.5rem;
            border-left: 3px solid transparent;
            cursor: pointer;
            background: none;
            border-top: none;
            border-right: none;
            border-bottom: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }
        nav a:hover,
        nav .nav-link-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-left-color: #3b82f6;
        }
        nav a.active,
        nav .nav-link-btn.active {
            color: #fff;
            background: rgba(59, 130, 246, 0.15);
            border-left-color: #3b82f6;
        }
        nav .nav-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Dropdown */
        .nav-dropdown { position: relative; }
        .nav-dropdown-toggle {
            width: 100%;
            background: transparent;
            border: none;
            color: #cbd5e1;
            padding: 0.85rem 1.5rem;
            cursor: pointer;
            text-align: left;
            font: inherit;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-dropdown-toggle:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-left-color: #3b82f6;
        }
        .nav-dropdown-toggle.active {
            color: #fff;
            background: rgba(59, 130, 246, 0.15);
            border-left-color: #3b82f6;
        }
        .nav-dropdown-left {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .nav-dropdown-arrow {
            display: inline-block;
            transition: transform 0.2s ease;
            font-size: 0.7rem;
        }
        .nav-dropdown-menu {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.25s ease, opacity 0.2s ease;
        }
        .nav-dropdown.open .nav-dropdown-menu {
            max-height: 120px;
            opacity: 1;
        }
        .nav-dropdown.open .nav-dropdown-arrow {
            transform: rotate(180deg);
        }
        .nav-dropdown-menu a {
            padding: 0.6rem 1.5rem 0.6rem 3.25rem;
            font-size: 0.78rem;
            letter-spacing: 0.4px;
        }

        /* User section */
        nav .nav-user {
            padding: 1.25rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        nav .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }
        nav .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0066cc 0%, #003d82 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        nav .user-name {
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            display: block;
        }
        nav .user-role {
            color: #94a3b8;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        nav .nav-user a {
            padding: 0.45rem 0;
            border-left: none;
            font-size: 0.8rem;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        nav .nav-user a:hover {
            color: #f87171;
            background: transparent;
            border-left-color: transparent;
        }

        /* =================== SIDEBAR TOGGLE =================== */
        .sidebar-toggle {
            position: fixed;
            top: 0.75rem;
            z-index: 200;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: left 0.25s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
            background: transparent;
            border: none;
            color: #fff;
        }
        .sidebar-toggle:active { transform: scale(0.96); }

        /* Closed state */
        body.sidebar-closed nav.sidebar {
            transform: translateX(calc(-1 * var(--sidebar-width)));
        }
        body.sidebar-closed .main-wrapper {
            margin-left: 0;
        }
        body.sidebar-closed .sidebar-toggle {
            left: 0.5rem;
            background: rgba(0, 61, 130, 0.95);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            box-shadow: 0 8px 18px rgba(0,0,0,0.18);
        }
        body:not(.sidebar-closed) .sidebar-toggle {
            left: calc(var(--sidebar-width) - 52px);
        }

        /* Brand collapse */
        body.sidebar-closed .brand-full { display: none; }
        body.sidebar-closed .brand-short { display: flex; align-items: center; gap: 0.5rem; }

        /* =================== HEADER BAR =================== */
        .top-header {
            height: 56px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 30;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-icon-btn {
            position: relative;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #64748b;
            transition: background 0.15s, color 0.15s;
        }
        .header-icon-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .header-icon-btn svg {
            width: 18px;
            height: 18px;
        }
        .notif-dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 7px;
            height: 7px;
            background: #3b82f6;
            border-radius: 50%;
            border: 2px solid #fff;
        }
        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0066cc, #003d82);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.7rem;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .header-avatar:hover { opacity: 0.9; }

        /* =================== MAIN WRAPPER =================== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.25s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
            flex: 1;
        }

        /* =================== PAGE HEADER =================== */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid #e2e8f0;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .header-content {
            flex: 1;
            min-width: 250px;
        }
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .page-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.4rem;
            font-weight: 500;
        }
        .page-subtitle strong {
            color: #0f172a;
            font-weight: 700;
        }

        /* =================== ALERTS =================== */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            border-left: 4px solid;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left-color: #10b981;
        }
        .alert-error {
            background: #fef2f2;
            color: #7f1d1d;
            border-left-color: #ef4444;
        }

        /* =================== BUTTONS =================== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.4rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            letter-spacing: 0.3px;
        }
        .btn:active { transform: scale(0.97); }
        .btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0066cc 0%, #003d82 100%);
            color: #fff;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0052a3 0%, #002d5f 100%);
            box-shadow: 0 6px 16px rgba(0, 51, 130, 0.15);
        }
        .btn-danger {
            background: #dc2626;
            color: #fff;
        }
        .btn-danger:hover {
            background: #b91c1c;
        }
        .btn-outline {
            background: transparent;
            border: 1.5px solid #cbd5e1;
            color: #475569;
        }
        .btn-outline:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .btn-sm {
            padding: 0.5rem 0.9rem;
            font-size: 0.8rem;
        }

        /* =================== CARD =================== */
        .card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #0f172a;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* =================== TABLE =================== */
        .table-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .table-container {
            overflow-x: auto;
        }
        table, .employees-table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #fafbfc;
        }
        td {
            padding: 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #e2e8f0;
            color: #374151;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background-color 0.15s ease; }
        tbody tr:hover td { background: #f8fafc; }

        /* Table columns */
        .name-cell { font-weight: 600; color: #0f172a; }
        .department-cell, .position-cell, .folder-cell { font-size: 0.9rem; }
        .employment-cell { text-align: center; }
        .actions-column { min-width: 170px; }
        .actions-cell { text-align: center; }

        /* Employment badges */
        .employment-badge {
            display: inline-flex;
            padding: 0.35rem 0.8rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }
        .employment-badge.permanent {
            background: #d1fae5;
            color: #065f46;
        }
        .employment-badge.cos {
            background: #dbeafe;
            color: #0c4a6e;
        }

        /* Folder link styling */
        .folder-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
            border-bottom: 1px solid transparent;
        }
        .folder-link:hover {
            color: #0052a3;
            border-bottom-color: #0052a3;
        }
        .folder-pending {
            color: #94a3b8;
            font-weight: 500;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .delete-form {
            display: inline;
        }

        /* =================== BADGE =================== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef08a; color: #713f12; }
        .badge-blue { background: #dbeafe; color: #0c4a6e; }

        /* =================== EMPTY STATE =================== */
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
            background: #fafbfc;
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.7;
        }
        .empty-state-text {
            font-size: 1.05rem;
            color: #475569;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .empty-state-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border: 1.5px solid #0066cc;
            border-radius: 5px;
            transition: all 0.2s ease;
            display: inline-block;
        }
        .empty-state-link:hover {
            background: #0066cc;
            color: #fff;
        }

        /* =================== STATUS MESSAGE =================== */
        .status-message {
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            border-left: 4px solid;
        }
        .status-message.alert-error {
            background: #fef2f2;
            color: #7f1d1d;
            border-left-color: #ef4444;
        }

        /* =================== PAGINATION =================== */
        .pagination-wrapper {
            margin-top: 2rem;
            padding: 0 1rem;
        }

        /* =================== FORM =================== */
        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 0.9rem;
            font-family: inherit;
            color: #111827;
            background: #fff;
            transition: all 0.2s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08), 0 0 0 1px rgba(0, 102, 204, 0.2);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        .error-text {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.4rem;
            font-weight: 500;
        }

        /* =================== INFO GRID =================== */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .info-row {
            display: flex;
            padding: 0.85rem 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            color: #64748b;
            width: 45%;
            flex-shrink: 0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
        }
        .info-value {
            color: #111827;
            font-weight: 600;
        }

        /* =================== RESPONSIVE =================== */
        @media (max-width: 768px) {
            .sidebar-toggle { display: none; }
            nav.sidebar {
                width: 100%;
                height: auto;
                flex-direction: row;
                align-items: center;
                border-right: none;
                border-bottom: 2px solid #003d82;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                padding: 0 1rem;
                position: sticky;
                top: 0;
                transform: none !important;
            }
            nav .brand {
                padding: 1rem 0.5rem;
                border-bottom: none;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
                margin-right: auto;
            }
            nav .nav-links {
                flex: 1;
                padding: 0;
                display: flex;
                gap: 0;
            }
            nav a, nav .nav-link-btn, .nav-dropdown-toggle {
                padding: 1rem 0.75rem;
                border-left: none;
                border-bottom: 3px solid transparent;
                font-size: 0.8rem;
            }
            nav a:hover, nav .nav-link-btn:hover, .nav-dropdown-toggle:hover {
                border-left-color: transparent;
                border-bottom-color: #3b82f6;
                background: transparent;
            }
            nav a.active, nav .nav-link-btn.active, .nav-dropdown-toggle.active {
                border-left-color: transparent;
                border-bottom-color: #3b82f6;
            }
            .nav-dropdown-menu { display: none !important; }
            nav .nav-user {
                padding: 1rem 0.75rem;
                border-top: none;
                border-left: 1px solid rgba(255, 255, 255, 0.1);
            }
            nav .user-info { margin-bottom: 0; gap: 0.4rem; }
            nav .user-avatar { width: 32px; height: 32px; font-size: 0.8rem; }
            nav .user-details { display: none; }
            .main-wrapper { margin-left: 0 !important; }
            .top-header { display: none; }
        }
    </style>
</head>
<body>

    <button type="button" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="true">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
            <rect y="3" width="18" height="2" rx="1" fill="currentColor"/>
            <rect y="8" width="18" height="2" rx="1" fill="currentColor"/>
            <rect y="13" width="14" height="2" rx="1" fill="currentColor"/>
        </svg>
    </button>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="header-actions">
                <button class="header-icon-btn" aria-label="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-dot"></span>
                </button>
                <div class="header-avatar">AD</div>
            </div>
        </header>

        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        (function(){
            const btn = document.querySelector('.sidebar-toggle');
            if (!btn) return;

            const storageKey = 'sidebar_closed';
            const closed = (localStorage.getItem(storageKey) === '1');
            document.body.classList.toggle('sidebar-closed', closed);
            btn.setAttribute('aria-expanded', closed ? 'false' : 'true');

            btn.addEventListener('click', function(){
                const nowClosed = !document.body.classList.contains('sidebar-closed');
                document.body.classList.toggle('sidebar-closed', nowClosed);
                localStorage.setItem(storageKey, nowClosed ? '1' : '0');
                btn.setAttribute('aria-expanded', nowClosed ? 'false' : 'true');
            });
        })();
    </script>
</body>
</html>
