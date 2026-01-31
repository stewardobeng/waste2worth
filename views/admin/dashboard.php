<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Waste2Worth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-green-500">Waste2Worth</h1>
                <p class="text-gray-400 text-xs mt-1">Municipal Admin</p>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-gray-800 rounded-lg">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Analytics</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">group</span>
                    <span>Collectors</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span>Requests</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">redeem</span>
                    <span>Rewards</span>
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <a href="/logout" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">
            <header class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800">System Overview</h2>
                <div class="flex gap-4">
                    <button class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">Export PDF</button>
                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Run Reports</button>
                </div>
            </header>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500 mb-1">Total Waste</p>
                    <h3 class="text-2xl font-bold text-gray-900">1,245 kg</h3>
                    <p class="text-xs text-green-600 mt-2">↑ 12% from last month</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500 mb-1">CO2 Offset</p>
                    <h3 class="text-2xl font-bold text-gray-900">3.4 tons</h3>
                    <p class="text-xs text-green-600 mt-2">↑ 8% from last month</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500 mb-1">Active Collectors</p>
                    <h3 class="text-2xl font-bold text-gray-900">42</h3>
                    <p class="text-xs text-gray-400 mt-2">3 pending verification</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500 mb-1">Completion Rate</p>
                    <h3 class="text-2xl font-bold text-gray-900">94.2%</h3>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="bg-green-600 h-1.5 rounded-full" style="width: 94%"></div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Waste Diversion Trends</h3>
                    <canvas id="wasteChart"></canvas>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Collection by Type</h3>
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Waste Trends Chart
        new Chart(document.getElementById('wasteChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Waste Collected (kg)',
                    data: [400, 600, 550, 800, 950, 1245],
                    borderColor: '#10b981',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Waste Type Chart
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: ['Plastic', 'Paper', 'Organic', 'Metal', 'Other'],
                datasets: [{
                    data: [45, 20, 25, 5, 5],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
