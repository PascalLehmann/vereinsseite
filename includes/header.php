<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "SKV9killer"; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    // Diese Funktion wartet, bis die Seite bereit ist
    window.onload = function() {
        if (document.getElementById('news_inhalt')) {
            CKEDITOR.replace('news_inhalt', {
                language: 'de',
                height: 400,
                // Diese Zeile ist wichtig, damit du alle HTML-Tags (Tabellen, Farben etc.) nutzen kannst
                allowedContent: true, 
                // Falls du Tabellen und Farben willst, laden wir sie hier sicherheitshalber mit
                extraPlugins: 'colorbutton,font,justify,table,tableresize'
            });
        }
    };
</script>

<style>
    /* Sorgt dafür, dass der Editor eine schöne Mindesthöhe hat */
    .ck-editor__editable {
        min-height: 300px;
    }
</style>
</head>
<body>