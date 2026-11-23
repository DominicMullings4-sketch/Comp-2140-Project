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
     * SR-5.1, SR-5.3: orders awaiting assignment, each with a worker dropdown and a date box.
     */
    public function showAwaitingAssignment(): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryUI::showAwaitingAssignment() is not built yet.');
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
