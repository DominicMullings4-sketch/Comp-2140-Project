<?php

/**
 * DeliveryFeedbackService: Application Logic layer. Business rules AND SQL for Feature 6 (Provide Delivery Feedback).
 * Owner: Feature 6. Copy the style of CustomerService.php.
 */
class DeliveryFeedbackService
{
    private DataAccess $db;

    public function __construct()
    {
        $this->db = new DataAccess();
    }

    /**
     * 6.1: the signed-in bearer's deliveries for today.
     * SQL: SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, c.delivery_address AS customer_address,
     *             d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
     *      FROM deliveries d
     *      JOIN orders o ON o.order_id = d.order_id
     *      JOIN customers c ON c.customer_id = o.customer_id
     *      WHERE d.worker_id = :bearer AND d.delivery_date = :today
     * @return Order[]
     */
    public function getTodaysDeliveries(int $bearerId): array
    {
        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackService::getTodaysDeliveries() is not built yet (F6: 6.1).');
    }

    /**
     * 6.2, 6.3: the bearer marks a delivery 'Delivered' or 'Failed' and adds a comment.
     * Check: the order is assigned to THIS bearer (so nobody can change someone else's delivery),
     * and the status is only 'Delivered' or 'Failed'.
     * SQL: UPDATE deliveries
     *      SET status = :status, comment = :comment, outcome_recorded_at = NOW(), outcome_recorded_by = :bearer
     *      WHERE order_id = :id AND worker_id = :bearer
     * @return string[] error messages (empty = saved)
     */
    public function updateDelivery(int $orderId, int $bearerId, string $status, string $comment): array
    {
        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackService::updateDelivery() is not built yet (F6: 6.2, 6.3).');
    }

    /**
     * 6.4, 6.5: every delivery with its status and comment; only one status if a filter is chosen.
     * Read FROM deliveries d JOIN orders o ON o.order_id = d.order_id (as in getTodaysDeliveries).
     * Add "WHERE d.status = :status" only when $statusFilter isn't null.
     * @return Order[]
     */
    public function getDeliveries(?string $statusFilter): array
    {
        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackService::getDeliveries() is not built yet (F6: 6.4, 6.5).');
    }
}
