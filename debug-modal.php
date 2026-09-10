<?php
// Quick debug: Check if modal button click triggers the function
require_once __DIR__ . '/init.php';

if (!$user->isLoggedIn() || $_SESSION['role'] !== 'youth') {
    echo "Not logged in or not youth role";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Modal Debug</title>
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body class="bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Profile Modal Debug Test</h1>
        
        <p class="mb-4 text-slate-600">User ID: <?php echo $_SESSION['user_id']; ?> | Role: <?php echo $_SESSION['role']; ?></p>
        
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-900 mb-2"><strong>Instructions:</strong></p>
            <ol class="list-decimal list-inside space-y-1 text-blue-800 text-sm">
                <li>Click the "Open Modal" button below</li>
                <li>A modal should appear on top</li>
                <li>Look at the browser console for debug messages</li>
            </ol>
        </div>

        <button type="button" data-profile-open class="px-5 py-3 bg-blue-900 text-white rounded-lg font-bold mb-8">
            <span class="material-symbols-outlined inline mr-2">edit</span>
            Open Modal
        </button>

        <!-- Modal -->
        <div id="profileEditModal" class="profile-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
            <div class="profile-modal-panel w-full max-w-2xl rounded-2xl bg-white p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold">Edit Profile - SUCCESS!</h3>
                    <button type="button" data-profile-close class="text-gray-500 hover:text-gray-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">✅ Modal is working! You clicked the button and the modal appeared.</p>
                <p class="text-gray-600 text-sm mb-4">Try closing the modal using:</p>
                <ul class="list-disc list-inside text-gray-600 text-sm mb-4 space-y-1">
                    <li>The close button (X)</li>
                    <li>The "Close" button below</li>
                    <li>Pressing the Escape key</li>
                    <li>Clicking outside the modal (on the dark background)</li>
                </ul>
                <button type="button" data-profile-close class="px-4 py-2 bg-blue-600 text-white rounded-lg">Close Modal</button>
            </div>
        </div>
    </div>

    <script>
        console.log('Script starting...');

        function openProfileEditModal() {
            console.log('openProfileEditModal() called');
            const modal = document.getElementById('profileEditModal');
            if (!modal) {
                console.error('Modal element not found!');
                return;
            }
            console.log('Modal element found, removing hidden class');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('Modal opened successfully');
        }

        function closeProfileEditModal() {
            console.log('closeProfileEditModal() called');
            const modal = document.getElementById('profileEditModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            console.log('Modal closed');
        }

        function bindProfileModalControls() {
            console.log('bindProfileModalControls() called');
            const modal = document.getElementById('profileEditModal');
            const openButtons = document.querySelectorAll('[data-profile-open]');
            const closeButtons = document.querySelectorAll('[data-profile-close]');
            
            console.log('Found ' + openButtons.length + ' open buttons');
            console.log('Found ' + closeButtons.length + ' close buttons');
            
            openButtons.forEach(function(button) {
                console.log('Binding open button:', button);
                button.removeEventListener('click', button._profileOpenHandler);
                button._profileOpenHandler = function(event) {
                    console.log('Open button clicked!');
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileEditModal();
                };
                button.addEventListener('click', button._profileOpenHandler);
            });

            closeButtons.forEach(function(button) {
                console.log('Binding close button:', button);
                button.removeEventListener('click', button._profileCloseHandler);
                button._profileCloseHandler = function(event) {
                    console.log('Close button clicked!');
                    event.preventDefault();
                    event.stopPropagation();
                    closeProfileEditModal();
                };
                button.addEventListener('click', button._profileCloseHandler);
            });

            if (modal && !modal.dataset.bound) {
                modal.dataset.bound = 'true';
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        console.log('Backdrop clicked');
                        closeProfileEditModal();
                    }
                });
            }
        }

        window.openProfileEditModal = openProfileEditModal;
        window.closeProfileEditModal = closeProfileEditModal;

        console.log('Document readyState:', document.readyState);
        if (document.readyState === 'loading') {
            console.log('Adding DOMContentLoaded listener');
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOMContentLoaded fired');
                bindProfileModalControls();
            });
        } else {
            console.log('Document already loaded, calling bindProfileModalControls immediately');
            bindProfileModalControls();
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                console.log('Escape key pressed');
                closeProfileEditModal();
            }
        });

        console.log('Script completed');
    </script>
</body>
</html>
