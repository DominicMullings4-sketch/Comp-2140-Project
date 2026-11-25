<?php

/**
 * DeliveryService: Application Logic layer. Business rules AND SQL for Feature 5 (Confirm Delivery).
 * Owner: Feature 5. Copy the style of CustomerService.php.
 */
class DeliveryService
{
    private DataAccess $db;
    private UserService $users;

    public function __construct()
    {
        $this->db = new DataAccess();
        $this->users = new UserService();
    }

    /**
     * 5.1, 5.3, 5.4: the delivery date for an order saved at $savedAt.
     * Before 10:00 AM -> the same day. At or after 10:00 AM -> the next day.
     * Returns the date as 'YYYY-MM-DD'.
     * Hint: $savedAt->format('H:i') gives the time as text, e.g. '09:45'.
     */
    public function calculateDeliveryDate(DateTime $savedAt): string
    {
        // TODO (Feature 5)
        if($savedAt->format('H:i') < '10:00'){
            return $savedAt->format('Y-m-d');
        }

        $nextDay = clone $savedAt; // If not done, the original variable will be modified.
        $nextDay-> modify('+1 day');
        return $nextDay->format('Y-m-d');
    }

    /**
     * Every order due on a date, with customer name and assigned bearer, for the staff deliveries page.
     * SQL: SELECT o.*, c.name AS customer_name, u.username AS bearer_username,
     *             d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
     *      FROM deliveries d
     *      JOIN orders o ON o.order_id = d.order_id
     *      JOIN customers c ON c.customer_id = o.customer_id
     *      LEFT JOIN users u ON u.user_id = d.worker_id
     *      WHERE d.delivery_date = :date
     * @return Order[]
     */
    public function getOrdersForDate(string $date): array
    {
        // TODO (Feature 5)
        throw new Exception('DeliveryService::getOrdersForDate() is not built yet (F5).');
    }

    /** Every bearer, for the "assign a bearer" dropdown (5.5). Already built. @return User[] */
    public function getBearers(): array
    {
        return $this->users->getBearers();
    }

    /**
     * 5.5: assigns a bearer to an order. Check the bearer exists and really has the User::ROLE_BEARER role.
     * The status stays 'Pending' until the delivery date (5.6).
     * SQL: UPDATE deliveries SET worker_id = :bearer, assigned_at = NOW() WHERE order_id = :id
     * @return string[] error messages (empty = saved)
     */
    public function assignBearer(int $orderId, int $bearerId): array
    {
        // TODO (Feature 5)
        throw new Exception('DeliveryService::assignBearer() is not built yet (F5: 5.5).');
    }

    /**
     * 5.6: on the delivery date, assigned 'Pending' orders become 'Out for delivery'.
     * Called at the top of the delivery pages. One UPDATE:
     * SQL: UPDATE deliveries SET status = 'Out for delivery'
     *      WHERE worker_id IS NOT NULL AND status = 'Pending' AND delivery_date <= :today
     */
    public function refreshStatuses(): void
    {
        // TODO (Feature 5)
        throw new Exception('DeliveryService::refreshStatuses() is not built yet (F5: 5.6).');
    }
}
