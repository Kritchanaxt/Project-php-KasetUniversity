<?php
// เพิ่ม PHP session_start() ที่ด้านบนของหน้า
session_start();

// ฟังก์ชันตรวจสอบว่าเป็น admin หรือไม่
function isAdmin($username) {
    // ตรวจสอบว่าเป็น admin12345678 หรือไม่
    return ($username === 'admin12345678');
}

// เตรียมตัวแปร JavaScript สำหรับส่งค่าไปยัง script
$isAdminJS = 'false';
if (isset($_SESSION['username']) && isAdmin($_SESSION['username'])) {
    $isAdminJS = 'true';
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="src/output.css" rel="stylesheet" />
	<title>PLAYER HAVEN SHOP</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- CSS -->
	<style>
		/* อนิเมชันพื้นหลังสำหรับโหมดสว่าง (เพชรฟ้าขาวระยิบ) */
		body {
			position: relative;
			background: linear-gradient(45deg, #e6f3ff, #b3d4ff, #e6f3ff);
			transition: background 0.5s ease, background-color 0.5s ease;
		}

		body::before {
			content: '';
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: url('data:image/svg+xml,%3Csvg width="100" height="100" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="50" cy="50" r="2" fill="rgba(100, 200, 255, 0.8)" /%3E%3C/svg%3E') repeat;
			animation: sparkleLight 3s infinite;
			pointer-events: none;
			z-index: -1;
			/* อยู่ใต้เนื้อหา */
		}

		/* อนิเมชันพื้นหลังสำหรับโหมดมืด (เพชรฟ้าดำครึ้มระยิบ) */
		body.dark-mode {
			background: linear-gradient(45deg, #0a1a2f, #1a3b5d, #0a1a2f);
		}

		body.dark-mode::before {
			content: '';
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: url('data:image/svg+xml,%3Csvg width="100" height="100" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="50" cy="50" r="1" fill="rgba(0, 100, 200, 0.7)" /%3E%3C/svg%3E') repeat;
			animation: sparkleDark 3s infinite;
			pointer-events: none;
			z-index: -1;
			/* อยู่ใต้เนื้อหา */
		}

		@keyframes sparkleLight {
			0% {
				opacity: 0.3;
			}

			50% {
				opacity: 0.8;
			}

			100% {
				opacity: 0.3;
			}
		}

		@keyframes sparkleDark {
			0% {
				opacity: 0.2;
			}

			50% {
				opacity: 0.7;
			}

			100% {
				opacity: 0.2;
			}
		}

		/* Theme mode */

		.dark-mode {
			background-color: #0a1a2f;
			/* เปลี่ยนเป็นสีพื้นหลังหลักของโหมดมืด */
			color: white;
		}

		.dark-mode header {
			background-color: #1a3b5d;
		}

		.dark-mode .bg-indigo-600 {
			background-color: #1a3b5d !important;
		}

		/* ป้องกันปุ่มเปลี่ยนสีในโหมดสีเข้ม */
		.dark-mode .bg-blue-600 {
			background-color: #2563eb !important;
			color: white !important;
		}

		.dark-mode .bg-red-600 {
			background-color: #dc2626 !important;
			color: white !important;
		}

		.dark-mode .bg-green-500 {
			background-color: #22c55e !important;
			color: white !important;
		}

		.dark-mode .bg-red-500 {
			background-color: #ef4444 !important;
			color: white !important;
		}

		/* Admin Badge */
		.admin-badge {
			background-color: #fbbf24;
			color: #7c2d12;
			padding: 2px 8px;
			border-radius: 6px;
			font-size: 0.75rem;
			font-weight: bold;
			margin-left: 8px;
		}

		/* Switch Toggle Button */
		.theme-toggle {
			position: relative;
			display: inline-block;
			width: 60px;
			height: 34px;
		}

		.theme-toggle input {
			opacity: 0;
			width: 0;
			height: 0;
		}

		.slider {
			position: absolute;
			cursor: pointer;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #ccc;
			transition: background-color 0.4s ease;
			border-radius: 34px;
			box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.slider:before {
			position: absolute;
			content: "";
			height: 30px;
			width: 30px;
			left: 2px;
			bottom: 2px;
			background-color: white;
			transition: transform 0.4s ease;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
		}

		.slider:after {
			content: " ";
			font-size: 18px;
			color: #f1c40f;
			transition: opacity 0.3s ease, content 0.3s ease;

		}

		input:checked+.slider {
			background-color: #2c3e50;
		}

		input:checked+.slider:before {
			transform: translateX(26px);
		}

		input:checked+.slider:after {
			content: "🌙";
			color: #fff;
		}

		.bg-nav {
			background-color: #1a1f36;
		}
	</style>

</head>

<body class="bg-gray-100">
	<!-- Header -->
	<header class="bg-nav text-white">
		<!-- Navbar -->
		<div class="container mx-auto flex items-center justify-between py-0 px-4">
			<div class="text-2xl font-bold inline">
				<img id="logo" src="image/logo_player/light_logo.png" alt="light_logo"
					class="w-auto h-24 inline mr-3 transform transition-transform duration-300 hover:scale-105 filter brightness-90" />

				PLAYERHAVEN
			</div>
			<nav class="flex space-x-4">
				<a href="HomePage.php" class="hover:text-indigo-300">หน้าแรก</a>
				<a href="#HomePage.php" onclick="scrollToBottom()" class="hover:text-indigo-300">ซื้อไอดีเกม</a>
				<a href="RandomWheel.php" class="hover:text-indigo-300">สุ่มไอดีเกม</a>
				<a href="TopUpCredit.html" class="hover:text-indigo-300">เติม Points</a>
				<a href="ProfileEdit.html" class="hover:text-indigo-300">ดูโปรไฟล์</a>
				<a href="contact.html" class="hover:text-indigo-300">ติดต่อเรา</a>
				<!-- ปุ่ม Inventory สำหรับ Admin (ซ่อนเริ่มต้น) -->
				<a href="InventoryWeb/src/inventory.php" id="adminInventoryBtn" class="hidden hover:text-indigo-300 relative">
					<span>จัดการสินค้า</span>
					<span class="admin-badge">Admin</span>
				</a>
			</nav>
			<div class="space-x-4">
				<!-- ปุ่มเข้าสู่ระบบ -->
				<button id="loginBtn" class="bg-indigo-700 px-4 py-2 rounded hover:bg-indigo-800"
					onclick="location.href='Login.html'">
					เข้าสู่ระบบ
				</button>

				<!-- ปุ่มสมัครสมาชิก -->
				<button id="registerBtn" class="border border-white px-4 py-2 rounded hover:bg-indigo-700"
					onclick="location.href='Register.html'">
					สมัครสมาชิก
				</button>

				<!-- ปุ่มออกจากระบบ (ซ่อนเริ่มต้น) -->
				<button id="logoutBtn" class="hidden bg-red-500 px-4 py-2 rounded hover:bg-red-600" onclick="logout()">
					ออกจากระบบ
				</button>

				<!-- ปุ่มเปลี่ยนธีมแบบสไลด์ท็อกเกิล -->
				<label class="theme-toggle">
					<input type="checkbox" id="themeToggle" onchange="toggleTheme()">
					<span class="slider"></span>
				</label>
			</div>
        </div>
	</header>

	<!-- Section -->
	<section class=" text-white py-0 text-center flex justify-center">
		<!-- Profile -->
		<div class=" text-white py-3 flex justify-center">
			<div class="flex flex-row items-center w-full max-w-screen-xl px-8 space-x-6">
				<div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200 flex flex-row w-full">
					<!-- ใน div id="profileSection" -->
					<div class="flex-1 flex flex-col items-center text-center border-r pr-6 hidden" id="profileSection">
						<div class="flex flex-col items-center pb-4 border-b w-full">
							<div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
									stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-600">
									<path stroke-linecap="round" stroke-linejoin="round"
										d="M15.75 14.25a4.5 4.5 0 01-7.5 0M12 3.75a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5z" />
								</svg>
							</div>
							<!-- อัปเดตชื่อผู้ใช้ที่ล็อกอิน -->
							<p id="usernameDisplay" class="font-semibold text-gray-900 text-lg mt-2">Guest</p>
							<!-- แสดง point จากฐานข้อมูล -->
							<p id="pointDisplay" class="text-sm text-gray-600">คงเหลือ <span
									class="font-bold text-gray-900">0.00</span> เครดิต</p>
							<a href="ProfileEdit.html" class="text-sm text-blue-500 hover:underline">ดูโปรไฟล์ของคุณ</a>
						</div>
						<div class="py-4 w-full">
							<a href="TopUpCredit.html"
								class="mt-2 w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 text-center block">
								เติมเครดิต
							</a>

						</div>
					</div>

					<!-- Actions Section -->
					<div class="flex-1 flex flex-wrap justify-center items-center gap-4 pl-6 hidden" id="actionSection">
						<button id="topupHistory"
							class="flex flex-col items-center bg-gray-100 hover:bg-gray-200 p-4 rounded-xl w-40 shadow-sm text-gray-900 font-semibold hidden"
							onclick="window.location.href='TopUpHistory.html'"">
        <span class=" text-2xl">📜</span>
							<span class="text-sm mt-1">ประวัติเติมเงิน</span>
						</button>
						<button id="purchaseHistory"
							class="flex flex-col items-center bg-gray-100 hover:bg-gray-200 p-4 rounded-xl w-40 shadow-sm text-gray-900 font-semibold hidden"
							onclick="window.location.href='PurchaseHistory.html'">
							<span class="text-2xl">📦</span>
							<span class="text-sm mt-1">ประวัติการสั่งซื้อ</span>
						</button>
						<button id="giftCardBox"
							class="flex flex-col items-center bg-gray-100 hover:bg-gray-200 p-4 rounded-xl w-40 shadow-sm text-gray-900 font-semibold hidden"
							onclick="window.location.href='TopUpCredit.html'">
							<span class="text-2xl">💳</span>
							<span class="text-sm mt-1">เติม Points</span>
						</button>
						<button id="mailbox"
							class="flex flex-col items-center bg-gray-100 hover:bg-gray-200 p-4 rounded-xl w-40 shadow-sm text-gray-900 font-semibold hidden"
							onclick="window.location.href='Mailbox.html'">
							<span class="text-2xl">📩</span>
							<span class="text-sm mt-1">กล่องจดหมาย</span>
						</button>
						<!-- ปุ่มจัดการสินค้าสำหรับ Admin (ซ่อนเริ่มต้น) -->
						<button id="adminInventoryBox"
							class="flex flex-col items-center bg-yellow-100 hover:bg-yellow-200 p-4 rounded-xl w-40 shadow-sm text-gray-900 font-semibold hidden"
							onclick="window.location.href='InventoryWeb/src/inventory.php'">
							<span class="text-2xl">🔧</span>
							<span class="text-sm mt-1">จัดการสินค้า</span>
							<span class="text-xs bg-yellow-500 text-white px-2 py-0.5 rounded mt-1">Admin</span>
						</button>
					</div>



				</div>
			</div>
		</div>
	</section>

	<!-- Game Online Topup -->
	<section class="container mx-auto py-8">
		<h2
			class="text-3xl font-bold text-center mb-1 bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-blue-600 decoration-4 underline underline-offset-8 decoration-blue-400">
			ซื้อไอดีเกมออนไลน์
		</h2>
		<!-- container และจัดให้อยู่ตรงกลาง -->
		<div class="container mx-auto px-4 py-8">
			<!-- Grid Layout - ปรับขนาดตามหน้าจอ -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
				<!-- Valorant -->
<a href="vlr_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/valorant_pic.jpg" alt="Valorant"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">Valorant</h3>
        </div>
    </div>
</a>

				<!-- TFT -->
<a href="tft_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/TFT_pic.jpg" alt="TFT"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">TFT</h3>
        </div>
    </div>
</a>

				<!-- LoL -->
<a href="lol_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/lol_pic.jpg" alt="LoL"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">LoL</h3>
        </div>
    </div>
</a>

				<!-- FF -->
<a href="ff_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/FF.png" alt="Free Fire"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">Free Fire</h3>
        </div>
    </div>
</a>


				<!-- RoV -->
<a href="rov_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/rov_pic.jpg" alt="RoV"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">RoV</h3>
        </div>
    </div>
</a>


				<!-- Call of Duty: Mobile -->
<a href="codm_shop.php" class="relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
    <div class="aspect-video overflow-hidden">
        <img src="image/game_pic/CODM.jpg" alt="Call of Duty: Mobile"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
    </div>
    <div
        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold">Call of Duty: Mobile</h3>
        </div>
    </div>
</a>

				</div>
			</div>
		</div>
	</section>
	<!-- Script -->
	<script>
		// ส่งค่า isAdmin จาก PHP ไปยัง JavaScript
		const isAdmin = <?php echo $isAdminJS; ?>;

		function scrollToBottom() {
			window.scrollTo({
				top: document.body.scrollHeight,
				behavior: 'smooth' 
			});
		}
		
		window.onload = function () {
			const username = localStorage.getItem("username");
			const profileSection = document.getElementById("profileSection");
			const usernameDisplay = document.getElementById("usernameDisplay");
			const pointDisplay = document.getElementById("pointDisplay");
			const loginBtn = document.getElementById("loginBtn");
			const registerBtn = document.getElementById("registerBtn");
			const logoutBtn = document.getElementById("logoutBtn");

			// กล่อง Action Buttons
			const actionSection = document.getElementById("actionSection");
			const purchaseHistory = document.getElementById("purchaseHistory");
			const topupHistory = document.getElementById("topupHistory");
			const giftCardBox = document.getElementById("giftCardBox");
			const mailbox = document.getElementById("mailbox");
			
			// Admin UI Elements
			const adminInventoryBtn = document.getElementById("adminInventoryBtn");
			const adminInventoryBox = document.getElementById("adminInventoryBox");

			if (username) {
				usernameDisplay.textContent = username; // อัปเดตชื่อบัญชี
				profileSection.classList.remove("hidden"); // แสดงโปรไฟล์
				actionSection.classList.remove("hidden"); // แสดงปุ่มต่างๆ
				logoutBtn.classList.remove("hidden"); // แสดงปุ่มออกจากระบบ
				loginBtn.classList.add("hidden"); // ซ่อนปุ่มเข้าสู่ระบบ
				registerBtn.classList.add("hidden"); // ซ่อนปุ่มสมัครสมาชิก

				// แสดงปุ่มประวัติการทำรายการ
				purchaseHistory.classList.remove("hidden");
				topupHistory.classList.remove("hidden");
				giftCardBox.classList.remove("hidden");
				mailbox.classList.remove("hidden");
				
				// ตรวจสอบว่าเป็น admin12345678 หรือไม่
				if (username === 'admin12345678' || isAdmin) {
					// แสดงปุ่ม Admin ในเมนูหลัก
					adminInventoryBtn.classList.remove("hidden");
					// แสดงปุ่ม Admin ในส่วน Action
					adminInventoryBox.classList.remove("hidden");
				}

				// ดึงข้อมูลจากเซิร์ฟเวอร์เพื่ออัปเดต point
				fetch("get_profile.php")
					.then(response => response.json())
					.then(data => {
						console.log("Profile Data:", data); // Debug ดูค่าที่ได้
						if (data.logged_in) {
							const point = data.user.point || 0; // ดึงค่า point จากฐานข้อมูล (ถ้ามี)
							pointDisplay.textContent = `คงเหลือ ${point}.00 เครดิต`; // อัปเดตค่า point
						} else {
							console.warn("User not logged in from server, but localStorage has username.");
						}
					})
					.catch(error => {
						console.error("Error loading profile data:", error);
						pointDisplay.textContent = `คงเหลือ 0.00 เครดิต`;
					});
			} else {
				usernameDisplay.textContent = "Guest"; // แสดง "Guest" ถ้าไม่ล็อกอิน
				pointDisplay.textContent = "คงเหลือ 0.00 เครดิต"; // แสดงค่าเริ่มต้นถ้าไม่ล็อกอิน
				profileSection.classList.add("hidden"); // ซ่อนโปรไฟล์
				actionSection.classList.add("hidden"); // ซ่อนกล่อง Action
				logoutBtn.classList.add("hidden"); // ซ่อนปุ่มออกจากระบบ
				loginBtn.classList.remove("hidden"); // แสดงปุ่มเข้าสู่ระบบ
				registerBtn.classList.remove("hidden"); // แสดงปุ่มสมัครสมาชิก
				
				// ซ่อนปุ่ม Admin
				adminInventoryBtn.classList.add("hidden");
				adminInventoryBox.classList.add("hidden");

				// ซ่อนปุ่มประวัติการทำรายการ
				purchaseHistory.classList.add("hidden");
				topupHistory.classList.add("hidden");
				giftCardBox.classList.add("hidden");
				mailbox.classList.add("hidden");
			}
		};

		// ฟังก์ชัน Logout ที่ปรับปรุงใหม่
function logout() {
    // ลบข้อมูลทั้งหมดที่เกี่ยวข้องกับการล็อกอินออกจาก localStorage
    localStorage.removeItem("username");
    localStorage.removeItem("email");
    localStorage.removeItem("role");
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("point");
    localStorage.removeItem("user_id");
    localStorage.removeItem("session_token");
    
    // ล้าง PHP session ผ่าน AJAX request (ถ้าใช้ PHP session)
    fetch("logout.php", {
        method: "POST",
        credentials: "same-origin"
    }).then(response => {
        console.log("PHP session cleared");
    }).catch(error => {
        console.error("Error clearing PHP session:", error);
    });
    
    // ไปที่หน้า Login
    window.location.href = "Login.html";
}

		// ฟังก์ชันเปลี่ยนธีม
		function toggleTheme() {
			const body = document.body;
			const navbar = document.querySelector("header");
			const loginSection = document.querySelector(".bg-indigo-600");
			const logo = document.getElementById("logo");
			const isDarkMode = body.classList.toggle("dark-mode");

			if (isDarkMode) {
				logo.src = "image/logo_player/dark_logo.png"; // โลโก้สำหรับโหมดสีเข้ม
				body.style.backgroundColor = "#10142b";
				navbar.style.backgroundColor = "#1a1f36";
				if (loginSection) {
					loginSection.style.backgroundColor = "#232c58";
				}
			} else {
				logo.src = "image/logo_player/light_logo.png"; // โลโก้สำหรับโหมดปกติ/โหมดสว่าง
				body.style.backgroundColor = "#10142b";
				navbar.style.backgroundColor = "#1a1f36";
				if (loginSection) {
					loginSection.style.backgroundColor = "#232c58";
				}
			}

			localStorage.setItem("theme", isDarkMode ? "dark" : "light");
		}


	</script>
</body>

</html>