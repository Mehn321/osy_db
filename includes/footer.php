<?php if (isset($user) && $user->isLoggedIn()): ?>
            </div>
        </main>
    <?php endif; ?>

    <!-- Global Custom Confirm Modal -->
    <div id="globalConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999]">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl transform transition-all">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                        <span class="material-symbols-outlined text-red-600 dark:text-red-400" id="globalConfirmIcon">warning</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2" id="globalConfirmTitle">Confirm Action</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6" id="globalConfirmMessage">Are you sure?</p>
                    <div class="flex gap-3">
                        <button type="button" id="globalConfirmCancelBtn" class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-sm hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button type="button" id="globalConfirmOkBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-sm hover:bg-red-700 transition">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Custom Alert Modal -->
    <div id="globalAlertModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999]">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl transform transition-all">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400" id="globalAlertIcon">info</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2" id="globalAlertTitle">Information</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6" id="globalAlertMessage"></p>
                    <div class="flex gap-3">
                        <button type="button" id="globalAlertOkBtn" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global utility for Custom Confirm
        window.customConfirm = function(message, callback, title = 'Confirm Action', icon = 'warning', confirmBtnClass = 'bg-red-600 hover:bg-red-700') {
            const modal = document.getElementById('globalConfirmModal');
            document.getElementById('globalConfirmMessage').textContent = message;
            document.getElementById('globalConfirmTitle').textContent = title;
            document.getElementById('globalConfirmIcon').textContent = icon;
            
            const okBtn = document.getElementById('globalConfirmOkBtn');
            okBtn.className = 'flex-1 px-4 py-2 text-white rounded-lg font-bold text-sm transition ' + confirmBtnClass;
            
            const cancelBtn = document.getElementById('globalConfirmCancelBtn');
            
            const cleanup = () => {
                modal.classList.add('hidden');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
            };

            const onOk = () => { cleanup(); if (callback) callback(true); };
            const onCancel = () => { cleanup(); if (callback) callback(false); };

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);

            modal.classList.remove('hidden');
        };

        // Global utility for Custom Alert
        window.customAlert = function(message, title = 'Information', icon = 'info') {
            const modal = document.getElementById('globalAlertModal');
            document.getElementById('globalAlertMessage').textContent = message;
            document.getElementById('globalAlertTitle').textContent = title;
            document.getElementById('globalAlertIcon').textContent = icon;
            
            const okBtn = document.getElementById('globalAlertOkBtn');
            const cleanup = () => {
                modal.classList.add('hidden');
                okBtn.removeEventListener('click', cleanup);
            };
            okBtn.addEventListener('click', cleanup);
            
            modal.classList.remove('hidden');
        };

        // Intercept all forms that use onsubmit="return confirm('...')"
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const onsubmitAttr = form.getAttribute('onsubmit');
                if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    // Extract the message from confirm('...')
                    const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                    if (match) {
                        const message = match[1];
                        form.removeAttribute('onsubmit');
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            window.customConfirm(message, (confirmed) => {
                                if (confirmed) {
                                    // Submit programmatically
                                    form.submit();
                                }
                            });
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>