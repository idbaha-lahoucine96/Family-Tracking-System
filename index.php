<?php


// index.php
require_once 'database.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_family') {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imagePath = '';
        if (isset($_FILES['family_image']) && $_FILES['family_image']['error'] === 0) {
            $extension = pathinfo($_FILES['family_image']['name'], PATHINFO_EXTENSION);
            $imagePath = $uploadDir . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['family_image']['tmp_name'], $imagePath);
        }

        $familyData = [
            'name' => $_POST['name'],
            'city' => $_POST['city'],
            'phone' => $_POST['phone'],
            'latitude' => $_POST['latitude'],
            'longitude' => $_POST['longitude'],
            'image_path' => $imagePath
        ];

        $db->addFamily($familyData);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'get_families') {
    header('Content-Type: application/json');
    echo json_encode($db->getAllFamilies());
    exit;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام تتبع العائلات المغربية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --light-bg: #f8f9fa;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .map-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            margin-top: 2rem;
        }

        #familyMap {
            height: 700px;
            border-radius: 10px;
            overflow: hidden;
        }

        .add-family-btn {
            background-color: var(--accent-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .add-family-btn:hover {
            transform: translateY(-2px);
            background-color: #2980b9;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.8rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: none;
        }

        .tracking-popup {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }

        .family-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 4px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .alerts-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .custom-alert {
            min-width: 300px;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            #familyMap {
                height: 500px;
            }

            .stat-card {
                padding: 1rem;
            }

            .custom-alert {
                min-width: auto;
                width: 90%;
                margin: 0 auto 10px;
            }
        }

        .custom-marker {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .marker-name {
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 2px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .custom-div-icon {
            background: none;
            border: none;
        }
    </style>
</head>

<body>
    <!-- Alerts Container -->
    <div class="alerts-container"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-geo-alt-fill me-2"></i>
                نظام تتبع العائلات المغربية
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="bi bi-people-fill"></i>
                <h3 class="total-families">0</h3>
                <p>إجمالي العائلات</p>
            </div>
            <div class="stat-card">
                <i class="bi bi-building"></i>
                <h3 class="total-cities">0</h3>
                <p>المدن المسجلة</p>
            </div>
            <div class="stat-card">
                <i class="bi bi-geo-alt"></i>
                <h3>منطقة كلميم وادنون</h3>
                <p>نطاق التغطية</p>
            </div>
        </div>

        <!-- Map Header -->
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-primary mb-0">خريطة العائلات</h2>
            <button class="btn add-family-btn text-white" data-bs-toggle="modal" data-bs-target="#addFamilyModal">
                <i class="bi bi-plus-circle me-2"></i>إضافة عائلة
            </button>
        </div>

        <!-- Map Container -->
        <div class="map-container">
            <div id="familyMap"></div>
        </div>
    </div>

    <!-- Add Family Modal -->
    <div class="modal fade" id="addFamilyModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        إضافة عائلة جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="familyForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_family">
                        <div class="mb-4">
                            <label class="form-label">صورة العائلة</label>
                            <input type="file" class="form-control" name="family_image" accept="image/*" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">اسم العائلة</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">طريقة تحديد الموقع</label>
                            <select id="locationMethod" class="form-control">
                                <option value="city">اختيار المدينة</option>
                                <option value="manual">تحديد يدوي على الخريطة</option>
                                <option value="current">الموقع الحالي</option>
                            </select>
                        </div>
                        <div id="citySection" class="mb-4">
                            <label class="form-label">المدينة</label>
                            <select name="city" class="form-control" id="citySelect">
                                <option value="كلميم" data-coords="29.0167,-10.0500">كلميم</option>
                                <option value="سيدي إفني" data-coords="29.3797,-10.1727">سيدي إفني</option>
                                <option value="طانطان" data-coords="28.4378,-11.1028">طانطان</option>
                                <option value="أسا" data-coords="28.3927,-9.4326">أسا</option>
                            </select>
                        </div>
                        <input type="hidden" name="latitude" id="familyLat">
                        <input type="hidden" name="longitude" id="familyLon">
                        <div id="addLocationMap" style="height: 300px; display: none; border-radius: 10px;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn add-family-btn text-white" id="saveFamily">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Show Alert Function
        function showAlert(message, type = 'success') {
            const alertsContainer = document.querySelector('.alerts-container');
            const alert = document.createElement('div');
            alert.className = `custom-alert alert alert-${type}`;
            alert.innerHTML = message;
            alertsContainer.appendChild(alert);

            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Updated Map Configuration for Guelmim Region
        const guelmimBounds = [
            [29.8, -9.2], // شمال شرق
            [28.2, -9.2], // جنوب شرق
            [28.2, -11.2], // جنوب غرب
            [29.8, -11.2] // شمال غرب
        ];

        // Initialize Main Map
        let mainMap = L.map('familyMap', {
            maxBounds: L.latLngBounds(guelmimBounds),
            minZoom: 8
        }).setView([29.0167, -10.0500], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mainMap);

        // Initialize Add Location Map
        let addMap = L.map('addLocationMap', {
            maxBounds: L.latLngBounds(guelmimBounds),
            minZoom: 8
        }).setView([29.0167, -10.0500], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(addMap);

        let addMarker = null;

        // Initialize City Select with Updated Cities
        document.getElementById('citySelect').innerHTML = `
    <option value="كلميم" data-coords="29.0167,-10.0500">كلميم</option>
    <option value="بويزكارن" data-coords="29.2231,-9.1397">بويزكارن</option>
    <option value="افران أطلس الصغير" data-coords="29.0333,-9.3333">افران أطلس الصغير</option>
    <option value="تغجيجت" data-coords="29.1167,-9.3833">تغجيجت</option>
    <option value="أداي" data-coords="29.0833,-10.2333">أداي</option>
    <option value="أسرير" data-coords="29.1500,-10.2667">أسرير</option>
    <option value="تيموله" data-coords="29.2333,-10.1833">تيموله</option>
    <option value="امتضي" data-coords="28.9833,-10.1833">امتضي</option>
    <option value="تكانت" data-coords="29.0500,-10.3333">تكانت</option>
    <option value="أباينو" data-coords="29.1500,-10.1000">أباينو</option>
    <option value="تاغجيجت" data-coords="29.1000,-9.3667">تاغجيجت</option>
    <option value="فم زكيد" data-coords="28.8833,-9.4167">فم زكيد</option>
`;

        // Update Region Title in Statistics Card
        document.querySelector('.stat-card:nth-child(3) h3').textContent = 'إقليم كلميم';
        document.querySelector('.stat-card:nth-child(3) p').textContent = 'جهة كلميم واد نون';

        // Load Families Function
        function loadFamilies() {
            fetch('index.php?action=get_families')
                .then(response => response.json())
                .then(families => {
                    // Clear existing markers
                    mainMap.eachLayer((layer) => {
                        if (layer instanceof L.Marker) {
                            mainMap.removeLayer(layer);
                        }
                    });

                    // Update statistics
                    updateStats(families);

                    families.forEach(family => {
                        if (isInGuelmimRegion(family.latitude, family.longitude)) {
                            const customIcon = L.divIcon({
                                className: 'custom-div-icon',
                                html: `
        <div class="custom-marker">
            <img src="${family.image_path}" 
                style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;"
                alt="${family.name}"
            >
            <div class="marker-name">${family.name}</div>
        </div>
    `,
                                iconSize: [60, 80], // Increased height to accommodate name
                                iconAnchor: [30, 40], // Adjusted to center the marker
                                popupAnchor: [0, -40] // Adjusted to position popup above name
                            });

                            const marker = L.marker([family.latitude, family.longitude], {
                                icon: customIcon
                            }).addTo(mainMap);

                            const popupContent = `
    <div class="tracking-popup">
        <img src="${family.image_path}" class="family-image" alt="${family.name}">
        <h5>${family.name}</h5>
        <p>${family.city}</p>
        <p>${family.phone}</p>
        <button class="btn btn-primary btn-sm" onclick="startTracking(${family.latitude}, ${family.longitude})">
            <i class="bi bi-geo-fill me-2"></i>تتبع
        </button>
    </div>
`;
                            marker.bindPopup(popupContent);
                        }
                    });
                })
                .catch(error => {
                    showAlert('حدث خطأ أثناء تحميل بيانات العائلات', 'danger');
                });
        }

        // Update Statistics
        function updateStats(families) {
            const cities = new Set(families.map(f => f.city));
            document.querySelector('.total-families').textContent = families.length;
            document.querySelector('.total-cities').textContent = cities.size;
        }

        // Updated Check if coordinates are in Guelmim region
        function isInGuelmimRegion(lat, lon) {
            return (lat >= 28.2 && lat <= 29.8 && lon >= -11.2 && lon <= -9.2);
        }

        // Location Method Change Handler
        document.getElementById('locationMethod').addEventListener('change', function() {
            const citySection = document.getElementById('citySection');
            const addLocationMap = document.getElementById('addLocationMap');

            if (addMarker) {
                addMap.removeLayer(addMarker);
            }

            switch (this.value) {
                case 'city':
                    citySection.style.display = 'block';
                    addLocationMap.style.display = 'none';
                    updateCityCoordinates();
                    break;

                case 'manual':
                    citySection.style.display = 'none';
                    addLocationMap.style.display = 'block';
                    setTimeout(() => addMap.invalidateSize(), 300);

                    addMap.on('click', function(e) {
                        if (isInGuelmimRegion(e.latlng.lat, e.latlng.lng)) {
                            if (addMarker) {
                                addMap.removeLayer(addMarker);
                            }
                            addMarker = L.marker(e.latlng).addTo(addMap);
                            document.getElementById('familyLat').value = e.latlng.lat;
                            document.getElementById('familyLon').value = e.latlng.lng;
                        } else {
                            showAlert('يرجى اختيار موقع داخل منطقة كلميم وادنون', 'warning');
                        }
                    });
                    break;

                case 'current':
                    citySection.style.display = 'none';
                    addLocationMap.style.display = 'none';
                    getCurrentLocation();
                    break;
            }
        });

        // Update City Coordinates
        function updateCityCoordinates() {
            const coords = document.getElementById('citySelect').options[
                document.getElementById('citySelect').selectedIndex
            ].dataset.coords.split(',');
            document.getElementById('familyLat').value = parseFloat(coords[0]);
            document.getElementById('familyLon').value = parseFloat(coords[1]);
        }

        // Get Current Location
        function getCurrentLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;

                    if (isInGuelmimRegion(userLat, userLon)) {
                        document.getElementById('familyLat').value = userLat;
                        document.getElementById('familyLon').value = userLon;
                        showAlert('تم تحديد موقعك بنجاح', 'success');
                    } else {
                        showAlert('عذراً، موقعك الحالي خارج منطقة كلميم وادنون', 'warning');
                    }
                }, function() {
                    showAlert('تعذر الوصول إلى الموقع', 'danger');
                });
            } else {
                showAlert('عذراً، متصفحك لا يدعم تحديد الموقع', 'warning');
            }
        }

        // City Select Change Handler
        document.getElementById('citySelect').addEventListener('change', updateCityCoordinates);

        // Save Family Handler
        document.getElementById('saveFamily').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('familyForm'));
            const lat = document.getElementById('familyLat').value;
            const lon = document.getElementById('familyLon').value;

            if (!lat || !lon) {
                showAlert('يرجى تحديد الموقع', 'warning');
                return;
            }

            if (!isInGuelmimRegion(parseFloat(lat), parseFloat(lon))) {
                showAlert('يرجى اختيار موقع داخل منطقة كلميم وادنون', 'warning');
                return;
            }

            fetch('index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addFamilyModal')).hide();
                        showAlert('تمت إضافة العائلة بنجاح', 'success');
                        loadFamilies();
                        document.getElementById('familyForm').reset();
                    }
                })
                .catch(error => {
                    showAlert('حدث خطأ أثناء حفظ البيانات', 'danger');
                });
        });

        // Start Tracking Function
        function startTracking(destLat, destLon) {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;
                    window.open(`https://www.google.com/maps/dir/${userLat},${userLon}/${destLat},${destLon}`, '_blank');
                });
            } else {
                showAlert('عذراً، متصفحك لا يدعم تحديد الموقع', 'warning');
            }
        }

        // Initialize
        loadFamilies();
    </script>
</body>

</html>