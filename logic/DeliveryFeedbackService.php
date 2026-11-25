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
     * SQL: SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, c.delivery_address AS customer_address
     *      FROM orders o JOIN customers c ON c.id = o.customer_id
     *      WHERE o.bearer_id = :bearer AND o.delivery_date = :today
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
     * SQL: UPDATE orders SET status = :status, delivery_comment = :comment WHERE id = :id AND bearer_id = :bearer
     * @return string[] error messages (empty = saved)
     */
    public function updateDelivery(int $orderId, int $bearerId, string $status, string $comment): array
    {
        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackService::updateDelivery() is not built yet (F6: 6.2, 6.3).');
    }

    /**
     * 6.4, 6.5: every delivery with its status and comment; only one status if a filter is chosen.
     * Add "WHERE o.status = :status" only when $statusFilter isn't null.
     * @return Order[]
     */
    public function getDeliveries(?string $statusFilter): array
    {
        // TODO (Feature 6)
        throw new Exception('DeliveryFeedbackService::getDeliveries() is not built yet (F6: 6.4, 6.5).');
    }
}
