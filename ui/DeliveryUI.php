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
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 5): replace this placeholder with the list.
        render_header('Deliveries');
        ?>
        <h1>Deliveries</h1>
        <section class="card todo">
            <p><strong>Not built yet.</strong> This page will let staff:</p>
            <ul>
                <li>see each order's delivery date (5.1, 5.3, 5.4: same day before 10:00 AM, next day after) and status (5.2)</li>
                <li>5.5 assign a bearer to an order</li>
                <li>5.6 have assigned orders change to "Out for delivery" on their delivery date</li>
            </ul>
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
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 5)
        throw new Exception('DeliveryUI::submitAssignment() is not built yet (F5: 5.5).');
    }
}
