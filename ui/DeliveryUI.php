<?php

/**
 * DeliveryUI: User Interface layer. The screens for Feature 5 (Confirm Delivery).
 * Owner: Feature 5. Copy the pattern from CustomerUI.php.
 *
 * deliveries.php                 -> showDeliveries()   (today's date)
 * deliveries.php?date=2026-11-26 -> showDeliveries()   (another date)
 * form submitted (POST)          -> submitAssignment()
 */
class DeliveryUI
{
    private DeliveryService $delivery;

    public function __construct()
    {
        $this->delivery = new DeliveryService();
    }

    /**
     * 5.1 - 5.6: orders due on a date, each with its status, assigned bearer, and a bearer dropdown
     * ($this->delivery->getBearers()) + Assign button.
     * Call $this->delivery->refreshStatuses() first (5.6), then $this->delivery->getOrdersForDate($date).
     */
    public function showDeliveries(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // Which day? deliveries.php?date=2026-11-26 shows that day; no date means today.
        $date = $_GET['date'] ?? date('Y-m-d');

        $this->delivery->refreshStatuses();                  // 5.6 first, so the statuses below are up to date
        $orders = $this->delivery->getOrdersForDate($date);  // 5.1, 5.2: the orders due on $date
        $bearers = $this->delivery->getBearers();            // 5.5: every bearer, for the dropdowns

        // The Assign forms post back with the same date, so the page stays on that day afterwards.
        $formAction = 'deliveries.php?date=' . urlencode($date);

        render_header('Deliveries');
        ?>
        <h1>Deliveries</h1>

        <section class="card">
            <!-- method="get" only reloads the page as deliveries.php?date=... Nothing is saved. -->
            <form method="get" action="deliveries.php">
                <label for="date">Delivery date</label>
                <input id="date" name="date" type="date" value="<?= h($date) ?>" required>
                <button type="submit">Show</button>
                <a class="button secondary" href="deliveries.php">Today</a>
            </form>
        </section>

        <section class="card">
            <h2>Orders Due: <?= h($date) ?></h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($orders) === 0): ?>
                <p>No orders are due for delivery on this date.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Ordered at</th>
                        <th>Status</th>
                        <th>Bearer</th>
                        <th>Assign bearer</th>
                    </tr>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= h($o->id) ?></td>
                            <td><?= h($o->customerName) ?></td>
                            <td><?= h($o->createdAt) ?></td>
                            <td><?= h($o->status) ?></td>
                            <td><?= $o->bearerUsername === null ? 'Not assigned' : h($o->bearerUsername) ?></td>
                            <td>
                                <?php if ($o->status === Order::STATUS_DELIVERED || $o->status === Order::STATUS_FAILED): ?>
                                    Completed
                                <?php else: ?>
                                    <!-- One small form per order: which order (hidden) + which bearer (dropdown). -->
                                    <form method="post" action="<?= h($formAction) ?>">
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * 5.5: the Assign button was pressed for one order.
     *   $errors = $this->delivery->assignBearer((int) $post['order_id'], (int) $post['bearer_id']);
     *   no errors -> set_flash('Bearer assigned.'); redirect('deliveries.php');
     *   errors    -> $this->showDeliveries($errors);
     */
    public function submitAssignment(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // Hand the two posted values to the logic layer, which checks the bearer and saves.
        // (int) turns the posted text into numbers; a missing value becomes 0 (no user or order has id 0).
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
