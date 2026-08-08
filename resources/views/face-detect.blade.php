<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detect Resident Face</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 650px;
            width: 100%;
        }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
        h2 { color: #333; text-align: center; margin-bottom: 10px; }
        .subtitle { color: #666; text-align: center; margin-bottom: 20px; }
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .result-container {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .result-container h3 { color: #333; margin-bottom: 15px; text-align: center; }
        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .result-item:last-child { border-bottom: none; }
        .result-label { color: #666; font-weight: 500; }
        .result-value { color: #333; font-weight: 600; }
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
            margin-bottom: 20px;
        }
        .file-upload-area:hover { border-color: #667eea; background: #f8f9fa; }
        .file-upload-area .icon { font-size: 3rem; display: block; margin-bottom: 10px; }
        input[type="file"] { display: none; }
        .preview-img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); }
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
        .confidence-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 1s ease;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('home') }}" class="back-link">← Back to Home</a>
        <h2>🔍 Face Detection</h2>
        <p class="subtitle">Upload a photo to identify the resident</p>

        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        @if(session('detection_result'))
            <div class="alert alert-success">✅ {{ session('detection_result')['message'] }}</div>

            @if(session('detection_result')['success'] && session('detection_result')['resident_details'])
                <div class="result-container">
                    <h3>👤 Resident Details</h3>

                    <div style="text-align: center;">
                        @if(session('detection_result')['resident_details']['face_image'])
                            <div class="resident-avatar">
                                <img src="{{ session('detection_result')['resident_details']['face_image'] }}" alt="Resident">
                            </div>
                        @else
                            <div class="resident-avatar">
                                {{ substr(session('detection_result')['resident_details']['name'], 0, 2) }}
                            </div>
                        @endif
                    </div>

                    <div class="result-item">
                        <span class="result-label">Name</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['name'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Resident Code</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['resident_code'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Phone</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['phone'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Email</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['email'] ?? 'N/A' }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Hostel</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['hostel'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Room</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['room'] }} (Bed {{ session('detection_result')['resident_details']['bed'] }})</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Joining Date</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['joining_date'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Food Status</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['food_status'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Rent</span>
                        <span class="result-value">{{ session('detection_result')['resident_details']['rent'] }}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Status</span>
                        <span class="result-value">
                            <span style="color: {{ session('detection_result')['resident_details']['status'] == 'ACTIVE' ? '#28a745' : '#dc3545' }};">
                                {{ session('detection_result')['resident_details']['status'] }}
                            </span>
                        </span>
                    </div>

                    <div style="margin-top: 15px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #666;">Confidence</span>
                            <span style="font-weight: 600; color: #667eea;">
                                {{ (session('detection_result')['confidence'] * 100) . '%' }}
                            </span>
                        </div>
                        <div class="confidence-bar">
                            <div class="confidence-fill" style="width: {{ (session('detection_result')['confidence'] * 100) . '%' }}"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">ℹ️ No matching resident found</div>
            @endif
        @endif

        <form action="{{ route('face.detect') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <div class="file-upload-area" onclick="document.getElementById('image').click()">
                    <span class="icon">📷</span>
                    <p><strong>Click to upload image</strong></p>
                    <small style="color: #666;">JPG, PNG, JPEG (Max 5MB)</small>
                </div>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn">🔍 Detect Face</button>
        </form>
    </div>
</body>
</html>
