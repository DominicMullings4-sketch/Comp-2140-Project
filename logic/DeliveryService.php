<?php

/**
 * DeliveryService: Application Logic layer. Business rules AND SQL for Feature 4 (Confirm Delivery).
 * Owner: Feature 4. Copy the style of CustomerService.php.
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
     * 4.1, 4.3, 4.4: the delivery date for an order saved at $savedAt.
     * Before 10:00 AM -> the same day. At or after 10:00 AM -> the next day.
     * Returns the date as 'YYYY-MM-DD'.
     */
    public function calculateDeliveryDate(DateTime $savedAt): string
    {
        // TODO (Feature 5)
        if($savedAt->format('H:i') < '10:00'){
            return $savedAt->format('Y-m-d');
        }

        $nextDay = clone $savedAt; // If not cloned, the original variable will be modified.
        $nextDay-> modify('+1 day');
        return $nextDay->format('Y-m-d');
    }

    /**
     * Returns every order due on a specified date, with customer name and assigned bearer, for the staff deliveries page.
     * @return Order[]
     */
    public function getOrdersForDate(string $date): array
    {
        $rows = $this->db->select('SELECT o.*, c.name AS customer_name, u.username AS bearer_username,
                   d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
            FROM deliveries d
            JOIN orders o ON o.order_id = d.order_id
            JOIN customers c ON c.customer_id = o.customer_id
            LEFT JOIN users u ON u.user_id = d.worker_id
            WHERE d.delivery_date = :date
            ORDER BY o.order_id', ['date' => $date]);

        $orders = [];
        foreach ($rows as $row) {
            $orders[] = Order::fromRow($row);
        }
        return $orders;
    }

    /** Every bearer, for the "assign a bearer" dropdown (4.5).*/
    public function getBearers(): array
    {
        return $this->users->getBearers();
    }

    /**
     * 4.5: assigns a bearer to an order. Check the bearer exists and really has the User::ROLE_BEARER role.
     * The status stays 'Pending' until the delivery date and when a bearer is assigned (4.6).
     * @return string[] error messages (empty = saved)
     */
    public function assignBearer(int $orderId, int $bearerId): array
    {
        $errors = [];

        $bearer = $this->users->getUser($bearerId);                       
        if ($bearer === null || $bearer->role !== User::ROLE_BEARER) {            
            $errors[] = 'Please choose a valid delivery bearer.';
        }

        // If any check failed, stop here. Nothing is saved.
        if (count($errors) > 0) {                                                  
            return $errors;
        }

        // Save the assignment. The status stays 'Pending' until the delivery date (5.6).
        $this->db->execute(
            'UPDATE deliveries SET worker_id = :bearer, assigned_at = NOW() WHERE order_id = :id',
            ['bearer' => $bearerId, 'id' => $orderId]                        
        );

        return $errors;   // empty list = saved
    }

    /**
     * 4.6: on the delivery date, assigned 'Pending' orders become 'Out for delivery' when a bearer is assigned.
     * Called at the top of the delivery pages. One UPDATE:
     */
    public function refreshStatuses(): void
    {
        // Only assigned (worker_id set), still-Pending orders whose delivery date has arrived.
        // The status = Pending check means running this again does nothing (safe on every page load).
        $this->db->execute(
            'UPDATE deliveries SET status = :outForDelivery
             WHERE worker_id IS NOT NULL AND status = :pending AND delivery_date <= :today',
            ['outForDelivery' => Order::STATUS_OUT_FOR_DELIVERY,'pending'=> Order::STATUS_PENDING, 'today'=> date('Y-m-d') ]
        );
    }
}
