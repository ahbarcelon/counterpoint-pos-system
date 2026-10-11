<?php
$signedIn = (int) session('user_id') > 0;
$isAdmin = (int) session('is_admin') === 1;
$section = service('uri')->getSegment(1) ?: 'dashboard';
$profileUrl = base_url('staff/' . (int) session('user_id'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10192b">
    <title><?= esc($title ?? 'POS System') ?> · CounterPoint</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body class="<?= $signedIn ? 'app-page' : 'guest-page' ?>">
<?php if ($signedIn): ?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= base_url('dashboard') ?>" aria-label="CounterPoint dashboard">
            <span class="brand-mark">CP</span>
            <span class="brand-copy"><strong>CounterPoint</strong><small>PC STORE POS</small></span>
        </a>
        <p class="nav-caption">WORKSPACE</p>
        <nav class="nav-links" aria-label="Main navigation">
            <a class="<?= $section === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>"><span class="nav-symbol">◫</span>Dashboard</a>
            <a class="<?= $section === 'products' ? 'active' : '' ?>" href="<?= base_url('products') ?>"><span class="nav-symbol">▤</span>Products</a>
            <a class="<?= $section === 'customers' ? 'active' : '' ?>" href="<?= base_url('customers') ?>"><span class="nav-symbol">◎</span>Customers</a>
            <a class="<?= $section === 'sales' ? 'active' : '' ?>" href="<?= base_url('sales') ?>"><span class="nav-symbol">↗</span>Sales history</a>
            <a class="<?= $section === 'staff' ? 'active' : '' ?>" href="<?= $isAdmin ? base_url('staff') : $profileUrl ?>"><span class="nav-symbol">◇</span><?= $isAdmin ? 'Staff' : 'My profile' ?></a>
        </nav>
        <div class="sidebar-bottom">
            <div class="store-status"><span class="status-dot"></span><span><strong>Store workspace</strong><small>Inventory & sales ready</small></span></div>
            <a class="sidebar-user" href="<?= $profileUrl ?>"><span class="profile-initial"><?= esc(strtoupper(substr((string) session('full_name'), 0, 1))) ?></span><span><strong><?= esc(session('full_name')) ?></strong><small><?= $isAdmin ? 'Administrator' : 'Staff member' ?></small></span></a>
        </div>
    </aside>
    <div class="main-shell">
        <header class="topbar">
            <div><p class="topbar-kicker">COUNTERPOINT / <?= esc(strtoupper($section)) ?></p><strong><?= esc($title ?? 'Dashboard') ?></strong></div>
            <div class="topbar-actions"><span class="topbar-date"><?= esc(date('D, M j, Y')) ?></span><form method="post" action="<?= base_url('logout') ?>"><?= csrf_field() ?><button class="signout-button" type="submit">Sign out <span aria-hidden="true">↗</span></button></form></div>
        </header>
        <main class="container">
            <?php if ($message = session('success')): ?><div class="alert success" role="status"><?= esc($message) ?></div><?php endif; ?>
            <?php if ($message = session('error')): ?><div class="alert error" role="alert"><?= esc($message) ?></div><?php endif; ?>
            <?php if (validation_errors()): ?><div class="alert error" role="alert"><?= validation_list_errors() ?></div><?php endif; ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<?php else: ?>
<header class="guest-bar"><a class="brand" href="<?= base_url('login') ?>"><span class="brand-mark">CP</span><span class="brand-copy"><strong>CounterPoint</strong><small>PC STORE POS</small></span></a><span>Modern retail, made simple.</span></header>
<main class="container guest-container"><?php if ($message = session('success')): ?><div class="alert success" role="status"><?= esc($message) ?></div><?php endif; ?><?= $this->renderSection('content') ?></main>
<?php endif; ?>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
