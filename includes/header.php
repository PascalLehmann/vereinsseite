<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "SKV9killer"; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (document.querySelector('#news_inhalt')) {
            CKEDITOR.replace('news_inhalt', {
                language: 'de',
                height: 400,
                // Fügt zusätzliche Freiheit für Tabellen und Farben hinzu
                extraPlugins: 'colorbutton,font,justify,table,tableresize',
                removeButtons: 'About' 
            });
        }
    });
</script>

<style>
    /* Sorgt dafür, dass der Editor eine schöne Mindesthöhe hat */
    .ck-editor__editable {
        min-height: 300px;
    }
</style>
</head>
<body>