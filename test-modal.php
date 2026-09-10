<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Test</title>
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Modal Test</h1>
        
        <button type="button" data-profile-open class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all">
            <span class="material-symbols-outlined">edit</span>
            Open Modal
        </button>

        <!-- Modal -->
        <div id="profileEditModal" class="profile-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
            <div class="profile-modal-panel w-full max-w-2xl rounded-2xl bg-white p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold">Edit Profile</h3>
                    <button type="button" data-profile-close class="text-gray-500 hover:text-gray-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">This is a test modal. Click "Open Modal" button to show it or click "Close Modal" to hide it.</p>
                <button type="button" data-profile-close class="px-4 py-2 bg-blue-600 text-white rounded-lg">Close Modal</button>
            </div>
        </div>
    </div>

    <script>
        function openProfileEditModal() {
            console.log('Opening modal');
            const modal = document.getElementById('profileEditModal');
            if (!modal) {
                console.error('Modal not found');
                return;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('Modal is now visible');
        }

        function closeProfileEditModal() {
            console.log('Closing modal');
            const modal = document.getElementById('profileEditModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            console.log('Modal is now hidden');
        }

        function bindProfileModalControls() {
            console.log('Binding modal controls');
            const modal = document.getElementById('profileEditModal');
            
            document.querySelectorAll('[data-profile-open]').forEach(function(button) {
                console.log('Found open button:', button);
                button.removeEventListener('click', button._profileOpenHandler);
                button._profileOpenHandler = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileEditModal();
                };
                button.addEventListener('click', button._profileOpenHandler);
            });

            document.querySelectorAll('[data-profile-close]').forEach(function(button) {
                console.log('Found close button:', button);
                button.removeEventListener('click', button._profileCloseHandler);
                button._profileCloseHandler = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    closeProfileEditModal();
                };
                button.addEventListener('click', button._profileCloseHandler);
            });
        }

        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindProfileModalControls);
        } else {
            bindProfileModalControls();
        }
    </script>
</body>
</html>
