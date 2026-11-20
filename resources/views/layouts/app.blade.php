
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Retail WMS') | Retail WMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">R</div>
            <div class="brand-copy">
                <div class="brand-name">Retail WMS</div>
                <div class="brand-sub">ENTERPRISE PLATFORM</div>
            </div>
        </div>

        <div class="nav-label">Workspace</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">▦</span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <span class="nav-icon">▤</span>
            <span class="nav-text">Products</span>
        </a>

        <a href="{{ route('categories.index') }}"
           class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <span class="nav-icon">▧</span>
            <span class="nav-text">Categories</span>
        </a>

<a href="{{ route('warehouses.index') }}"
   class="nav-link {{ request()->routeIs('warehouses.*', 'warehouse-locations.*') ? 'active' : '' }}">
    <span class="nav-icon">▣</span>
    <span class="nav-text">Warehouses</span>
</a>

        <a href="#" class="nav-link">
            <span class="nav-icon">⇄</span>
            <span class="nav-text">Inventory</span>
        </a>

        <a href="#" class="nav-link">
            <span class="nav-icon">⇅</span>
            <span class="nav-text">Stock Movements</span>
        </a>

        <div class="nav-label">Business Operations</div>
        <a href="#" class="nav-link">
            <span class="nav-icon">♧</span>
            <span class="nav-text">Suppliers</span>
        </a>
        <a href="#" class="nav-link">
            <span class="nav-icon">🛒</span>
            <span class="nav-text">Purchase Orders</span>
        </a>
        <a href="#" class="nav-link">
            <span class="nav-icon">♙</span>
            <span class="nav-text">Customers</span>
        </a>
        <a href="#" class="nav-link">
            <span class="nav-icon">▣</span>
            <span class="nav-text">Sales Orders</span>
        </a>

        <div class="nav-label">Management</div>
        <a href="#" class="nav-link">
            <span class="nav-icon">♙</span>
            <span class="nav-text">Users & Roles</span>
        </a>
        <a href="#" class="nav-link">
            <span class="nav-icon">⚙</span>
            <span class="nav-text">Settings</span>
        </a>

        <div class="sidebar-bottom">
            <div class="brand-sub">RETAIL WMS · VERSION 1.0</div>
            <p>Enterprise inventory and warehouse management.</p>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="breadcrumb">
                Workspace / <strong>@yield('breadcrumb', 'Products')</strong>
            </div>

            <div class="top-actions">
                <button class="icon-button" type="button"
                        aria-label="Refresh page"
                        onclick="window.location.reload()">↻</button>

                <div class="profile">
                    <div class="avatar">A</div>
                    <div class="profile-copy">
                        <div class="profile-name">Administrator</div>
                        <div class="profile-role">System Admin</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="flash flash-error">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <footer class="footer">
                © {{ date('Y') }} Retail WMS. Enterprise Retail & Warehouse Management.
            </footer>
        </div>
    </main>
</div>
</body>
</html>
