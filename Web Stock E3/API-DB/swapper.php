<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Schema Viewer</title>
    <!-- ลิงก์ไปยัง Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* From Uiverse.io by satyamchaudharydev */
        .button {
            position: relative;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            padding-block: 0.5rem;
            padding-inline: 1.25rem;
            background-color: rgb(0 107 179);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffff;
            gap: 10px;
            font-weight: bold;
            border: 3px solid #ffffff4d;
            outline: none;
            overflow: hidden;
            font-size: 15px;
            cursor: pointer;
        }

        .icon {
            width: 24px;
            height: 24px;
            transition: all 0.3s ease-in-out;
        }

        .button:hover {
            transform: scale(1.05);
            border-color: #fff9;
        }

        .button:hover .icon {
            transform: translate(4px);
        }

        .button:hover::before {
            animation: shine 1.5s ease-out infinite;
        }

        .button::before {
            content: "";
            position: absolute;
            width: 100px;
            height: 100%;
            background-image: linear-gradient(
                120deg,
                rgba(255, 255, 255, 0) 30%,
                rgba(255, 255, 255, 0.8),
                rgba(255, 255, 255, 0) 70%
            );
            top: 0;
            left: -100px;
            opacity: 0.6;
        }

        @keyframes shine {
            0% {
                left: -100px;
            }

            60% {
                left: 100%;
            }

            to {
                left: 100%;
            }
        }
    </style>
</head>
<body class="bg-purple-500 font-sans text-gray-800">

    <div class="max-w-6xl mx-auto p-6">
    <h2 class="text-4xl font-extrabold text-center text-white mb-8 tracking-wide drop-shadow-md">
    📊 เลือกตารางเพื่อดู JSON 
    </h2>


       <!-- Dropdown สำหรับเลือกตาราง -->
<div class="flex justify-center mb-6">
    <div class="relative w-64">
        <select id="tableSelector"
            class="w-full appearance-none bg-white border border-gray-300 text-gray-700 text-lg px-4 py-3 pr-10 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
            <option value="">-- เลือกตาราง --</option>
            <option value="Accounts">Accounts</option>
            <option value="Games">Games</option>
            <option value="Users">Users</option>
            <option value="TopUpHistory">TopUpHistory</option>
            <option value="purchase_history">purchase_history</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</div>


        <!-- ปุ่มสำหรับดาวน์โหลด JSON -->
        <div class="text-center">
            <button id="downloadJson" class="button" style="display:none;">
                <span>Download JSON</span>
            </button>
        </div>

     <!-- แสดงข้อมูล JSON ในการ์ด -->
<div class="json-container mt-6">
    <div class="bg-white w-full max-w-7xl rounded-lg mt-8 mx-auto shadow-lg p-6">
        <div class="flex p-4 gap-3 justify-start">
            <div class="circle">
                <span class="bg-blue-500 inline-block w-3 h-3 rounded-full"></span>
            </div>
            <div class="circle">
                <span class="bg-purple-500 inline-block w-3 h-3 rounded-full"></span>
            </div>
            <div class="circle">
                <span class="bg-pink-500 inline-block w-3 h-3 rounded-full"></span>
            </div>
        </div>
        <div class="card__content">
        <h3 class="text-2xl font-bold text-blue-700 mb-4 text-center uppercase tracking-wide">📜 JSON Output</h3>
            <pre id="jsonDisplay" class="bg-gray-200 p-4 rounded-lg overflow-x-auto shadow-md">กรุณาเลือกตาราง...</pre>
        </div>
    </div>
</div>

    </div>

    <script>
        let jsonData = {};  // เก็บข้อมูล JSON ที่จะใช้ดาวน์โหลด

        document.getElementById('tableSelector').addEventListener('change', function() {
            let tableName = this.value;
            if (!tableName) return;

            fetch(`api.php?table=${tableName}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        jsonData = data;  // เก็บ JSON ไว้สำหรับดาวน์โหลด
                        displayJson(data);
                        document.getElementById('downloadJson').style.display = "inline-block"; // แสดงปุ่ม Download
                    } else {
                        document.getElementById('jsonDisplay').textContent = "Error loading JSON";
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        function displayJson(json) {
            document.getElementById('jsonDisplay').textContent = JSON.stringify(json, null, 2);
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
    </script>

</body>
</html>
