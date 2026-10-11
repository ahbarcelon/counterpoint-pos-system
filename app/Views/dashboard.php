<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="dashboard-hero">
    <div class="hero-copy">
        <p class="hero-eyebrow"><span class="status-dot"></span> YOUR STORE AT A GLANCE</p>
        <h1>Every sale starts<br>with a better view.</h1>
        <p>Welcome back, <?= esc(session('full_name')) ?>. Keep your PC inventory, customers, and counter activity moving together.</p>
        <a class="button primary hero-button" href="<?= base_url('sales/new') ?>">Record a sale <span aria-hidden="true">↗</span></a>
    </div>
    <div class="hero-art" aria-hidden="true"><div class="art-ring ring-one"></div><div class="art-ring ring-two"></div><div class="art-card"><span>CP</span><small>POWERING<br>YOUR COUNTER</small></div><div class="art-spark spark-one"></div><div class="art-spark spark-two"></div></div>
</section>

<div class="section-heading"><div><p class="eyebrow">THE NUMBERS</p><h2>Store pulse</h2></div><span class="section-note">Live totals from your database</span></div>
<div class="stats-grid">
    <div class="stat"><span class="stat-icon blue">▤</span><span class="stat-label">Active products</span><strong><?= esc($productCount) ?></strong><small>Ready to sell</small></div>
    <div class="stat"><span class="stat-icon purple">◎</span><span class="stat-label">Customers</span><strong><?= esc($customerCount) ?></strong><small>In your directory</small></div>
    <div class="stat"><span class="stat-icon amber">◇</span><span class="stat-label">Staff accounts</span><strong><?= esc($staffCount) ?></strong><small>On your team</small></div>
    <div class="stat accent"><span class="stat-icon mint">↗</span><span class="stat-label">Total sales</span><strong><?= esc($saleCount) ?></strong><small>Transactions recorded</small></div>
</div>

<section class="panel"><div class="panel-heading"><div><p class="eyebrow">LATEST ACTIVITY</p><h2>Recent transactions</h2><p class="muted">Your newest sales, all in one place.</p></div><a class="text-link" href="<?= base_url('sales') ?>">View all sales <span aria-hidden="true">↗</span></a></div>
<?php if (! $recentSales): ?><p class="empty">No sales yet. Your first transaction will appear here.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Product</th><th>Customer</th><th>Staff</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead><tbody><?php foreach ($recentSales as $sale): ?><tr><td class="cell-strong"><?= esc($sale['product_name']) ?></td><td><?= esc($sale['customer_name']) ?></td><td><?= esc($sale['staff_name']) ?></td><td><?= esc($sale['quantity']) ?></td><td class="money">₱<?= esc($sale['total_price']) ?></td><td><?= esc(date('M j, Y g:i A', strtotime($sale['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?= $this->endSection() ?>
