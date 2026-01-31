<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Waste2Worth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-center text-green-600">Join Waste2Worth</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-sm mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="<?php echo ($_ENV['APP_URL'] ?? 'http://localhost/waste2worth'); ?>/register" method="POST">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Select Your Role</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer border-2 border-gray-200 rounded-lg p-4 flex flex-col items-center hover:border-green-500 transition-colors peer-checked:border-green-600">
                        <input type="radio" name="role" value="client" class="hidden peer" <?php echo (isset($old['role']) && $old['role'] === 'client') || !isset($old['role']) ? 'checked' : ''; ?>>
                        <span class="material-symbols-outlined text-3xl mb-2">person</span>
                        <span class="text-sm font-medium">Client</span>
                    </label>
                    <label class="cursor-pointer border-2 border-gray-200 rounded-lg p-4 flex flex-col items-center hover:border-green-500 transition-colors">
                        <input type="radio" name="role" value="collector" class="hidden peer" <?php echo isset($old['role']) && $old['role'] === 'collector' ? 'checked' : ''; ?>>
                        <span class="material-symbols-outlined text-3xl mb-2">local_shipping</span>
                        <span class="text-sm font-medium">Collector</span>
                    </label>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" value="<?php echo $old['email'] ?? ''; ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone Number</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="tel" value="<?php echo $old['phone'] ?? ''; ?>" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Confirm Password</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" name="confirm_password" type="password" required>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" type="submit">
                    Create Account
                </button>
            </div>
            <p class="text-center text-gray-600 text-sm mt-4">
                Already have an account? <a href="/login" class="text-green-600 hover:underline">Login</a>
            </p>
        </form>
    </div>
    <script>
        // Simple JS to handle role selection UI
        const radios = document.querySelectorAll('input[name="role"]');
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                radios.forEach(r => {
                    r.parentElement.classList.remove('border-green-600', 'bg-green-50');
                    r.parentElement.classList.add('border-gray-200');
                });
                if (radio.checked) {
                    radio.parentElement.classList.remove('border-gray-200');
                    radio.parentElement.classList.add('border-green-600', 'bg-green-50');
                }
            });
        });
        // Set initial state
        document.querySelector('input[name="role"]:checked').parentElement.classList.add('border-green-600', 'bg-green-50');
    </script>
</body>
</html>
