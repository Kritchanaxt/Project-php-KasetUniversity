<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Schema Viewer</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            color: #fff;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Background effect with animation */
        .background-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 10%, transparent 10.01%);
            background-size: 20px 20px;
            animation: moveBackground 10s linear infinite;
            pointer-events: none;
        }

        @keyframes moveBackground {
            from {
                transform: translateY(0) translateX(0);
            }
            to {
                transform: translateY(-100%) translateX(-100%);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Navigation Bar (เมนูด้านบน) */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(16, 7, 32, 0.8);
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: background 0.3s ease-in-out;
        }

        /* Glow Blur Effect */
        .navbar::after {
            content: '';
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle farthest-corner at 10% 20%, rgba(255, 94, 247, 0.4) 17.8%, rgba(2, 245, 255, 0.4) 100.2%);
            filter: blur(20px);
            z-index: -1;
            position: absolute;
            left: 0;
            top: 0;
        }

        /* Logo */
        .nav-logo {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.2s ease-in-out;
        }

        .nav-logo:hover {
            transform: scale(1.1);
            text-shadow: 0px 0px 10px rgba(255, 94, 247, 0.6);
        }

        /* Menu items */
        .nav-links {
            list-style: none;
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
        }

        .nav-links a:hover {
            background: linear-gradient(90deg, #ff5ef7, #02f5ff);
            box-shadow: 0px 4px 15px rgba(255, 94, 247, 0.5);
            color: #fff;
        }

        .nav-links a:active {
            transform: scale(0.95);
        }
        /* ปุ่ม Add Product (ดีไซน์ใหม่) */
.add-product-btn {
  width: 165px;
  height: 62px;
  cursor: pointer;
  color: #fff;
  font-size: 17px;
  border-radius: 1rem;
  border: none;
  position: relative;
  background: #100720;
  transition: 0.1s;
  font-weight: bold;
}


        /* Main content styling */
        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            animation: fadeIn 0.5s ease-out;
            margin-top: 90px;
        }

        h1, h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
            animation: slideIn 0.5s ease-out;
            margin-bottom: 2rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Login button */
        .login-btn {
            background: linear-gradient(90deg, #a78bfa, #7c3aed);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        .login-btn:hover {
            background: linear-gradient(90deg, #8b5cf6, #6d28d9);
            box-shadow: 0px 4px 15px rgba(167, 139, 250, 0.5);
            transform: translateY(-3px);
        }

        /* JSON Viewer Specific Styles */
        .table-selector {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .table-selector select {
            width: 100%;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(167, 139, 250, 0.5);
            border-radius: 15px;
            appearance: none;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .table-selector select:focus {
            outline: none;
            border-color: #a78bfa;
            box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.5);
        }

        .table-selector select option {
            background-color: #24243e;
            color: white;
        }

        .select-arrow {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #a78bfa;
        }

        .download-btn {
            background: linear-gradient(90deg, #a78bfa, #7c3aed);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(167, 139, 250, 0.3);
        }

        .download-btn:active {
            transform: translateY(-1px);
        }

        .json-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 0;
            backdrop-filter: blur(10px);
            overflow: hidden;
            margin-top: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .json-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .json-card-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .circle-red {
            background-color: #ff5f56;
        }

        .circle-yellow {
            background-color: #ffbd2e;
        }

        .circle-green {
            background-color: #27c93f;
        }

        .json-card-title {
            font-size: 1.5rem;
            text-align: center;
            width: 100%;
            margin-top: 1rem;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .json-content {
            padding: 2rem;
            max-height: 600px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0 0 20px 20px;
        }

        #jsonDisplay {
            font-family: 'Courier New', monospace;
            color: #fff;
            white-space: pre-wrap;
        }

        /* Styling keys and values in JSON */
        .json-key {
            color: #a78bfa;
        }

        .json-string {
            color: #4ade80;
        }

        .json-number {
            color: #fb923c;
        }

        .json-boolean {
            color: #60a5fa;
        }

        .json-null {
            color: #f87171;
        }

        /* Custom scrollbar */
        .json-content::-webkit-scrollbar {
            width: 10px;
        }

        .json-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0 0 20px 0;
        }

        .json-content::-webkit-scrollbar-thumb {
            background: rgba(167, 139, 250, 0.5);
            border-radius: 5px;
        }

        .json-content::-webkit-scrollbar-thumb:hover {
            background: rgba(167, 139, 250, 0.8);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.8rem 1rem;
            }

            .nav-links a {
                padding: 0.6rem 1rem;
                font-size: 1rem;
            }

            .dashboard-header {
                padding: 1.5rem;
            }

            h1, h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-effect"></div>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">📦 Inventory</div>
        <ul class="nav-links">
        <li><a href="inventory.php">Store</a></li>
        <li><a href="edit_product.php">EditProduct</a></li>
        <li><a href="Stockgame.php">ShowProduct</a></li>
        <li><a href="swapper.php">API-DB</a></li>
        <li><a href="add_product.php" class="add-product-btn">➕ Add Product</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>JSON Schema Viewer</h1>
            <p class="text-lg text-gray-200 mb-4">เลือกตารางเพื่อดู JSON Schema</p>

            <!-- Table Selector Dropdown -->
            <div class="table-selector">
                <select id="tableSelector">
                    <option value="">-- เลือกตาราง --</option>
                    <option value="Accounts">Accounts</option>
                    <option value="Games">Games</option>
                    <option value="Users">Users</option>
                    <option value="TopUpHistory">TopUpHistory</option>
                    <option value="purchase_history">purchase_history</option>
                </select>
                <div class="select-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Download Button -->
            <div class="text-center">
                <button id="downloadJson" class="download-btn" style="display:none;">
                    <span>Download JSON</span>
                </button>
            </div>
        </div>

        <!-- JSON Display -->
        <div class="json-card">
            <div class="json-card-header">
                <div class="circle circle-red"></div>
                <div class="circle circle-yellow"></div>
                <div class="circle circle-green"></div>
                <h2 class="json-card-title">📜 JSON Output</h2>
            </div>
            <div class="json-content">
                <pre id="jsonDisplay">กรุณาเลือกตาราง...</pre>
            </div>
        </div>
    </div>

    <script>
        let jsonData = {};  // Store JSON data for download

        document.getElementById('tableSelector').addEventListener('change', function() {
            let tableName = this.value;
            if (!tableName) return;

            fetch(`api.php?table=${tableName}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        jsonData = data;  // Store JSON for download
                        displayFormattedJson(data);
                        document.getElementById('downloadJson').style.display = "inline-block"; // Show download button
                    } else {
                        document.getElementById('jsonDisplay').textContent = "Error loading JSON";
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        function displayFormattedJson(json) {
            const jsonString = JSON.stringify(json, null, 2);
            // Format JSON with color highlights
            const formattedJson = jsonString
                .replace(/"([^"]+)":/g, '<span class="json-key">"$1":</span>')
                .replace(/"([^"]*)"/g, '<span class="json-string">"$1"</span>')
                .replace(/\b(true|false)\b/g, '<span class="json-boolean">$1</span>')
                .replace(/\b(null)\b/g, '<span class="json-null">$1</span>')
                .replace(/\b(\d+)\b/g, '<span class="json-number">$1</span>');
            
            document.getElementById('jsonDisplay').innerHTML = formattedJson;
        }

        document.getElementById('downloadJson').addEventListener('click', function() {
            const blob = new Blob([JSON.stringify(jsonData, null, 2)], { type: "application/json" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `${document.getElementById('tableSelector').value}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        // Simple display function for when we don't want HTML formatting
        function displayJson(json) {
            document.getElementById('jsonDisplay').textContent = JSON.stringify(json, null, 2);
        }
    </script>
</body>
</html>