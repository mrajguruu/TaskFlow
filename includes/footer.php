    </main>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?>. Built with ❤️</p>
        </div>
    </footer>

    <script>
        function toggleUserMenu() {
            const dropdown = document.getElementById('userMenuDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userMenuDropdown');

            if (!userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

    <!-- Settings Loader (lightweight) -->
    <script>
        // Load settings from localStorage for all pages
        window.getTaskflowSettings = function() {
            const stored = localStorage.getItem('taskflow_settings');
            if (stored) {
                try {
                    return JSON.parse(stored);
                } catch (e) {
                    return null;
                }
            }
            return null;
        };
    </script>

    <!-- Toast Notification System -->
    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>

    <!-- Skeleton Loading -->
    <script src="<?= APP_URL ?>/assets/js/skeleton.js"></script>

    <!-- Mobile Navigation -->
    <script src="<?= APP_URL ?>/assets/js/mobile-nav.js"></script>

    <!-- Dark Mode -->
    <script src="<?= APP_URL ?>/assets/js/dark-mode.js"></script>

    <!-- Form Validation -->
    <script src="<?= APP_URL ?>/assets/js/form-validation.js"></script>

    <!-- Password Strength -->
    <script src="<?= APP_URL ?>/assets/js/password-strength.js"></script>

    <!-- Form Auto-Save -->
    <script src="<?= APP_URL ?>/assets/js/form-autosave.js"></script>

    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?= APP_URL ?>/assets/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
