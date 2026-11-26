<?php

/**
 * DeliveryUI: User Interface layer for Feature 4 (Confirm Delivery). Staff only.
 *
 *   deliveries.php (GET)   -> showDeliveryList()   SR-4.2: the delivery list for a dispatch date
 *   deliveries.php (POST)  -> submitAction()       SR-4.3: assign a bearer / record the handover
 *
 * This class only shows pages and passes form values on. The rules are in DeliveryService.
 */
class DeliveryUI
{
    private DeliveryService $delivery;

    public function __construct()
    {
        $this->delivery = new DeliveryService();
    }

    /**
     * SR-4.2 / AC-4.2: every order scheduled for the chosen dispatch date, with the customer,
     * contact number, address, products, quantities and total, and a flag on incomplete orders.
     * SR-4.3: each order not yet handed over has an Assign form and a Record handover button.
     */
    public function showDeliveryList(array $errors = []): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        $date = $this->selectedDate();
        $orders = $this->delivery->getDeliveryList($date);
        $bearers = $this->delivery->getBearers();

        // The forms post back with the same date, so the page stays on that day afterwards.
        $formAction = 'deliveries.php?date=' . urlencode($date);

        // How many orders on this list are missing a contact number or address (AC-4.2).
        $incomplete = 0;
        foreach ($orders as $o) {
            if (count($this->delivery->getMissingDetails($o)) > 0) {
                $incomplete++;
            }
        }

        render_header('Deliveries');
        ?>
        <h1>Deliveries</h1>

        <section class="card">
            <form method="get" action="deliveries.php">
                <label for="date">Dispatch date</label>
                <input id="date" name="date" type="date" value="<?= h($date) ?>" required>
                <button type="submit">Show orders</button>
                <a class="button secondary" href="deliveries.php">Today</a>
            </form>
            <p>Orders confirmed by 10:00 a.m. are dispatched the same day. Orders confirmed later are dispatched the next day.</p>
        </section>

        <section class="card">
            <h2>Orders for <?= h(date('l, j F Y', strtotime($date))) ?> (<?= count($orders) ?>)</h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($incomplete > 0): ?>
                <p class="flash error">
                    <?= $incomplete ?> order<?= $incomplete === 1 ? ' is' : 's are' ?> missing delivery information (marked Incomplete below).
                </p>
            <?php endif; ?>

            <?php if (count($orders) === 0): ?>
                <p>No orders are scheduled for dispatch on this date.</p>
            <?php else: ?>
                <!-- The wrapper lets this wide table scroll sideways inside the card in a narrow window,
                     instead of the last column being cut off. -->
                <div class="table-scroll">
                <table>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Contact number</th>
                        <th>Delivery address</th>
                        <th>Products</th>
                        <th class="number">Total</th>
                        <th>Status</th>
                        <th>Bearer / handover</th>
                    </tr>
                    <?php foreach ($orders as $o): ?>
                        <?php $this->showOrderRow($o, $bearers, $formAction); ?>
                    <?php endforeach; ?>
                </table>
                </div>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-4.3: one of the two buttons in an order's row was pressed.
     * action=assign   -> assign or change the bearer
     * action=handover -> record the handover to the assigned bearer (status becomes Out for delivery)
     */
    public function submitAction(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

        $orderId = (int) ($post['order_id'] ?? 0);
        $action = $post['action'] ?? '';

        if ($action === 'assign') {
            $errors = $this->delivery->assignBearer($orderId, (int) ($post['bearer_id'] ?? 0));
            $success = 'Bearer assigned to order #' . $orderId . '.';
        } elseif ($action === 'handover') {
            // Who recorded the handover comes from the session (who is signed in), NEVER from the form.
            $errors = $this->delivery->recordHandover($orderId, (int) AccessControl::currentUserId());
            $success = 'Handover recorded. Order #' . $orderId . ' is out for delivery.';
        } else {
            $errors = ['Unknown action.'];
            $success = '';
        }

        // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't submit it twice.
        if (count($errors) === 0) {
            set_flash($success);
            redirect('deliveries.php?date=' . urlencode($this->selectedDate()));
        }

        // Not saved: show the page again with the error messages.
        $this->showDeliveryList($errors);
    }

    /** One order's row in the delivery list (AC-4.2 details, AC-4.3 actions). */
    private function showOrderRow(Order $o, array $bearers, string $formAction): void
    {
        $missing = $this->delivery->getMissingDetails($o);
        ?>
        <tr>
            <td><a href="orders.php?action=invoice&amp;id=<?= h($o->id) ?>">#<?= h($o->id) ?></a></td>
            <td>
                <?= h($o->customerName) ?>
                <?php if (count($missing) > 0): ?>
                    <br><span class="missing">Incomplete: <?= h(implode('; ', $missing)) ?></span>
                <?php endif; ?>
            </td>
            <td><?= $this->detailCell($o->customerPhone, $this->delivery->phoneProblem($o->customerPhone)) ?></td>
            <td><?= $this->detailCell($o->customerAddress, $this->delivery->addressProblem($o->customerAddress)) ?></td>
            <td>
                <?php foreach ($o->lines as $line): ?>
                    <?= h($line->quantity) ?> &times; <?= h($line->productName) ?><br>
                <?php endforeach; ?>
            </td>
            <td class="number"><?= h(money($o->total())) ?></td>
            <td><?= h($o->status) ?></td>
            <td>
                <?php if ($o->status !== Order::STATUS_PENDING): ?>
                    <?= $o->bearerUsername === null ? 'Not assigned' : h($o->bearerUsername) ?>
                    <?php if ($o->handedOverAt !== null): ?>
                        <br>Handed over <?= h(date('j M Y, g:i a', strtotime($o->handedOverAt))) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Assign (or change) the bearer: which order (hidden) + which bearer (dropdown). -->
                    <form method="post" action="<?= h($formAction) ?>">
                        <input type="hidden" name="order_id" value="<?= h($o->id) ?>">
                        <input type="hidden" name="action" value="assign">
                        <select name="bearer_id" aria-label="Bearer for order <?= h($o->id) ?>" required>
                            <option value="">Choose a bearer</option>
                            <?php foreach ($bearers as $b): ?>
                                <option value="<?= h($b->id) ?>" <?= $b->id === $o->bearerId ? 'selected' : '' ?>>
                                    <?= h($b->name) ?> (<?= h($b->username) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit"><?= $o->bearerId === null ? 'Assign' : 'Change bearer' ?></button>
                    </form>

                    <!-- Record the handover to the assigned bearer. Disabled until a bearer is assigned
                         (the logic layer checks this too, so it can't be skipped). -->
                    <form method="post" action="<?= h($formAction) ?>">
                        <input type="hidden" name="order_id" value="<?= h($o->id) ?>">
                        <input type="hidden" name="action" value="handover">
                        <button type="submit" <?= $o->bearerId === null ? 'disabled title="Assign a bearer first"' : '' ?>>
                            Record handover
                        </button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * A contact number or address cell: the value as normal, the value in red if it can't be used,
     * or a red "Missing" if it is blank.
     */
    private function detailCell(?string $value, ?string $problem): string
    {
        if (trim((string) $value) === '') {
            return '<span class="missing">Missing</span>';
        }
        if ($problem !== null) {
            return '<span class="missing">' . h($value) . '</span>';
        }
        return h($value);
    }

    /** The dispatch date in the address (?date=YYYY-MM-DD), or today if it is missing or not a real date. */
    private function selectedDate(): string
    {
        $date = $_GET['date'] ?? '';
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return date('Y-m-d');
        }
        return $date;
    }
}
