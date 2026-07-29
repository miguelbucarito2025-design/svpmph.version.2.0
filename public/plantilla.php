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
  <link rel="stylesheet" href="public/css/style.css?v=<?php echo date('dd-yy-ww') ?>" />
  <script src='public/js/alert.js' defer></script>
</head>

<body>





  <?php include_once 'public/views/alert.php'; ?>
</body>

</html>