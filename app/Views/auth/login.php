<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="auth-shell">
    <div class="auth-story">
        <div><p class="eyebrow">BUILT FOR THE WAY YOU SELL</p><h2>One clear view of your PC store.</h2><p>Products, people, and every transaction—organized for a faster, more confident day at the counter.</p></div>
        <div class="auth-story-bottom"><span>INVENTORY</span><span>CUSTOMERS</span><span>SALES</span></div>
    </div>
    <div class="auth-card">
        <p class="eyebrow">STAFF ACCESS</p>
        <h1>Welcome back</h1>
        <p class="muted">Sign in to manage inventory, customers, staff, and sales.</p>
        <?php if ($message = session('error')): ?><div class="alert error"><?= esc($message) ?></div><?php endif; ?>
        <?php if (validation_errors()): ?><div class="alert error"><?= validation_list_errors() ?></div><?php endif; ?>
        <form method="post" action="<?= base_url('login') ?>" class="stack">
            <?= csrf_field() ?>
            <label>Username<input name="username" value="<?= esc(old('username'), 'attr') ?>" autocomplete="username" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button primary" type="submit">Sign in</button>
        </form>
        <p class="small muted">Need access? Ask your store administrator to create a staff account for you.</p>
    </div>
</section>
<?= $this->endSection() ?>
