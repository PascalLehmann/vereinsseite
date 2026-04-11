<footer id="main-footer">
        <p>
            &copy; <?php echo date("Y"); ?> 
            <a href="index.php" style="font-weight: bold; color: var(--primary-white);">SKV9killer.de</a> | 
            <a href="impressum.php">Impressum</a> | 
            <a href="datenschutz.php">Datenschutz</a>
        </p>
    </footer>
    <div id="lightbox-overlay" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="Vergrößerte Ansicht">
</div>

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