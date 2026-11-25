<?php

/**
 * DeliveryFeedbackUI: User Interface layer. The screens for Feature 6 (Provide Delivery Feedback).
 * Owner: Feature 6. Copy the pattern from CustomerUI.php.
 *
 * Bearer side:  my_deliveries.php        -> showMyDeliveries() / submitFeedback()   (6.1 - 6.3)
 * Staff side:   delivery_feedback.php    -> showFeedbackReview()                    (6.4, 6.5)
 *               delivery_feedback.php?status=Failed  (filter)
 */
class DeliveryFeedbackUI
{
    private DeliveryFeedbackService $feedback;
    private DeliveryService $delivery;

    public function __construct()
    {
        $this->feedback = new DeliveryFeedbackService();
        $this->delivery = new DeliveryService();
    }

    /**
     * 6.1: the signed-in bearer's deliveries for today: order number, customer name, phone, address,
     * current status, and a small form per delivery: status dropdown (Delivered / Failed) + comment + Save.
     * Call $this->delivery->refreshStatuses() first (5.6), then
     * $this->feedback->getTodaysDeliveries(AccessControl::currentUserId()).
     */
    public function showMyDeliveries(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_BEARER);

        // TODO (Feature 6): replace this placeholder with the bearer's list.
        render_header('My Deliveries');
        ?>
        <h1>My Deliveries</h1>
        <section class="card todo">
            <p><strong>Not built yet.</strong> This page will let a bearer:</p>
            <ul>
                <li>6.1 see their assigned deliveries for today: order number, customer name, phone number and delivery address</li>
                <li>6.2 update a delivery's status to "Delivered" or "Failed"</li>
                <li>6.3 add a comment to a delivery</li>
            </ul>
        </section>
        <?php
        render_footer();
    }

    /**
     * 6.2, 6.3: a bearer saved a status + comment for one delivery.
     *   $errors = $this->feedback->updateDelivery((int) $post['order_id'], AccessControl::currentUserId(),
     *                                             $post['status'] ?? '', trim($post['comment'] ?? ''));
     *   no errors -> set_flash('Delivery updated.'); redirect('my_deliveries.php');
     *   errors    -> $this->showMyDeliveries($errors);
     */
    public function submitFeedback(array $post): void
    {
        AccessControl::requireRole(User::ROLE_BEARER);

        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackUI::submitFeedback() is not built yet (F6: 6.2, 6.3).');
    }

    /**
     * 6.4, 6.5: staff review every delivery's status and comment, with a status filter
     * (a dropdown of Order::ALL_STATUSES that reloads the page as ?status=...).
     *   $deliveries = $this->feedback->getDeliveries($_GET['status'] ?? null);
     */
    public function showFeedbackReview(): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 6): replace this placeholder with the filter and table.
        render_header('Delivery Feedback');
        ?>
        <h1>Delivery Feedback</h1>
        <section class="card todo">
            <p><strong>Not built yet.</strong> This page will let staff:</p>
            <ul>
                <li>6.4 review each delivery's status and the bearer's comments</li>
                <li>6.5 filter deliveries by status</li>
            </ul>
        </section>
        <?php
        render_footer();
    }
}
