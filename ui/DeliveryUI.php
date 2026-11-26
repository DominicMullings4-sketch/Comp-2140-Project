<?php

/**
 * DeliveryUI: User Interface layer. The screens for Feature 5 (Confirm Delivery).
 * Owner: Feature 5. Copy the pattern from CustomerUI.php.
         -> submitAssignment()
 */
class DeliveryUI
{
    private DeliveryService $delivery;

    public function __construct()
    {
        $this->delivery = new DeliveryService();
    }

    /**
     * 5.1 - 5.5: orders due on a date, each with its status, assigned bearer, and a bearer dropdown.
     */
    public function showDeliveries(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // Which day? deliveries.php?date=2026-11-26 shows that day; no date means today.
        $date = $_GET['date'] ?? date('Y-m-d');

        $this->delivery->refreshStatuses();                  // 5.6 first, so the statuses below are up to date
        $orders = $this->delivery->getOrdersForDate($date);  // 5.1, 5.2: the orders due on $date
        $bearers = $this->delivery->getBearers();            // 5.5: every bearer, for the dropdowns

        $formAction = 'deliveries.php?date=' . urlencode($date);

        // The date written out in full for the banner
        $dateInWords = date('l, j F Y', strtotime($date));

        // The three totals in the banner. Count each order once, in the group it belongs to.
        $needBearer = 0;   // no bearer yet
        $assigned = 0;     // bearer chosen, not finished yet
        $completed = 0;    // Delivered or Failed
        foreach ($orders as $o) {
            if ($o->status === Order::STATUS_DELIVERED || $o->status === Order::STATUS_FAILED) {
                $completed++;
            } elseif ($o->bearerId === null) {
                $needBearer++;
            } else {
                $assigned++;
            }
        }

        // The number beside "Orders due": only orders still to be delivered (Pending or Out for delivery).
        // Delivered and Failed orders are finished, so they are not counted.
        $stillToDeliver = $needBearer + $assigned;

        render_header('Deliveries');
        ?>
        <section class="deliveries-banner">
            <p class="banner-date"><?= h($dateInWords) ?></p>
            <h1>Deliveries</h1>
            <div class="banner-totals">
                <div><span class="total-number"><?= $needBearer ?></span> Need a bearer</div>
                <div><span class="total-number"><?= $assigned ?></span> Assigned</div>
                <div><span class="total-number"><?= $completed ?></span> Completed</div>
            </div>
        </section>

        <?php if (count($errors) > 0): ?>
            <ul class="flash error">
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="deliveries-layout">

            <div class="order-cards">
                <h2>Orders due <span class="count"><?= $stillToDeliver ?></span></h2>

                <?php if (count($orders) === 0): ?>
                    <p class="card">No orders are due for delivery on this date.</p>
                <?php endif; ?>

                <?php foreach ($orders as $o): ?>
                    <?php
                    if ($o->status === Order::STATUS_DELIVERED) {
                        $statusClass = 'status-delivered';
                    } elseif ($o->status === Order::STATUS_FAILED) {
                        $statusClass = 'status-failed';
                    } elseif ($o->status === Order::STATUS_OUT_FOR_DELIVERY) {
                        $statusClass = 'status-out';
                    } else {
                        $statusClass = 'status-pending';
                    }
                    ?>
                    <section class="order-card">
                        <div class="order-card-top">
                            <span class="order-number">Order #<?= h($o->id) ?></span>
                            <span class="status-pill <?= $statusClass ?>"><?= h($o->status) ?></span>
                        </div>

                        <h3><?= h($o->customerName) ?></h3>

                        <div class="order-card-details">
                            <div>
                                <span class="detail-label">Ordered at</span>
                                <?= h($o->createdAt) ?>
                            </div>
                            <div>
                                <span class="detail-label">Bearer</span>
                                <?= $o->bearerUsername === null ? 'Not assigned' : h($o->bearerUsername) ?>
                            </div>
                        </div>

                        <div class="order-card-actions">
                            <?php if ($o->status === Order::STATUS_DELIVERED || $o->status === Order::STATUS_FAILED): ?>
                                <p class="done-note">Completed: no changes needed.</p>
                            <?php else: ?>
                                <!-- One small form per order: which order (hidden) + which bearer (dropdown). -->
                                <form method="post" action="<?= h($formAction) ?>" class="assign-row">
                                    <input type="hidden" name="order_id" value="<?= h($o->id) ?>">
                                    <select name="bearer_id" aria-label="Bearer for order <?= h($o->id) ?>" required>
                                        <option value="">Choose a bearer</option>
                                        <?php foreach ($bearers as $b): ?>
                                            <option value="<?= h($b->id) ?>" <?= $b->id === $o->bearerId ? 'selected' : '' ?>>
                                                <?= h($b->name) ?> (<?= h($b->username) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"><?= $o->bearerId === null ? 'Assign' : 'Reassign' ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <aside class="card date-panel">
                <h2>Delivery date</h2>
                <!-- method="get" only reloads the page as deliveries.php?date=... Nothing is saved. -->
                <form method="get" action="deliveries.php">
                    <label for="date">Show orders due on</label>
                    <input id="date" name="date" type="date" value="<?= h($date) ?>" required>
                    <button type="submit">Show</button>
                    <a class="button secondary" href="deliveries.php">Today</a>
                </form>
            </aside>

        </div>
        <?php
        render_footer();
    }

    /**
     * 5.5: the Assign button was pressed for one order.
     */
    public function submitAssignment(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // Hand the two posted values to the logic layer, which checks the bearer and saves.
        $errors = $this->delivery->assignBearer((int) ($post['order_id'] ?? 0), (int) ($post['bearer_id'] ?? 0));

        // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't submit it twice.
        // Go back to the same day the staff member was looking at.
        if (count($errors) === 0) {
            set_flash('Bearer assigned.');
            redirect('deliveries.php?date=' . urlencode($_GET['date'] ?? date('Y-m-d')));
        }

        // Not saved: show the page again with the error messages.
        $this->showDeliveries($errors);
    }
}
