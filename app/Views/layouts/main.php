<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'POS System') ?> · CounterPoint</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= base_url(session('user_id') ? 'dashboard' : 'login') ?>"><span class="brand-mark">CP</span><span>CounterPoint</span></a>
    <?php if (session('user_id')): ?>
        <nav class="nav-links" aria-label="Main navigation">
            <a href="<?= base_url('dashboard') ?>">Dashboard</a><a href="<?= base_url('products') ?>">Products</a><a href="<?= base_url('customers') ?>">Customers</a><a href="<?= base_url('staff') ?>">Staff</a><a href="<?= base_url('sales') ?>">Sales</a>
        </nav>
        <div class="account"><span><?= esc(session('full_name')) ?></span><form method="post" action="<?= base_url('logout') ?>"><?= csrf_field() ?><button class="link-button" type="submit">Sign out</button></form></div>
    <?php endif; ?>
</header>
<main class="container">
    <?php if ($message = session('success')): ?><div class="alert success" role="status"><?= esc($message) ?></div><?php endif; ?>
    <?php if ($message = session('error')): ?><div class="alert error" role="alert"><?= esc($message) ?></div><?php endif; ?>
    <?php if (session('user_id') && validation_errors()): ?><div class="alert error" role="alert"><?= validation_list_errors() ?></div><?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
