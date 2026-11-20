<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retail WMS Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #1f2937;
        }
        .sidebar {
            position: fixed;
            width: 230px;
            height: 100vh;
            background: #172033;
            color: white;
            padding: 28px 20px;
        }
        .sidebar h2 { font-size: 20px; margin-bottom: 35px; }
        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 13px 10px;
            border-radius: 7px;
            margin-bottom: 8px;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #334155;
            color: white;
        }
        main { margin-left: 230px; padding: 35px; }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        header h1 { margin: 0; font-size: 27px; }
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }
        .card, .panel {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }
        .card p { color: #64748b; margin: 0 0 12px; }
        .card h2 { font-size: 30px; margin: 0; }
        .panel h2 { font-size: 18px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th { color: #64748b; font-size: 13px; }
        .empty { color: #64748b; text-align: center; }
        @media(max-width: 900px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
            .sidebar { width: 180px; }
            main { margin-left: 180px; padding: 20px; }
        }
        @media(max-width: 600px) {
            .sidebar { position: static; width: 100%; height: auto; }
            main { margin-left: 0; padding: 16px; }
            .cards { grid-template-columns: 1fr 1fr; }
            .table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Retail WMS</h2>
        <a href="/" class="active">Dashboard</a>
        <a href="#">Products</a>
        <a href="#">Warehouses</a>
        <a href="#">Inventory</a>
        <a href="#">Movements</a>
    </aside>

    <main>
        <header>
            <h1>Dashboard</h1>
            <span>Warehouse Management System</span>
        </header>

        <section class="cards">
            <div class="card">
                <p>Total Products</p>
                <h2>{{ number_format($stats['products']) }}</h2>
            </div>
            <div class="card">
                <p>Active Warehouses</p>
                <h2>{{ number_format($stats['warehouses']) }}</h2>
            </div>
            <div class="card">
                <p>Total Stock</p>
                <h2>{{ number_format($stats['total_stock']) }}</h2>
            </div>
            <div class="card">
                <p>Low Stock Items</p>
                <h2>{{ number_format($stats['low_stock']) }}</h2>
            </div>
        </section>

        <section class="panel">
            <h2>Recent Inventory Movements</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements as $movement)
                            <tr>
                                <td>{{ $movement->product?->name ?? 'N/A' }}</td>
                                <td>{{ $movement->warehouse?->name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($movement->type) }}</td>
                                <td>{{ number_format($movement->quantity) }}</td>
                                <td>{{ $movement->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    No inventory movements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
