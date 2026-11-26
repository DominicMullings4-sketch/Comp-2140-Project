<?php

/**
 * DeliveryFeedbackUI: User Interface layer. The screens for Feature 6 (Provide Delivery Feedback).
 * Owner: Feature 6. Copy the pattern from CustomerUI.php.
 *
 * Bearer side:  my_deliveries.php        -> showMyDeliveries() / submitFeedback()   (6.1 - 6.3)
 * Staff side:   delivery_feedback.php    -> showFeedbackReview() / submitStaffFeedback()   (6.2 - 6.5)
 *               delivery_feedback.php?status=Failed  (filter)
 */
class DeliveryFeedbackUI
{
    private DeliveryFeedbackService $feedback;

    public function __construct()
    {
        $this->feedback = new DeliveryFeedbackService();
    }

    /**
     * 6.1: the signed-in bearer's deliveries for today: order number, customer name, phone, address,
     * current status, and a small form per delivery: status dropdown (Delivered / Failed) + comment + Save.
     */
    public function showMyDeliveries(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_BEARER);  

        $deliveries = $this->feedback->getTodaysDeliveries((int) AccessControl::currentUserId());  

        // The only two outcomes a bearer can record (6.2).
        $outcomes = [Order::STATUS_DELIVERED, Order::STATUS_FAILED];

        render_header('My Deliveries');
        ?>
        <h1>My Deliveries</h1>

