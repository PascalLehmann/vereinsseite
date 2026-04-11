// assets/js/script.js
// C-Entwickler-Mindset: Wir warten, bis der DOM-Baum (die Variablen-Deklaration) vollständig geladen ist.
$(document).ready(function() {
    
    // Wir selektieren unsere wichtigen DOM-Elemente
    const $lightbox = $('#imageLightbox');
    const $lightboxImage = $('#lightboxImage');
    const $closeButton = $('.news-lightbox-close');

    // FUNKTION 1: Klick auf ein Thumbnail in der Galerie
    $('.news-gallery .news-thumbnail').on('click', function() {
        
        // A) Wir holen uns den Pointer (den Pfad) des angeklickten Bildes
        const src = $(this).attr('src');
        
        // B) Wir übergeben diesen Pfad an unser leeres Modal-Bild
        $lightboxImage.attr('src', src);
        
        // C) Wir fügen die Klasse 'active' hinzu, wodurch das Modal per CSS eingeblendet und zentriert wird
        $lightbox.addClass('active');
        
        // D) Wir verhindern das Scrollen der Hauptseite im Hintergrund (UX-Fix)
        $('body').css('overflow', 'hidden');
    });

    // FUNKTION 2: Klick zum Schließen
    // A) Klick auf das 'x'
    $closeButton.on('click', function() {
        closeLightbox();
    });

    // B) Klick auf den schwarzen Hintergrund (aber NICHT auf das Bild selbst!)
    $lightbox.on('click', function(event) {
        // Wir prüfen, ob das geklickte Element das Lightbox-Overlay ist, nicht das Bild selbst.
        if (event.target !== $lightboxImage[0]) {
            closeLightbox();
        }
    });

    // C) Klick auf die 'Esc' Taste
    $(document).on('keydown', function(event) {
        if (event.key === "Escape" && $lightbox.hasClass('active')) {
            closeLightbox();
        }
    });

    // Hilfsfunktion zum sauberen Schließen
    function closeLightbox() {
        // Wir entfernen die 'active' Klasse -> CSS blendet es aus
        $lightbox.removeClass('active');
        // Wir reaktivieren das Scrollen der Hauptseite
        $('body').css('overflow', 'auto');
        // Optional: Wir löschen den Bild-Pfad, um Speicher zu sparen
        setTimeout(function() {
            $lightboxImage.attr('src', '');
        }, 400); // Warten bis die CSS-Transition fertig ist
    }
});