
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <title>@yield('title', 'Retail WMS') | Retail WMS</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Application Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Application Stylesheets --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sales-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
    <link
    rel="stylesheet"
    href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}"
>

    @stack('styles')
</head>

<body>

<div class="app">

    {{-- =========================================
         SIDEBAR
    ========================================== --}}

    <aside class="sidebar">

        {{-- Brand --}}
        <div class="brand">
            <div class="brand-icon">R</div>

            <div class="brand-copy">
                <div class="brand-name">Retail WMS</div>
                <div class="brand-sub">ENTERPRISE PLATFORM</div>
            </div>
        </div>

        {{-- Workspace Navigation --}}
        <div class="nav-label">Workspace</div>

        @can('dashboard.view')
            <a
                href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <span class="nav-icon">▦</span>
                <span class="nav-text">Dashboard</span>
            </a>
        @endcan

        @can('products.view')
            <a
                href="{{ route('products.index') }}"
                class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▤</span>
                <span class="nav-text">Products</span>
            </a>
        @endcan

        @can('categories.view')
            <a
                href="{{ route('categories.index') }}"
                class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▧</span>
                <span class="nav-text">Categories</span>
            </a>
        @endcan

        @can('warehouses.view')
            <a
                href="{{ route('warehouses.index') }}"
                class="nav-link {{ request()->routeIs('warehouses.*', 'warehouse-locations.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▣</span>
                <span class="nav-text">Warehouses</span>
            </a>
        @endcan

        @can('inventory.view')
            <a
                href="{{ route('inventory.index') }}"
                class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">📦</span>
                <span class="nav-text">Inventory</span>
            </a>
        @endcan

        @can('inventory.receive')
            <a
                href="{{ route('stock-receiving.create') }}"
                class="nav-link {{ request()->routeIs('stock-receiving.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">＋</span>
                <span class="nav-text">Receive Stock</span>
            </a>
        @endcan

        @can('stock-movements.view')
            <a
                href="{{ route('stock-movements.index') }}"
                class="nav-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">⇅</span>
                <span class="nav-text">Stock Movements</span>
            </a>
        @endcan

        @can('inventory.transfer')
            <a
                href="{{ route('stock-transfers.create') }}"
                class="nav-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">⇄</span>
                <span class="nav-text">Stock Transfer</span>
            </a>
        @endcan

        {{-- =========================================
             BUSINESS OPERATIONS
        ========================================== --}}

        @if(auth()->user()->hasAnyPermission([
            'suppliers.view',
            'purchase-orders.view',
            'customers.view',
            'sales-orders.view'
        ]))

            <div class="nav-label">Business Operations</div>

            @can('suppliers.view')
                <a
                    href="{{ route('suppliers.index') }}"
                    class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">▣</span>
                    <span class="nav-text">Suppliers</span>
                </a>
            @endcan

            @can('purchase-orders.view')
                <a
                    href="{{ route('purchase-orders.index') }}"
                    class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">🛒</span>
                    <span class="nav-text">Purchase Orders</span>
                </a>
            @endcan

            @can('customers.view')
                <a
                    href="{{ route('customers.index') }}"
                    class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Customers</span>
                </a>
            @endcan

            @can('sales-orders.view')
                <a
                    href="{{ route('sales-orders.index') }}"
                    class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">▣</span>
                    <span class="nav-text">Sales Orders</span>
                </a>
            @endcan

        @endif

        {{-- =========================================
            MANAGEMENT
        ========================================== --}}

        @if(auth()->user()->hasAnyPermission([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.manage',
            'settings.manage'
        ]))

            <div class="nav-label">Management</div>

            @can('users.view')
                <a
                    href="{{ route('users.index') }}"
                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">♙</span>
                    <span class="nav-text">Users</span>
                </a>
            @endcan

            @can('settings.manage')
                <a href="#" class="nav-link">
                    <span class="nav-icon">⚙</span>
                    <span class="nav-text">Settings</span>
                </a>
            @endcan

        @endif

        {{-- Sidebar Footer --}}
        <div class="sidebar-bottom">
            <div class="brand-sub">RETAIL WMS</div>
            <p>Enterprise inventory and warehouse management.</p>
        </div>

    </aside>

    {{-- =========================================
         MAIN CONTENT
    ========================================== --}}

    <main class="main">

        {{-- Topbar --}}
        <header class="topbar">

            <div class="breadcrumb">
                Workspace /
                <strong>@yield('breadcrumb', 'Products')</strong>
            </div>

            <div class="top-actions">

                <button
                    class="icon-button"
                    type="button"
                    aria-label="Refresh page"
                    onclick="window.location.reload()"
                >
                    ↻
                </button>

                {{-- Notification Center --}}
                @include('notifications.partials.center')

                {{-- Logged-in User Profile --}}
                <div class="profile">

                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="profile-copy">

                        <div class="profile-name">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="profile-role">
                            {{ auth()->user()->getRoleNames()->implode(', ') ?: 'No Role Assigned' }}
                        </div>

                    </div>

                </div>

            </div>

        </header>

        {{-- =========================================
             PAGE CONTENT
        ========================================== --}}

        <div class="content">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="flash flash-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error Message --}}
            @if(session('error'))
                <div class="flash flash-error">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Validation Errors --}}
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

            {{-- Main View --}}
            @yield('content')

            {{-- =========================================
                 REALTIME WEBSOCKET DEMO
            ========================================== --}}

            <section
                id="realtime-demo"
                class="panel"
                style="margin-top: 24px; padding: 24px;"
            >

                <div style="margin-top: 16px;">

                    <p
                        id="socket-status"
                        role="status"
                        aria-live="polite"
                        style="font-weight: 600;"
                    >
                        🟡 Connecting to Reverb...
                    </p>

                    <button
                        type="button"
                        id="send-realtime-test"
                        class="btn btn-primary"
                    >
                        🔔 Send Test Socket Notification
                    </button>

                    <div
                        id="socket-message"
                        aria-live="polite"
                        style="margin-top: 16px;"
                    ></div>

                </div>

            </section>

            {{-- Footer --}}
            <footer class="footer">
                © {{ date('Y') }} Retail WMS.
                Enterprise Retail & Warehouse Management.
            </footer>

        </div>

    </main>

</div>

{{-- Page-specific JavaScript --}}
@stack('scripts')

</body>
</html>
