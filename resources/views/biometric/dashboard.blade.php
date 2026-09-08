<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🔐 Biometric Door Lock System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 { font-size: 28px; }
        .header p { opacity: 0.9; margin-top: 5px; }
        
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        .info-box strong { color: #533f03; }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid #1a73e8;
        }
        .card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .card .icon { font-size: 30px; display: block; margin-bottom: 10px; }
        .card h3 { font-size: 16px; margin-bottom: 5px; }
        .card p { font-size: 13px; color: #666; }
        .card .status { 
            font-size: 12px; 
            padding: 4px 12px; 
            background: #f0f0f0; 
            border-radius: 20px; 
            display: inline-block;
            margin-top: 10px;
        }
        .card.danger { border-left-color: #ea4335; }
        .card.success { border-left-color: #34a853; }
        .card.warning { border-left-color: #fbbc04; }
        .card.purple { border-left-color: #9c27b0; }
        
        .card.punch-card {
            border-left: 4px solid #ff6b00;
            background: linear-gradient(135deg, #fff5e6, #ffffff);
            text-align: center;
            padding: 30px 20px;
        }
        .card.punch-card .icon { font-size: 48px; }
        .card.punch-card h3 { font-size: 20px; color: #ff6b00; }
        .card.punch-card .resident-select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            margin: 10px 0;
        }
        
        .door-indicator {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin: 5px;
        }
        .door-indicator.open { background: #e6f4ea; color: #1e7e34; }
        .door-indicator.locked { background: #fce8e6; color: #c62828; }
        .door-indicator.info { background: #e8f0fe; color: #1a73e8; }
        
        .results {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 13px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .results .time { color: #6a9955; }
        .results .success { color: #4ec9b0; }
        .results .error { color: #f48771; }
        .results .info { color: #569cd6; }
        .results .warning { color: #dcdcaa; }
        .results .door-open { color: #4ec9b0; font-weight: bold; }
        .results .door-locked { color: #f48771; font-weight: bold; }
        
        .btn {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover { background: #1557b0; }
        .btn-danger { background: #ea4335; }
        .btn-danger:hover { background: #c62828; }
        .btn-success { background: #34a853; }
        .btn-success:hover { background: #2d9249; }
        .btn-warning { background: #fbbc04; color: #333; }
        .btn-warning:hover { background: #e5aa00; }
        .btn-large { padding: 15px 30px; font-size: 16px; }
        
        .toolbar { margin-bottom: 15px; }
        .loading { 
            display: inline-block; 
            width: 14px; 
            height: 14px; 
            border: 2px solid #f3f3f3; 
            border-top: 2px solid #1a73e8; 
            border-radius: 50%; 
            animation: spin 0.8s linear infinite; 
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat .num { font-size: 24px; font-weight: bold; }
        .stat .label { font-size: 12px; color: #666; }
        
        .payment-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 13px;
            text-align: left;
        }
        .payment-info .label { color: #666; }
        .payment-info .value { font-weight: bold; }
        
        @media (max-width: 768px) {
            .card.punch-card { padding: 20px; }
            .card.punch-card .icon { font-size: 36px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Biometric Door Lock System</h1>
            <p>Individual Payment Check - Each resident checked separately</p>
        </div>
        
        <div class="info-box">
            <strong>📋 Rules:</strong><br>
            • <strong>Before 10th:</strong> Door OPENS for everyone (No payment check) 🟢<br>
            • <strong>After 10th:</strong> Door OPENS only if PAID, LOCKED if NOT PAID 🔒<br>
            • Each resident's payment is checked <strong>individually</strong> when they punch
        </div>
        
        <div class="stats" id="stats">
            <div class="stat">
                <div class="num" id="total">0</div>
                <div class="label">Total Residents</div>
            </div>
            <div class="stat">
                <div class="num" id="synced">0</div>
                <div class="label">Synced</div>
            </div>
            <div class="stat">
                <div class="num" id="access-enabled">0</div>
                <div class="label">Access Enabled</div>
            </div>
            <div class="stat">
                <div class="num" id="today-date">-</div>
                <div class="label">Today's Date</div>
            </div>
        </div>
        
        <!-- PUNCH CARD -->
        <div class="card punch-card" id="punch-card">
            <div class="icon">🚪</div>
            <h3>Door Access</h3>
            <p>Select resident and punch - Individual payment check</p>
            
            <select class="resident-select" id="resident-select">
                <option value="">-- Select Resident --</option>
            </select>
            
            <div id="door-status-display" style="margin: 10px 0;">
                <span class="door-indicator locked">🔒 LOCKED</span>
            </div>
            
            <div id="payment-info-display" class="payment-info" style="display:none;"></div>
            
            <button class="btn btn-warning btn-large" onclick="punchDoor()" id="punch-btn">
                👊 PUNCH
            </button>
            <br>
            <button class="btn btn-success" onclick="checkPaymentStatus()" style="margin-top: 10px;">
                💰 Check Payment
            </button>
        </div>
        
        <div class="grid">
            <div class="card" onclick="syncAll()">
                <span class="icon">👥</span>
                <h3>Sync All Residents</h3>
                <p>Add all residents to device</p>
                <span class="status" id="sync-status">Click to sync</span>
            </div>
            
            <div class="card success" onclick="dailyCheck()">
                <span class="icon">💰</span>
                <h3>Daily Payment Check</h3>
                <p>Check payments & update access</p>
                <span class="status" id="payment-status">Click to check</span>
            </div>
            
            <div class="card warning" onclick="getAttendance()">
                <span class="icon">📊</span>
                <h3>Get Attendance</h3>
                <p>Pull today's logs</p>
                <span class="status" id="attendance-status">Click to fetch</span>
            </div>
            
            <div class="card danger" onclick="deviceStatus()">
                <span class="icon">📟</span>
                <h3>Device Status</h3>
                <p>Check device online</p>
                <span class="status" id="device-status">Click to check</span>
            </div>
        </div>
        
        <div class="toolbar">
            <button class="btn" onclick="clearLogs()">🗑️ Clear</button>
            <button class="btn" onclick="exportLogs()">📤 Export</button>
            <span style="margin-left: 10px; font-size: 12px; color: #666;" id="timestamp"></span>
        </div>
        
        <div class="results" id="results">
            <span class="time">[System]</span> <span class="info">Ready for testing...</span>
        </div>
    </div>

    <script>
        let isRunning = false;
        
        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const time = new Date().toLocaleTimeString();
            const emojis = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️', system: '⚙️' };
            const line = document.createElement('div');
            line.innerHTML = `<span class="time">[${time}]</span> <span class="${type}">${emojis[type] || '•'} ${message}</span>`;
            results.appendChild(line);
            results.scrollTop = results.scrollHeight;
        }
        
        function setStatus(id, text, type = 'info') {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = text;
                el.style.color = type === 'success' ? '#34a853' : type === 'error' ? '#ea4335' : '#666';
            }
        }
        
        async function fetchAPI(url, name, method = 'GET', data = null) {
            try {
                log(`🔄 ${name}...`, 'info');
                const options = { 
                    method: method, 
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };
                
                if (data) {
                    options.body = JSON.stringify(data);
                }
                
                const response = await fetch(url, options);
                const result = await response.json();
                log(`✅ ${name} completed`, 'success');
                return result;
            } catch (error) {
                log(`❌ ${name} failed: ${error.message}`, 'error');
                return { success: false, error: error.message };
            }
        }
        
        async function loadResidents() {
            try {
                const response = await fetch('/api/test/daily-check', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success && data.data) {
                    const select = document.getElementById('resident-select');
                    select.innerHTML = '<option value="">-- Select Resident --</option>';
                    data.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.resident_id;
                        const status = item.access_enabled ? '✅ Access' : '❌ Locked';
                        const paid = item.has_paid ? '💰 Paid' : '💸 Unpaid';
                        option.textContent = `${item.name} - ${status} - ${paid}`;
                        select.appendChild(option);
                    });
                    
                    let enabled = 0;
                    data.data.forEach(item => {
                        if (item.access_enabled) enabled++;
                    });
                    document.getElementById('total').textContent = data.total;
                    document.getElementById('access-enabled').textContent = enabled;
                    document.getElementById('synced').textContent = data.total;
                    
                    const today = new Date();
                    const day = today.getDate();
                    const month = today.toLocaleString('default', { month: 'long' });
                    document.getElementById('today-date').textContent = `${day} ${month}`;
                }
            } catch (e) {
                log('Failed to load residents: ' + e.message, 'error');
            }
        }
        
        async function punchDoor() {
            if (isRunning) return;
            
            const residentId = document.getElementById('resident-select').value;
            if (!residentId) {
                log('⚠️ Please select a resident first', 'warning');
                return;
            }
            
            isRunning = true;
            const btn = document.getElementById('punch-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Punching...';
            
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            log(`👊 Punching door for resident ID: ${residentId}`, 'info');
            
            const result = await fetchAPI('/api/test/punch', 'Punch', 'POST', { resident_id: parseInt(residentId) });
            
            if (result.error) {
                log(`❌ ${result.error}`, 'error');
                document.getElementById('door-status-display').innerHTML = `<span class="door-indicator locked">🔒 ERROR</span>`;
                document.getElementById('payment-info-display').style.display = 'none';
            } else {
                const doorStatus = result.door || 'LOCKED';
                const emoji = doorStatus === 'OPEN' ? '🚪' : '🔒';
                const statusClass = doorStatus === 'OPEN' ? 'open' : 'locked';
                
                document.getElementById('door-status-display').innerHTML = 
                    `<span class="door-indicator ${statusClass}">${emoji} ${doorStatus}</span>`;
                
                if (result.payment_details) {
                    const p = result.payment_details;
                    document.getElementById('payment-info-display').style.display = 'block';
                    document.getElementById('payment-info-display').innerHTML = `
                        <div><span class="label">💰 Amount:</span> <span class="value">₹${p.amount}</span></div>
                        <div><span class="label">✅ Paid:</span> <span class="value">₹${p.paid}</span></div>
                        <div><span class="label">📊 Balance:</span> <span class="value">₹${p.balance}</span></div>
                        <div><span class="label">📌 Status:</span> <span class="value">${p.status}</span></div>
                    `;
                } else {
                    document.getElementById('payment-info-display').style.display = 'block';
                    document.getElementById('payment-info-display').innerHTML = `
                        <div><span class="label">💰 Payment:</span> <span class="value">No payment record for this month</span></div>
                    `;
                }
                
                log(`📅 Day: ${result.day_of_month}`, 'info');
                log(`📌 Rule Applied: ${result.rule_applied}`, 'info');
                log(`🚪 Door: ${doorStatus}`, doorStatus === 'OPEN' ? 'door-open' : 'door-locked');
                log(`💰 Payment: ${result.has_paid ? 'Paid ✅' : 'Not Paid ❌'}`, result.has_paid ? 'success' : 'error');
                log(`📝 ${result.message}`, doorStatus === 'OPEN' ? 'success' : 'warning');
                
                setTimeout(loadResidents, 1000);
            }
            
            btn.disabled = false;
            btn.innerHTML = '👊 PUNCH';
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            isRunning = false;
        }
        
        async function checkPaymentStatus() {
            const residentId = document.getElementById('resident-select').value;
            if (!residentId) {
                log('⚠️ Please select a resident first', 'warning');
                return;
            }
            
            log(`💰 Checking payment for resident ID: ${residentId}`, 'info');
            
            const result = await fetchAPI(`/api/test/check-payment/${residentId}`, 'Check Payment');
            
            if (result.success) {
                const status = result.has_paid ? '✅ PAID' : '❌ NOT PAID';
                log(`💰 ${result.resident}: ${status}`, result.has_paid ? 'success' : 'error');
                log(`🚪 Door Status: ${result.door_status}`, 'info');
                log(`📅 Day: ${result.day_of_month}, Month: ${result.month}`, 'info');
                
                if (result.payment_details) {
                    const p = result.payment_details;
                    log(`   Amount: ₹${p.amount}, Paid: ₹${p.paid}, Balance: ₹${p.balance}`, 'info');
                }
            } else {
                log(`❌ ${result.error}`, 'error');
            }
        }
        
        async function syncAll() {
            if (isRunning) return;
            isRunning = true;
            setStatus('sync-status', 'Syncing...', 'info');
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            
            const result = await fetchAPI('/api/test/sync-all', 'Sync All');
            
            if (result.error) {
                setStatus('sync-status', 'Failed', 'error');
                log(`❌ ${result.error}`, 'error');
            } else {
                const success = result.success_count || 0;
                const total = result.total || 0;
                setStatus('sync-status', `${success}/${total} synced`, success === total ? 'success' : 'warning');
                log(`✅ ${success} of ${total} residents synced`, 'success');
                if (result.failure_count > 0) {
                    log(`⚠️ ${result.failure_count} failed`, 'warning');
                }
                
                document.getElementById('total').textContent = total;
                document.getElementById('synced').textContent = success;
                
                if (result.data) {
                    result.data.forEach(item => {
                        const status = item.status === 'success' ? '✅' : '❌';
                        log(`   ${status} ${item.name} (${item.employee_code})`, item.status === 'success' ? 'success' : 'error');
                    });
                }
                
                setTimeout(loadResidents, 1000);
            }
            
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            isRunning = false;
        }
        
        async function dailyCheck() {
            if (isRunning) return;
            isRunning = true;
            setStatus('payment-status', 'Checking...', 'info');
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            
            const result = await fetchAPI('/api/test/daily-check', 'Payment Check');
            
            if (result.error) {
                setStatus('payment-status', 'Failed', 'error');
                log(`❌ ${result.error}`, 'error');
            } else {
                let enabled = 0;
                let disabled = 0;
                
                log(`📅 Day: ${result.day_of_month} - ${result.rule}`, 'info');
                
                if (result.data) {
                    result.data.forEach(item => {
                        const status = item.access_enabled ? '✅ Enabled' : '❌ Disabled';
                        const door = item.door_status || (item.access_enabled ? 'OPEN' : 'LOCKED');
                        const paid = item.has_paid ? '💰 Paid' : '💸 Unpaid';
                        const action = item.action ? ` → ${item.action}` : '';
                        log(`   ${item.name}: ${status} | ${door} | ${paid} ${action}`, item.access_enabled ? 'success' : 'error');
                        
                        if (item.access_enabled) enabled++;
                        else disabled++;
                    });
                }
                
                setStatus('payment-status', `${enabled} enabled, ${disabled} disabled`, 'success');
                log(`✅ ${enabled} residents have access, ${disabled} disabled`, 'success');
                
                document.getElementById('access-enabled').textContent = enabled;
                setTimeout(loadResidents, 1000);
            }
            
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            isRunning = false;
        }
        
        async function getAttendance() {
            if (isRunning) return;
            isRunning = true;
            setStatus('attendance-status', 'Fetching...', 'info');
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            
            const result = await fetchAPI('/api/test/attendance', 'Attendance');
            
            if (result.error) {
                setStatus('attendance-status', 'Failed', 'error');
                log(`❌ ${result.error}`, 'error');
            } else {
                let count = 0;
                if (result.data) {
                    if (typeof result.data === 'string') {
                        count = result.data.split(';').filter(s => s.trim()).length;
                    } else if (Array.isArray(result.data)) {
                        count = result.data.length;
                    }
                }
                setStatus('attendance-status', `${count} logs`, count > 0 ? 'success' : 'warning');
                log(`✅ Found ${count} attendance logs`, 'success');
                
                if (count > 0 && result.data) {
                    let logs = typeof result.data === 'string' ? result.data.split(';') : result.data;
                    logs.slice(0, 5).forEach(logItem => {
                        log(`   ${logItem}`, 'info');
                    });
                }
            }
            
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            isRunning = false;
        }
        
        async function deviceStatus() {
            if (isRunning) return;
            isRunning = true;
            setStatus('device-status', 'Checking...', 'info');
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            
            const result = await fetchAPI('/api/test/device', 'Device');
            
            if (result.error) {
                setStatus('device-status', 'Failed', 'error');
                log(`❌ ${result.error}`, 'error');
            } else {
                const online = result.status?.success ? '🟢 Online' : '🔴 Offline';
                const reboot = result.reboot?.success ? '✅ Reboot sent' : '❌ Reboot failed';
                setStatus('device-status', result.status?.success ? 'Online' : 'Offline', result.status?.success ? 'success' : 'error');
                log(`📟 ${online}`, result.status?.success ? 'success' : 'error');
                log(`🔄 ${reboot}`, result.reboot?.success ? 'success' : 'error');
            }
            
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            isRunning = false;
        }
        
        function clearLogs() {
            document.getElementById('results').innerHTML = '';
            log('Logs cleared', 'system');
        }
        
        function exportLogs() {
            const text = document.getElementById('results').innerText;
            const blob = new Blob([text], { type: 'text/plain' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `biometric-log-${new Date().toISOString().slice(0,10)}.txt`;
            a.click();
            log('Logs exported', 'success');
        }
        
        window.onload = function() {
            log('🔐 Biometric Door Lock System', 'system');
            log('💡 Select a resident and click PUNCH', 'info');
            log('📌 Each resident is checked individually', 'info');
            log('📌 Before 10th: Door opens for everyone', 'info');
            log('📌 After 10th: Door opens only if PAID', 'info');
            log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'system');
            document.getElementById('timestamp').textContent = `Loaded: ${new Date().toLocaleTimeString()}`;
            loadResidents();
        };
    </script>
</body>
</html>