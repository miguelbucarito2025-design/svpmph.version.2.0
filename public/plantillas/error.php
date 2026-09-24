<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/form.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/global.css?v=<?php echo time(); ?>" />
  <script defer src="<?= URL_BASE ?>public/js/global.js?v=<?php echo time(); ?>"></script>
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <base href="/<?= HOST ?>/">


  <title>Error - SVPMPH</title>
</head>

<body>
  <?php
  require_once 'public/layaout/icon_svg.php';
  require_once 'public/layaout/alert.php';
  ?>

  <?php

  require_once 'public/views/error/index.php';

  ?>



</body>

</html>