<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Resident Face</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-link { color: #667eea; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        h2 { color: #333; font-size: 1.8rem; }
        .subtitle { color: #666; text-align: center; margin-bottom: 25px; font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; font-size: 0.95rem; }
        select, input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
            background: white;
        }
        select:focus, input:focus { outline: none; border-color: #667eea; }
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
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .webcam-container { margin: 20px 0; text-align: center; position: relative; }
        video { border-radius: 12px; background: #f0f0f0; width: 100%; max-width: 400px; border: 3px solid #e9ecef; }
        .webcam-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .btn-capture {
            padding: 10px 25px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-capture:hover { background: #218838; transform: translateY(-2px); }
        .btn-clear {
            padding: 10px 25px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-clear:hover { background: #c82333; transform: translateY(-2px); }
        canvas { display: none; }
        .captured-preview {
            margin-top: 15px;
            position: relative;
            display: none;
        }
        .captured-preview img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 12px;
            border: 3px solid #667eea;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .resident-info {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .resident-info .label { color: #666; font-size: 0.85rem; }
        .resident-info .value { color: #333; font-weight: 500; font-size: 1rem; }
        .resident-info .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }
        .file-upload-area:hover { border-color: #667eea; background: #f8f9fa; }
        .file-upload-area .icon { font-size: 2rem; display: block; margin-bottom: 5px; }
        input[type="file"] { display: none; }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number { font-size: 1.5rem; font-weight: bold; color: #667eea; }
        .stat-label { font-size: 0.75rem; color: #666; }
        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .filter-bar select { flex: 1; padding: 8px 12px; }
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 15px;
            overflow: hidden;
            display: none;
        }
        .progress-bar .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s;
            border-radius: 2px;
        }
        @media (max-width: 480px) {
            .container { padding: 25px; }
            .resident-info .grid { grid-template-columns: 1fr; }
            .stats-bar { grid-template-columns: 1fr; }
            .webcam-controls { flex-direction: column; }
            .btn-capture, .btn-clear { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ route('home') }}" class="back-link">← Back</a>
            <span style="font-size: 1.5rem;">🏠</span>
        </div>

        <h2>📸 Register Resident Face</h2>
        <p class="subtitle">Select a resident and capture/upload their photo for face recognition</p>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number">{{ $stats['total_residents'] }}</div>
                <div class="stat-label">Total Residents</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #28a745;">{{ $stats['face_registered'] }}</div>
                <div class="stat-label">Face Registered</div>
            </div>
            <div class="stat-item">
                {{-- <div class="stat-number" style="color: #dc3545;">{{ $stats['pending'] }}</div> --}}
                <div class="stat-label">Pending Registration</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        @if($residents->count() == 0)
            <div class="alert alert-info">ℹ️ No active residents available for face registration.</div>
        @endif

        <div class="filter-bar">
            <select id="hostelFilter" onchange="filterResidents()">
                <option value="">All Hostels</option>
                @foreach($hostels as $hostel)
                    <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                @endforeach
            </select>
        </div>

        <form action="{{ route('face.register') }}" method="POST" enctype="multipart/form-data" id="registerForm">
            @csrf

            <div class="form-group">
                <label for="resident_id">👤 Select Resident</label>
                <select id="resident_id" name="resident_id" required>
                    <option value="">-- Select a resident --</option>
                    @foreach($residents as $resident)
                        <option value="{{ $resident->id }}"
                                data-name="{{ $resident->name }}"
                                data-code="{{ $resident->resident_code }}"
                                data-phone="{{ $resident->phone }}"
                                data-hostel="{{ $resident->hostel->hostel_name ?? 'N/A' }}"
                                data-room="{{ $resident->room->room_no ?? 'N/A' }}"
                                data-bed="{{ $resident->bed->bed_no ?? 'N/A' }}"
                                data-food="{{ $resident->food_status_label }}"
                                data-rent="{{ $resident->formatted_rent }}">
                            {{ $resident->name }} ({{ $resident->resident_code }})
                            @if($resident->face_registered) ✅ @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="residentInfo" class="resident-info" style="display: none;">
                <div class="grid">
                    <div><span class="label">Name:</span> <span class="value" id="displayName">-</span></div>
                    <div><span class="label">Resident Code:</span> <span class="value" id="displayCode">-</span></div>
                    <div><span class="label">Phone:</span> <span class="value" id="displayPhone">-</span></div>
                    <div><span class="label">Hostel:</span> <span class="value" id="displayHostel">-</span></div>
                    <div><span class="label">Room:</span> <span class="value" id="displayRoom">-</span></div>
                    <div><span class="label">Bed:</span> <span class="value" id="displayBed">-</span></div>
                    <div><span class="label">Food Status:</span> <span class="value" id="displayFood">-</span></div>
                    <div><span class="label">Rent:</span> <span class="value" id="displayRent">-</span></div>
                </div>
            </div>

            <div class="webcam-container">
                <video id="video" autoplay playsinline></video>
                <div class="webcam-controls">
                    <button type="button" class="btn-capture" onclick="startCamera()" id="startCameraBtn">📷 Start Camera</button>
                    <button type="button" class="btn-capture" onclick="capturePhoto()" id="captureBtn" style="display: none;">📸 Capture</button>
                    <button type="button" class="btn-clear" onclick="clearImage()" id="clearBtn" style="display: none;">🗑️ Clear</button>
                </div>
                <canvas id="canvas"></canvas>
                <div class="captured-preview" id="previewContainer">
                    <img id="capturedImage" alt="Captured face">
                    <span style="position: absolute; top: 10px; right: 10px; background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">✅ Captured</span>
                </div>
            </div>

            <div class="form-group">
                <label for="image">📁 Or Upload Image</label>
                <div class="file-upload-area" onclick="document.getElementById('image').click()">
                    <span class="icon">🖼️</span>
                    <span>Click to upload resident photo</span>
                    <span id="fileName" style="display: block; color: #667eea; font-weight: 500; margin-top: 5px;"></span>
                </div>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="progress-bar" id="progressBar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <button type="submit" class="btn" id="submitBtn" disabled>📝 Register Face</button>
        </form>

        <div style="margin-top: 15px; text-align: center; font-size: 0.85rem; color: #999;">
            <small>Supported: JPG, PNG, JPEG (Max 5MB) • Face must be clearly visible</small>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturedImage = document.getElementById('capturedImage');
        const previewContainer = document.getElementById('previewContainer');
        const imageInput = document.getElementById('image');
        const submitBtn = document.getElementById('submitBtn');
        const fileName = document.getElementById('fileName');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const captureBtn = document.getElementById('captureBtn');
        const clearBtn = document.getElementById('clearBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const residentSelect = document.getElementById('resident_id');
        const residentInfo = document.getElementById('residentInfo');

        let stream = null;
        let isCameraStarted = false;

        residentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value) {
                residentInfo.style.display = 'block';
                document.getElementById('displayName').textContent = selectedOption.dataset.name || '-';
                document.getElementById('displayCode').textContent = selectedOption.dataset.code || '-';
                document.getElementById('displayPhone').textContent = selectedOption.dataset.phone || '-';
                document.getElementById('displayHostel').textContent = selectedOption.dataset.hostel || '-';
                document.getElementById('displayRoom').textContent = selectedOption.dataset.room || '-';
                document.getElementById('displayBed').textContent = selectedOption.dataset.bed || '-';
                document.getElementById('displayFood').textContent = selectedOption.dataset.food || '-';
                document.getElementById('displayRent').textContent = selectedOption.dataset.rent || '-';
            } else {
                residentInfo.style.display = 'none';
            }
            validateForm();
        });

        function filterResidents() {
            const hostelId = document.getElementById('hostelFilter').value;
            if (hostelId) {
                fetch(`/api/residents-by-hostel?hostel_id=${hostelId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            residentSelect.innerHTML = '<option value="">-- Select a resident --</option>';
                            data.data.forEach(resident => {
                                const opt = document.createElement('option');
                                opt.value = resident.id;
                                opt.dataset.name = resident.name;
                                opt.dataset.code = resident.resident_code;
                                opt.dataset.phone = resident.phone;
                                opt.dataset.hostel = resident.hostel?.hostel_name || 'N/A';
                                opt.dataset.room = resident.room?.room_no || 'N/A';
                                opt.dataset.bed = resident.bed?.bed_no || 'N/A';
                                opt.dataset.food = resident.food_status_label || 'N/A';
                                opt.dataset.rent = resident.formatted_rent || 'N/A';
                                opt.textContent = `${resident.name} (${resident.resident_code})`;
                                residentSelect.appendChild(opt);
                            });
                        }
                    });
            }
        }

        function startCamera() {
            if (isCameraStarted) { stopCamera(); return; }
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
                .then(function(streamObj) {
                    stream = streamObj;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    video.play();
                    isCameraStarted = true;
                    startCameraBtn.textContent = '⏹️ Stop Camera';
                    startCameraBtn.style.background = '#dc3545';
                    captureBtn.style.display = 'inline-block';
                    clearBtn.style.display = 'inline-block';
                    previewContainer.style.display = 'none';
                    imageInput.value = '';
                    fileName.textContent = '';
                })
                .catch(function(err) {
                    alert('Camera access denied. Please upload an image instead.');
                });
            } else {
                alert('Camera not supported. Please upload an image.');
            }
            validateForm();
        }

        function stopCamera() {
            if (stream) { stream.getTracks().forEach(track => track.stop()); stream = null; }
            video.srcObject = null;
            video.style.display = 'none';
            isCameraStarted = false;
            startCameraBtn.textContent = '📷 Start Camera';
            startCameraBtn.style.background = '#28a745';
            captureBtn.style.display = 'none';
            validateForm();
        }

        function capturePhoto() {
            if (!isCameraStarted) { alert('Please start the camera first'); return; }
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataURL = canvas.toDataURL('image/jpeg', 0.9);
            capturedImage.src = dataURL;
            previewContainer.style.display = 'block';
            fetch(dataURL).then(res => res.blob()).then(blob => {
                const file = new File([blob], "captured_face.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                imageInput.files = dataTransfer.files;
                fileName.textContent = '📸 captured_face.jpg';
                stopCamera();
                validateForm();
            });
        }

        function clearImage() {
            previewContainer.style.display = 'none';
            imageInput.value = '';
            fileName.textContent = '';
            if (isCameraStarted) stopCamera();
            validateForm();
        }

        imageInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    capturedImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    fileName.textContent = '📁 ' + file.name;
                    if (isCameraStarted) stopCamera();
                };
                reader.readAsDataURL(file);
                validateForm();
            }
        });

        function validateForm() {
            submitBtn.disabled = !(residentSelect.value && imageInput.files && imageInput.files.length > 0);
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!residentSelect.value) { e.preventDefault(); alert('Please select a resident'); return; }
            if (!imageInput.files || imageInput.files.length === 0) { e.preventDefault(); alert('Please capture or upload an image'); return; }
            progressBar.style.display = 'block';
            let progress = 0;
            const interval = setInterval(() => {
                progress += 5;
                if (progress <= 90) progressFill.style.width = progress + '%';
            }, 100);
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Registering...';
            setTimeout(() => clearInterval(interval), 5000);
        });

        window.addEventListener('beforeunload', function() {
            if (stream) stream.getTracks().forEach(track => track.stop());
        });

        validateForm();
    </script>
</body>
</html>
