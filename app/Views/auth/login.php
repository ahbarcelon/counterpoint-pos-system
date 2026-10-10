<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="auth-shell">
    <div class="auth-card">
        <p class="eyebrow">STAFF ACCESS</p>
        <h1>Welcome back</h1>
        <p class="muted">Sign in to manage inventory, customers, staff, and sales.</p>
        <?php if ($message = session('error')): ?><div class="alert error"><?= esc($message) ?></div><?php endif; ?>
        <?php if (validation_errors()): ?><div class="alert error"><?= validation_list_errors() ?></div><?php endif; ?>
        <form method="post" action="<?= base_url('login') ?>" class="stack">
            <?= csrf_field() ?>
            <label>Username<input name="username" value="<?= old('username') ?>" autocomplete="username" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button primary" type="submit">Sign in</button>
        </form>
        <p class="small muted">An administrator must create the first staff account using the documented CLI command.</p>
    </div>
</section>
<?= $this->endSection() ?>
