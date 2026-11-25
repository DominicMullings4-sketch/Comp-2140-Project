<?php

/**
 * DeliveryFeedbackService: Application Logic layer. Business rules AND SQL for Feature 6 (Provide Delivery Feedback).
 * Owner: Feature 6. Copy the style of CustomerService.php.
 */
class DeliveryFeedbackService
{
    private DataAccess $db;
    private UserService $users;

    public function __construct()
    {
        $this->db = new DataAccess();
        $this->users = new UserService();   // to look up the role of whoever records an outcome
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
        // Part 1 - the SQL: this bearer's deliveries due today, joined to each order and its customer
        // (the customer's phone and address are what the bearer needs to make the delivery).
        $rows = $this->db->select(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, c.delivery_address AS customer_address,
                    d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
             FROM deliveries d
             JOIN orders o ON o.order_id = d.order_id
             JOIN customers c ON c.customer_id = o.customer_id
             WHERE d.worker_id = :bearer AND d.delivery_date = :today
             ORDER BY o.order_id',
            ['bearer' => $bearerId, 'today' => date('Y-m-d')]
        );

        $deliveries = [];
        foreach ($rows as $row) {
            $deliveries[] = Order::fromRow($row);
        }
        return $deliveries;
    }

    /**
     * 6.2, 6.3: marks a delivery 'Delivered' or 'Failed' and adds a comment.
     * Bearers AND office staff can record an outcome:
     *   - a bearer only for a delivery assigned to them (so nobody can change someone else's delivery)
     *   - office staff for any delivery that has a bearer assigned (e.g. when a bearer phones it in)
     * The role is looked up from the database here, not taken from the UI.
     * SQL: UPDATE deliveries
     *      SET status = :status, comment = :comment, outcome_recorded_at = NOW(), outcome_recorded_by = :recordedBy
     *      WHERE order_id = :id AND worker_id IS NOT NULL   (+ AND worker_id = :bearer for a bearer)
     * @return string[] error messages (empty = saved)
     */
    public function updateDelivery(int $orderId, int $userId, string $status, string $comment): array
    {
        $errors = [];

        // Check 1 (6.2): only one of the two outcomes can be recorded.
        if (!in_array($status, [Order::STATUS_DELIVERED, Order::STATUS_FAILED], true)) {
            $errors[] = 'Please choose Delivered or Failed.';
        }

        // Who is recording it? Staff and bearers have different limits (Check 2).
        $user = $this->users->getUser($userId);
        $isStaff = $user !== null && $user->role === User::ROLE_STAFF;
        $isBearer = $user !== null && $user->role === User::ROLE_BEARER;

        // Check 2: which deliveries this user may update.
        // Both: the delivery must exist and have a bearer assigned.
        // A bearer: it must also be assigned to THEM. (Built in pieces, like getDeliveries().)
        $where = 'WHERE order_id = :id AND worker_id IS NOT NULL';
        $whereParams = ['id' => $orderId];
        if (!$isStaff) {
            $where .= ' AND worker_id = :bearer';
            $whereParams['bearer'] = $userId;
        }

        if (!$isStaff && !$isBearer) {
            $errors[] = 'You are not allowed to record delivery outcomes.';
        } else {
            $matches = $this->db->select('SELECT delivery_id FROM deliveries ' . $where, $whereParams);
            if (count($matches) === 0) {
                $errors[] = $isStaff
                    ? 'That delivery was not found or has no bearer assigned yet.'
                    : 'That delivery is not assigned to you.';
            }
        }

        // If any check failed, stop here. Nothing is saved.
        if (count($errors) > 0) {
            return $errors;
        }

        // Save the outcome (6.2), the comment (6.3), and when and by whom it was recorded.
        // The same WHERE as Check 2 is a safety net: a bearer still can't touch another bearer's delivery.
        // An empty comment is saved as NULL ("no comment") rather than an empty string.
        $this->db->execute(
            'UPDATE deliveries
             SET status = :status, comment = :comment, outcome_recorded_at = NOW(), outcome_recorded_by = :recordedBy ' . $where,
            $whereParams + [
                'status'     => $status,
                'comment'    => $comment === '' ? null : $comment,
                'recordedBy' => $userId,
            ]
        );

        return $errors;   // empty list = saved
    }

    /**
     * 6.4, 6.5: every delivery with its status and comment; only one status if a filter is chosen.
     * Read FROM deliveries d JOIN orders o ON o.order_id = d.order_id (as in getTodaysDeliveries).
     * Add "WHERE d.status = :status" only when $statusFilter isn't null.
     * @return Order[]
     */
    public function getDeliveries(?string $statusFilter): array
    {
        // Every delivery, with its customer and its bearer.
        // LEFT JOIN users: deliveries with no bearer yet still appear (bearer_username is just empty).
        $sql = 'SELECT o.*, c.name AS customer_name, u.username AS bearer_username,
                       d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
                FROM deliveries d
                JOIN orders o ON o.order_id = d.order_id
                JOIN customers c ON c.customer_id = o.customer_id
                LEFT JOIN users u ON u.user_id = d.worker_id';
        $params = [];

        // 6.5: only filter when a real status was chosen.
        // "All" (an empty value) or anything that isn't one of the four statuses means "no filter".
        if ($statusFilter !== null && in_array($statusFilter, Order::ALL_STATUSES, true)) {
            $sql .= ' WHERE d.status = :status';
            $params['status'] = $statusFilter;
        }

        // Newest delivery dates first.
        $sql .= ' ORDER BY d.delivery_date DESC, o.order_id DESC';
        $rows = $this->db->select($sql, $params);

        $deliveries = [];
        foreach ($rows as $row) {
            $deliveries[] = Order::fromRow($row);
        }
        return $deliveries;
    }
}
