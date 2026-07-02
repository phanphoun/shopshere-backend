<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }} Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,0.04);
            --sidebar-active: rgba(59,130,246,0.15);
            --sidebar-border: rgba(255,255,255,0.08);
            --sidebar-text: #94a3b8;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f6f8fb;
            color: #1e293b;
            font-size: 0.925rem;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
            color: #fff;
            padding-top: 22px;
            padding-bottom: 24px;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,0.06);
            box-shadow: 1px 0 24px rgba(15,23,42,0.25);
            z-index: 1020;
        }
        .sidebar-brand {
            padding: 0 22px 18px;
            font-size: 1.35rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid var(--sidebar-border);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand i {
            color: #60a5fa;
            font-size: 1.2rem;
        }
        .sidebar .nav {
            padding: 0 10px;
        }
        .sidebar a.nav-link {
            color: var(--sidebar-text);
            padding: 11px 14px;
            border-radius: 10px;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.18s ease;
            margin-bottom: 2px;
            text-decoration: none;
        }
        .sidebar a.nav-link i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }
        .sidebar a.nav-link:hover {
            background: var(--sidebar-hover);
            color: #e2e8f0;
        }
        .sidebar a.nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            border-left-color: #3b82f6;
        }
        .sidebar .nav-divider {
            border-top: 1px solid var(--sidebar-border);
            margin: 14px 12px;
        }
        .sidebar .logout-btn {
            margin-top: auto;
        }
        .sidebar form .nav-link {
            cursor: pointer;
        }
        .main {
            margin-left: 260px;
            padding: 24px 28px;
            min-height: 100vh;
        }
        .topbar {
            background: rgba(255,255,255,0.7);
            backdrop-filter: saturate(180%) blur(10px);
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 22px;
            border: 1px solid rgba(255,255,255,0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 22px rgba(15,23,42,0.06);
        }
        .page-title {
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .topbar .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.65);
            border: 1px solid rgba(255,255,255,0.8);
            padding: 6px 12px;
            border-radius: 999px;
        }
        .topbar .user-chip img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .card {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04), 0 6px 18px rgba(15,23,42,0.04);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 1px 2px rgba(15,23,42,0.06), 0 10px 26px rgba(15,23,42,0.07);
        }
        .stat-card {
            padding: 18px 20px;
            border-radius: 14px;
            background: #ffffff;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04), 0 6px 18px rgba(15,23,42,0.04);
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1px 2px rgba(15,23,42,0.06), 0 14px 28px rgba(15,23,42,0.08);
        }
        .stat-card .icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }
        .stat-card .value {
            font-size: 1.85rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .table thead th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 12px 14px;
            border-bottom: 1px solid #eef1f6;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 14px 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr {
            transition: background-color 0.15s ease;
        }
        .table tbody tr:hover {
            background-color: #fafbfd;
        }
        .badge {
            font-weight: 500;
            padding: 0.4em 0.75em;
            border-radius: 999px;
            letter-spacing: 0.01em;
        }
        .btn {
            font-weight: 500;
            border-radius: 10px;
        }
        .product-thumb {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }
        .filter-bar {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .filter-bar .form-control,
        .filter-bar .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            min-height: 38px;
        }
        .table-wrapper {
            border-radius: 14px;
            overflow: hidden;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main {
                margin-left: 0;
                padding: 18px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-shop"></i> ShopSphere
        </a>

        <nav class="nav flex-column">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <i class="bi bi-cart-check"></i> Orders
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a href="{{ route('admin.support.index') }}" class="nav-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Support
            </a>
            <div class="nav-divider"></div>
            <form method="POST" action="{{ route('admin.logout') }}" class="logout-btn">
                @csrf
                <button type="submit" class="nav-link bg-transparent border-0 w-100 text-start" style="cursor:pointer">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h4 class="m-0 page-title">@yield('page_title', 'Dashboard')</h4>
            </div>
            <div class="user-chip">
                <span class="me-2 text-muted">{{ auth()->user()->name }}</span>
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="avatar">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:32px;height:32px;background:linear-gradient(145deg,#0ea5e9,#2563eb);font-size:0.85rem;">
                        {{ strtoupper(Str::substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>
</html>
