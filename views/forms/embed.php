<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($form['title']) ?></title>
    <style>
        body { margin: 0; padding: 0; background: transparent; overflow-y: auto; }
        iframe { width: 100%; height: 100%; border: 0; }
    </style>
</head>
<body>
    <?php
        // Just load the normal public view inside the iframe wrapper
        echo view('forms.public', ['form' => $form, 'schema' => $schema, 'settings' => $settings, 'theme' => $theme], 'layouts.public');
    ?>
</body>
</html>
