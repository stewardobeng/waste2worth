<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Waste2Worth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
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
                        <a href="/client/dashboard" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</a>
                        <a href="/client/discovery" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Find Collectors</a>
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
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Your Activity</h1>
                <a href="/client/discovery" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-green-700">Request New Pickup</a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Active Requests</p>
                    <h3 class="text-3xl font-bold text-gray-900">0</h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Total Waste Diverted</p>
                    <h3 class="text-3xl font-bold text-gray-900">0 kg</h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Impact Score</p>
                    <h3 class="text-3xl font-bold text-green-600">Low</h3>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Service Requests</h2>
                </div>
                <div class="p-6 text-center text-gray-500">
                    <p>You haven't made any requests yet. <a href="/client/discovery" class="text-green-600 hover:underline">Find a collector</a> to get started.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
