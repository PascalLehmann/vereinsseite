<footer id="main-footer">
    <p>
        &copy; <?php echo date("Y"); ?>
        <a href="/index.php" style="font-weight: bold; color: var(--primary-white);">SKV9killer.de</a> |
        <a href="/pages/rechtliches/impressum.php">Impressum</a> |
        <a href="/pages/rechtliches/datenschutz.php">Datenschutz</a> |
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="/pages/admin/logout.php" class="footer-login-link">Logout</a>
        <?php else: ?>
            <a href="/pages/admin/login.php" class="footer-login-link">Login</a>
        <?php endif; ?>
    </p>
</footer>

<!-- Globale Lightbox (wird von script.js gesteuert) -->
<div id="imageLightbox" class="news-lightbox-modal">
    <span class="news-lightbox-close">&times;</span>
    <div class="news-lightbox-content">
        <img class="news-lightbox-image" id="lightboxImage" alt="Vergrößerte Ansicht">
    </div>
</div>

<script src="/assets/js/script.js"></script>
</body>

</html>