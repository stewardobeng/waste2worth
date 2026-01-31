<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Collectors - Waste2Worth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        #map { height: 500px; width: 100%; border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-green-600 font-bold text-xl">Waste2Worth</span>
                    </div>
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/client/dashboard" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</a>
                        <a href="/client/discovery" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Find Collectors</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="/logout" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Discovery Nearby Collectors</h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div id="map" class="shadow-sm"></div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Available Collectors</h2>
                    <div id="collector-list" class="space-y-4 max-h-[400px] overflow-y-auto">
                        <!-- Collector list items will be injected here -->
                        <p class="text-gray-500 text-sm">Use the map to find collectors near you.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let markers = [];

        function findNearby(lat, lng) {
            fetch(`/api/client/collectors/nearby?lat=${lat}&lng=${lng}`)
                .then(res => res.json())
                .then(data => {
                    // Clear markers
                    markers.forEach(m => map.removeLayer(m));
                    markers = [];
                    
                    const list = document.getElementById('collector-list');
                    list.innerHTML = '';

                    if (data.length === 0) {
                        list.innerHTML = '<p class="text-gray-500 text-sm">No collectors found in this area.</p>';
                        return;
                    }

                    data.forEach(collector => {
                        const marker = L.marker([collector.latitude, collector.longitude])
                            .bindPopup(`<b>${collector.display_name}</b><br>${collector.availability_status}`)
                            .addTo(map);
                        markers.push(marker);

                        const div = document.createElement('div');
                        div.className = 'p-4 border border-gray-100 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors';
                        div.innerHTML = `
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900">${collector.display_name}</h3>
                                    <p class="text-xs text-gray-500">${collector.availability_status} • ${parseFloat(collector.distance).toFixed(2)} km away</p>
                                </div>
                                <span class="bg-green-100 text-green-800 text-[10px] px-2 py-0.5 rounded-full font-bold">${collector.rating_avg} ★</span>
                            </div>
                            <button onclick="requestPickup(${collector.user_id})" class="mt-3 w-full bg-green-600 text-white text-xs py-2 rounded font-bold hover:bg-green-700">Request Pickup</button>
                        `;
                        list.appendChild(div);
                    });
                });
        }

        // Use Geolocation API
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 13);
                findNearby(lat, lng);
            }, () => {
                console.error("Geolocation failed");
            });
        }

        function requestPickup(collectorId) {
            alert("Redirecting to pickup request for collector ID: " + collectorId);
            // location.href = '/client/request?collector_id=' + collectorId;
        }
    </script>
</body>
</html>
