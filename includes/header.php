<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SKV9killer.de'; ?></title>
    
    <link rel="stylesheet" href="/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
    /* Kleiner Fix für Select2 im Design */
    .select2-container--default .select2-selection--single {
        border-radius: 10px;
        height: 40px;
        border: 1px solid #ddd;
        padding-top: 5px;
    }
</style>
</head>
<body>