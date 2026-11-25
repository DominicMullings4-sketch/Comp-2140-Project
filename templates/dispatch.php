<?php
$assignedCount = count(array_filter($orders, static fn($order) => $order['bearer_id'] !== null));
?>
<header class="dispatch-banner">
    <div>
        <p class="banner-date"><time datetime="<?= e($date) ?>"><?= e($parsed->format('l, j F Y')) ?></time></p>
        <h1>Delivery dispatch</h1>
    </div>
    <div class="route-print" aria-hidden="true"><i></i><i></i><i></i></div>
    <dl class="dispatch-totals">
        <div><dt>Awaiting bearer</dt><dd><?= count($orders) - $assignedCount ?></dd></div>
        <div><dt>Assigned</dt><dd><?= $assignedCount ?></dd></div>
        <div><dt>Handed over</dt><dd><?= count($handovers) ?></dd></div>
    </dl>
</header>
<?php if ($error !== null): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<div class="dispatch-layout">
    <aside class="date-panel panel">
        <h2>Dispatch date</h2>
        <form method="get">
            <label class="sr-only" for="dispatch-date">Dispatch date</label>
            <input id="dispatch-date" type="date" name="date" value="<?= e($date) ?>" required>
            <button>Show orders <span aria-hidden="true">→</span></button>
        </form>
    </aside>
    <div class="order-queue">
        <h2>Confirmed orders <span class="count"><?= count($orders) ?></span></h2>
        <?php if (!$orders): ?><p class="panel empty">No confirmed orders are awaiting dispatch for this date.</p><?php endif; ?>
        <?php foreach ($orders as $order): ?>
            <section>
                <div class="ticket-top">
                    <span class="order-id"><?= e($order['order_id']) ?></span>
                    <span class="status <?= $order['bearer_id'] === null ? 'waiting' : 'assigned' ?>">
                        <?= $order['bearer_id'] === null ? 'Awaiting bearer' : 'Bearer assigned' ?>
                    </span>
                </div>
                <h3><?= e($order['name']) ?></h3>
                <?php if (trim($order['phone']) === '' || trim($order['address']) === ''): ?>
                    <p class="error">Incomplete delivery information</p>
                <?php endif; ?>
                <dl class="contact-grid">
                    <div><dt>Delivery address</dt><dd><?= e(trim($order['address']) ?: 'Missing') ?></dd></div>
                    <div><dt>Contact number</dt><dd><?= e(trim($order['phone']) ?: 'Missing') ?></dd></div>
                </dl>
                <div class="items-row">
                    <div><h4>Products</h4>
                        <ul><?php foreach ($order['lines'] as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul>
                    </div>
                    <div class="total"><h4>Order total</h4><strong><?= money($order['total']) ?></strong></div>
                </div>
                <div class="ticket-actions">
                    <form method="post" class="assign-form">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="order" value="<?= e($order['order_id']) ?>">
                        <label>Bearer
                            <select name="worker" required>
                                <option value="">Select a bearer</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?= e($worker->getId()) ?>" <?= $worker->getId() === $order['bearer_id'] ? 'selected' : '' ?>>
                                        <?= e($worker->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button name="action" value="assign">Save assignment</button>
                    </form>
                    <?php if ($order['bearer_id'] !== null): ?>
                        <form method="post" class="handover-form">
                            <input type="hidden" name="token" value="<?= e($token) ?>">
                            <input type="hidden" name="order" value="<?= e($order['order_id']) ?>">
                            <input type="hidden" name="worker" value="<?= e($order['bearer_id']) ?>">
                            <button name="action" value="handover">Record handover to <?= e($order['bearer']) ?> →</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <aside class="history panel">
        <h2>Recorded handovers</h2>
        <?php if (!$handovers): ?><p class="empty">No handovers recorded for this date.</p><?php else: ?>
            <ul><?php foreach (array_reverse($handovers) as $handover): ?>
                <li>
                    <span class="order-id"><?= e($handover['order_id']) ?></span>
                    <strong><?= e($handover['name']) ?></strong>
                    <span class="status assigned">Out for Delivery</span>
                    <time><?= e($handover['handed_over_at']) ?></time>
                </li>
            <?php endforeach; ?></ul>
        <?php endif; ?>
    </aside>
</div>
