<?php

/**
 * DeliveryUI: User Interface layer.
 * Feature owner: P5 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class DeliveryUI
{
    private UserPermission $permission;
    private DeliveryMgmt $deliveries;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, DeliveryMgmt.
     */
    public function __construct(UserPermission $permission, DeliveryMgmt $deliveries)
    {
        $this->permission = $permission;
        $this->deliveries = $deliveries;
    }

    /**
     * Graded SRS SR-7.2 and SR-7.3: owner dispatch list, assignments and handovers.
     */
    public function showAwaitingAssignment(): void
    {
        $owner = $this->permission->requireRole([UserRole::BusinessOwner]);
        $date = $_GET['date'] ?? today();
        $parsed = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            http_response_code(400);
            render_header('Invalid dispatch date', $owner);
            echo '<p>Please select a valid dispatch date.</p><a href="deliveries.php">Delivery dispatch</a>';
            render_footer();
            return;
        }

        $_SESSION['dispatch_token'] ??= bin2hex(random_bytes(32));
        $token = $_SESSION['dispatch_token'];
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order = $_POST['order'] ?? null;
            $worker = $_POST['worker'] ?? null;
            try {
                if (!is_string($_POST['token'] ?? null) || !hash_equals($token, $_POST['token'])) {
                    throw new DomainException('Refresh the page and try again.');
                }
                if (!is_string($order) || !is_string($worker) || $worker === '') {
                    throw new DomainException('Select an order and bearer.');
                }
                switch ($_POST['action'] ?? '') {
                    case 'assign':
                        $this->deliveries->assignBearer($order, $worker, $date);
                        break;
                    case 'handover':
                        $this->deliveries->recordHandover($order, $date, $worker);
                        break;
                    default:
                        throw new DomainException('Choose a valid dispatch action.');
                }
                redirect('deliveries.php?date=' . $date);
            } catch (DomainException $exception) {
                http_response_code(400);
                $error = $exception->getMessage();
            }
        }
        $orders = $this->deliveries->dispatchList($date);
        $workers = $this->deliveries->getDeliveryWorkers();
        $handovers = $this->deliveries->handovers($date);
        render_header('Delivery dispatch', $owner);
        require __DIR__ . '/../../templates/dispatch.php';
        render_footer();
    }

    /**
     * SR-5.4, SR-5.5: if needsSameDayWarning() and the user hasn't confirmed, showSameDayWarning();
     * otherwise createDelivery().
     * @param string $deliveryDate  date as Y-m-d
     */
    public function submitAssignment(string $orderId, string $workerId, string $deliveryDate): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::submitAssignment() is not built yet.');
    }

    /**
     * SR-5.5: warn that it's after 10:00 AM; let the user proceed or pick another date.
     */
    public function showSameDayWarning(): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::showSameDayWarning() is not built yet.');
    }

    /**
     * SR-6.1: today's deliveries for the signed-in delivery worker (office staff may see all).
     */
    public function showTodaysDeliveries(): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::showTodaysDeliveries() is not built yet.');
    }

    /**
     * SR-6.2, SR-6.3: DeliveryMgmt::completeDelivery() with the signed-in user.
     */
    public function submitCompleted(string $deliveryId): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::submitCompleted() is not built yet.');
    }

    /**
     * SR-6.2, SR-6.3: DeliveryMgmt::failDelivery() with the signed-in user.
     */
    public function submitFailed(string $deliveryId): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::submitFailed() is not built yet.');
    }
}
