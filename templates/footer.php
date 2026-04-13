<footer id="main-footer">
    <p>
        &copy; <?php echo date("Y"); ?>
        <a href="/index.php" style="font-weight: bold; color: var(--primary-white);">SKV9killer.de</a> |
        <a href="/impressum.php">Impressum</a> |
        <a href="/datenschutz.php">Datenschutz</a> |
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="/pages/admin/logout.php" class="footer-login-link">Logout</a>
        <?php else: ?>
            <a href="/pages/admin/login.php" class="footer-login-link">Login</a>
        <?php endif; ?>
    </p>
</footer>
<script src="/assets/js/script.js"></script>
<script>
    function openLightbox(src) {
        const overlay = document.getElementById('lightbox-overlay');
        const img = document.getElementById('lightbox-img');
        img.src = src;
        overlay.classList.add('active');
    }

    function closeLightbox() {
        const overlay = document.getElementById('lightbox-overlay');
        overlay.classList.remove('active');
    }
</script>
</body>

</html>