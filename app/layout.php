<?php
/** @var string $template */
$title = $title ?? 'Accounting and advisory';
$description = $description ?? 'Practical accounting and advisory support for Australian businesses.';
$section = $section ?? 'home';
$url = site_url($path ?? '/');
$isPreview = config('SITE_MODE') !== 'production';
?>
<!doctype html>
<html lang="en-AU">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#10272b">
  <meta name="description" content="<?= e($description) ?>">
  <meta name="robots" content="<?= $isPreview ? 'noindex,nofollow' : 'index,follow' ?>">
  <?php if ($url): ?><link rel="canonical" href="<?= e($url) ?>"><?php endif; ?>
  <meta property="og:type" content="website">
  <meta property="og:locale" content="en_AU">
  <meta property="og:title" content="<?= e($title) ?> | <?= e(config('BRAND_NAME')) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <?php if ($url): ?><meta property="og:url" content="<?= e($url) ?>"><?php endif; ?>
  <title><?= e($title) ?> | <?= e(config('BRAND_NAME')) ?></title>
  <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/css/style.css?v=3">
  <script defer src="/assets/js/main.js?v=3"></script>
</head>
<body>
<?php require APP_ROOT . '/app/partials/header.php'; ?>
<?php require APP_ROOT . '/app/views/' . $template . '.php'; ?>
<?php require APP_ROOT . '/app/partials/footer.php'; ?>
</body>
</html>