        <section class="card">
            <h2>Today, <?= h(date('Y-m-d')) ?></h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($deliveries) === 0): ?>
                <p>You have no deliveries assigned for today.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Delivery address</th>
                        <th>Status</th>
                        <th>Record outcome</th>
                    </tr>
                    <?php foreach ($deliveries as $d): ?>
                        <tr>
                            <td><?= h($d->id) ?></td>
                            <td><?= h($d->customerName) ?></td>
                            <td><?= h($d->customerPhone) ?></td>
                            <td><?= h($d->customerAddress) ?></td>
                            <td><?= h($d->status) ?></td>
                            <td>
                                <!-- One small form per delivery: which order (hidden) + outcome + comment. -->
                                <form method="post" action="my_deliveries.php">
                                    <input type="hidden" name="order_id" value="<?= h($d->id) ?>">

                                    <select name="status" aria-label="Outcome for order <?= h($d->id) ?>" required>
                                        <option value="">Choose an outcome</option>
                                        <?php foreach ($outcomes as $outcome): ?>
                                            <option value="<?= h($outcome) ?>" <?= $d->status === $outcome ? 'selected' : '' ?>>
                                                <?= h($outcome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <textarea name="comment" rows="2" aria-label="Comment for order <?= h($d->id) ?>"
                                              placeholder="Comment (optional)"><?= h($d->deliveryComment ?? '') ?></textarea>

                                    <button type="submit"><?= in_array($d->status, $outcomes, true) ? 'Update' : 'Save' ?></button>
                                </form>
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
     * 6.2, 6.3: a bearer saved a status + comment for one delivery.
     */
    public function submitFeedback(array $post): void
    {
        AccessControl::requireRole(User::ROLE_BEARER); 

        // Hand the posted values to the logic layer, which checks them and saves.
        // The bearer id comes from the session (who is signed in), NEVER from the form.
        $errors = $this->feedback->updateDelivery(
            (int) ($post['order_id'] ?? 0),
            (int) AccessControl::currentUserId(),
            $post['status'] ?? '',
            trim($post['comment'] ?? '')
        );

        // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't submit it twice.
        if (count($errors) === 0) {
            set_flash('Delivery updated.');
            redirect('my_deliveries.php');
        }

        // Not saved: show the page again with the error messages.
        $this->showMyDeliveries($errors);
    }

    /**
     * Staff reviews every delivery's status and comment, with a status filter
     * (a dropdown of Order::ALL_STATUSES that reloads the page as ?status=...).
     */
    public function showFeedbackReview(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // 6.5: which status to show? delivery_feedback.php?status=Failed, or every delivery if none chosen.
        $statusFilter = $_GET['status'] ?? '';

        // The update forms post back with the same filter, so the page stays on it afterwards.
        $formAction = 'delivery_feedback.php?status=' . urlencode($statusFilter);
        $outcomes = [Order::STATUS_DELIVERED, Order::STATUS_FAILED];

        $deliveries = $this->feedback->getDeliveries($statusFilter);         // aoolies filter by status

        render_header('Delivery Feedback');
        ?>
        <h1>Delivery Feedback</h1>

        <section class="card">
            <form method="get" action="delivery_feedback.php">
                <label for="status">Show deliveries with status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (Order::ALL_STATUSES as $status): ?>
                        <option value="<?= h($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                            <?= h($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filter</button>
            </form>
        </section>

        <section class="card">
            <h2><?= $statusFilter === '' ? 'All deliveries' : h($statusFilter) . ' deliveries' ?> (<?= count($deliveries) ?>)</h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($deliveries) === 0): ?>
                <p>No deliveries match this filter.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Order #</th>
                        <th>Delivery date</th>
                        <th>Customer</th>
                        <th>Bearer</th>
                        <th>Status</th>
                        <th>Comment</th>
                        <th>Record outcome</th>
                    </tr>
                    <?php foreach ($deliveries as $d): ?>
                        <tr>
                            <td><?= h($d->id) ?></td>
                            <td><?= h($d->deliveryDate) ?></td>
                            <td><?= h($d->customerName) ?></td>
                            <td><?= $d->bearerUsername === null ? 'Not assigned' : h($d->bearerUsername) ?></td>
                            <td><?= h($d->status) ?></td>
                            <!-- nl2br keeps the line breaks the bearer typed; h() runs first so it stays safe. -->
                            <td><?= $d->deliveryComment === null ? '' : nl2br(h($d->deliveryComment)) ?></td>
                            <td>
                                <?php if ($d->bearerId === null): ?>
                                    Assign a bearer first
                                <?php else: ?>
                                    <!-- Same form as the bearer page: which order (hidden) + outcome + comment. -->
                                    <form method="post" action="<?= h($formAction) ?>">
                                        <input type="hidden" name="order_id" value="<?= h($d->id) ?>">

                                        <select name="status" aria-label="Outcome for order <?= h($d->id) ?>" required>
                                            <option value="">Choose an outcome</option>
                                            <?php foreach ($outcomes as $outcome): ?>
                                                <option value="<?= h($outcome) ?>" <?= $d->status === $outcome ? 'selected' : '' ?>>
                                                    <?= h($outcome) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <textarea name="comment" rows="2" aria-label="Comment for order <?= h($d->id) ?>"
                                                  placeholder="Comment (optional)"><?= h($d->deliveryComment ?? '') ?></textarea>

                                        <button type="submit"><?= in_array($d->status, $outcomes, true) ? 'Update' : 'Save' ?></button>
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
     * 6.2, 6.3 (staff side): office staff recorded an outcome from the Delivery Feedback page,
     * for example when a bearer phones it in. The logic layer checks the user's role and the delivery.
     */
    public function submitStaffFeedback(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        // The user id comes from the session (who is signed in), NEVER from the form.
        $errors = $this->feedback->updateDelivery(
            (int) ($post['order_id'] ?? 0),
            (int) AccessControl::currentUserId(),
            $post['status'] ?? '',
            trim($post['comment'] ?? '')
        );

        // Saved? Redirect (Post -> Redirect -> Get) back to the same filter.
        if (count($errors) === 0) {
            set_flash('Delivery updated.');
            redirect('delivery_feedback.php?status=' . urlencode($_GET['status'] ?? ''));
        }

        // Not saved: show the page again with the error messages.
        $this->showFeedbackReview($errors);
    }
}
