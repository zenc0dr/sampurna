<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Sampurna</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/sampurna.css', 'sampurna') }}">
    <link rel="icon" type="image/svg" href="/sampurna/images/icons/sampurna-icon.svg">
    <script src="{{ mix('js/sampurna.js', 'sampurna') }}" defer></script>
</head>
<body>
<div id="sampurna-app"></div>
</body>
</html>
