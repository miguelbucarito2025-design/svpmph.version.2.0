<?php

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SVPMPH-oficial V=2</title>
  <base href="/<?= HOST ?>/">

  <link rel="stylesheet" href="public/css/style.css" />
</head>

<body>
  <?php
  $contenido = $vista ?? 'index.php';
  include_once $contenido; ?>
</body>

</html>