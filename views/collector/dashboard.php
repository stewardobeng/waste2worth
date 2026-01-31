<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard - Waste2Worth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-green-800 text-white flex flex-col">
            <div class="p-6">
                <h1 class="text-2xl font-bold">Waste2Worth</h1>
                <p class="text-green-300 text-xs mt-1">Collector Portal</p>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-white/10 rounded-lg">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span>Requests</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">payments</span>
                    <span>Earnings</span>
                </a>
            </nav>
            <div class="p-4 border-t border-white/10">
                <a href="/logout" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-10">
                <h2 class="text-xl font-bold text-gray-800">Dashboard Overview</h2>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-sm font-medium text-gray-600"><?php echo $profile['availability_status'] ?? 'Offline'; ?></span>
                    </div>
                </div>
            </header>

            <div class="p-8 max-w-6xl mx-auto">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Total Pickups</p>
                        <h3 class="text-3xl font-bold text-gray-900">0</h3>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Monthly Earnings</p>
                        <h3 class="text-3xl font-bold text-gray-900">$0.00</h3>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Average Rating</p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $profile['rating_avg'] ?? '0.00'; ?> ★</h3>
                    </div>
                </div>

                <!-- Profile Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Profile Settings</h3>
                    <form action="/collector/profile/update" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Display Name</label>
                            <input type="text" name="display_name" value="<?php echo $profile['display_name'] ?? ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Availability</label>
                            <select name="availability_status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="available" <?php echo ($profile['availability_status'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="busy" <?php echo ($profile['availability_status'] ?? '') === 'busy' ? 'selected' : ''; ?>>Busy</option>
                                <option value="offline" <?php echo ($profile['availability_status'] ?? '') === 'offline' ? 'selected' : ''; ?>>Offline</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Bio</label>
                            <textarea name="bio" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500"><?php echo $profile['bio'] ?? ''; ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Latitude</label>
                            <input type="text" name="latitude" value="<?php echo $profile['latitude'] ?? ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Longitude</label>
                            <input type="text" name="longitude" value="<?php echo $profile['longitude'] ?? ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Service Radius (km)</label>
                            <input type="number" name="service_radius_km" value="<?php echo $profile['service_radius_km'] ?? '5'; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 transition-colors">Save Profile</button>
                        </div>
                    </form>
                </div>

                <!-- Upcoming Requests -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Recent Pickup Requests</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Client</th>
                                    <th class="px-6 py-4">Address</th>
                                    <th class="px-6 py-4">Time</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No requests found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo $request['client_email']; ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $request['pickup_address']; ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $request['desired_pickup_time']; ?></td>
                                            <td class="px-6 py-4 text-sm font-bold text-orange-500 uppercase"><?php echo $request['status']; ?></td>
                                            <td class="px-6 py-4 flex gap-2">
                                                <form action="/collector/status/update" method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                    <input type="hidden" name="status" value="accepted">
                                                    <button type="submit" class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded font-bold hover:bg-green-200">Accept</button>
                                                </form>
                                                <form action="/collector/status/update" method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded font-bold hover:bg-red-200">Decline</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
