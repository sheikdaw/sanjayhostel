<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Residents</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .back-link { color: #667eea; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        h2 { color: #333; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; font-size: 0.85rem; }
        .resident-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .resident-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .resident-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .resident-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            overflow: hidden;
        }
        .resident-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .resident-name { text-align: center; font-weight: 600; color: #333; font-size: 1.1rem; }
        .resident-code { text-align: center; color: #667eea; font-size: 0.85rem; }
        .resident-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 0.85rem;
        }
        .resident-details .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .resident-details .label { color: #666; }
        .resident-details .value { color: #333; font-weight: 500; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-top: 5px;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-top: 10px;
            width: 100%;
            transition: background 0.3s;
        }
        .delete-btn:hover { background: #c82333; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state .icon { font-size: 4rem; display: block; margin-bottom: 20px; }
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 480px) {
            .container { padding: 20px; }
            .header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <a href="{{ route('home') }}" class="back-link">← Back</a>
                <h2>👥 Residents with Face Registration</h2>
            </div>
            <a href="{{ route('face.register.form') }}" style="
                padding: 10px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                text-decoration: none;
                display: inline-block;
            ">+ Register New</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Active Residents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;">{{ $stats['registered'] }}</div>
                <div class="stat-label">Face Registered</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending Registration</div>
            </div>
        </div>

        @if($residents->count() > 0)
            <div class="resident-grid">
                @foreach($residents as $resident)
                    <div class="resident-card">
                        <div class="resident-avatar">
                            @if($resident->face_image_path)
                                <img src="{{ asset('storage/' . $resident->face_image_path) }}" alt="{{ $resident->name }}">
                            @else
                                {{ $resident->initials }}
                            @endif
                        </div>
                        <div class="resident-name">{{ $resident->name }}</div>
                        <div class="resident-code">{{ $resident->resident_code }}</div>

                        <div class="resident-details">
                            <div class="row">
                                <span class="label">📱 Phone</span>
                                <span class="value">{{ $resident->phone }}</span>
                            </div>
                            <div class="row">
                                <span class="label">🏠 Hostel</span>
                                <span class="value">{{ $resident->hostel->hostel_name ?? 'N/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="label">🛏️ Room</span>
                                <span class="value">{{ $resident->room->room_no ?? 'N/A' }} (Bed {{ $resident->bed->bed_no ?? 'N/A' }})</span>
                            </div>
                            <div class="row">
                                <span class="label">🍽️ Food</span>
                                <span class="value">{{ $resident->food_status_label }}</span>
                            </div>
                            <div class="row">
                                <span class="label">💰 Rent</span>
                                <span class="value">{{ $resident->formatted_rent }}</span>
                            </div>
                            <div class="row">
                                <span class="label">📅 Registered</span>
                                <span class="value">{{ $resident->face_registered_at ? $resident->face_registered_at->format('d M Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        <span class="badge badge-success">✅ Face Registered</span>

                        <form action="{{ route('face.delete', $resident->face_id) }}" method="POST" onsubmit="return confirm('Remove face registration for {{ $resident->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn">🗑️ Remove Registration</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <span class="icon">📭</span>
                <p>No residents have registered faces yet</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">
                    <a href="{{ route('face.register.form') }}" style="color: #667eea;">Register a face</a> to get started
                </p>
            </div>
        @endif
    </div>
</body>
</html>
