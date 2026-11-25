<?php

/**
 * DataAccess: Data Management layer. The ONE class that talks to MySQL.
 * Every service creates one in its constructor and runs its SQL through it:
 *
 *     $this->db = new DataAccess();
 *
 *     // Reading many rows
 *     $rows = $this->db->select('SELECT * FROM customers ORDER BY name', []);
 *
 *     // Reading one row (null if not found)
 *     $row = $this->db->selectOne('SELECT * FROM customers WHERE id = :id', ['id' => $id]);
 *
 *     // INSERT or UPDATE
 *     $this->db->execute('UPDATE products SET quantity = :qty WHERE id = :id', ['qty' => 5, 'id' => 3]);
 *     $newId = $this->db->lastInsertId();   // after an INSERT
 *
 * Always use :name placeholders and pass the values in the array.
 * Never paste form input into the SQL text (SQL injection).
 *
 * Saving to more than one table at once? Use a TRANSACTION, so it's all-or-nothing:
 *     $this->db->beginTransaction();
 *     try {
 *         ... several execute() calls ...
 *         $this->db->commit();       // everything worked: keep it all
 *     } catch (Exception $e) {
 *         $this->db->rollback();     // something failed: undo it all
 *         throw $e;
 *     }
 */
class DataAccess
{
    // One connection shared by every DataAccess object, so a transaction started
    // in one service still covers SQL run by another during the same page.
    private static ?PDO $connection = null;

    public function __construct()
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            self::$connection = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // a failed SQL statement shows a clear error
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // rows come back as ['column' => value]
            ]);
        }
    }

    /** Runs a SELECT and returns every row (an empty list if none). */
    public function select(string $sql, array $params): array
    {
        $statement = self::$connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** Runs a SELECT and returns the first row, or null if there isn't one. */
    public function selectOne(string $sql, array $params): ?array
    {
        $statement = self::$connection->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    /** Runs an INSERT or UPDATE and returns how many rows changed. */
    public function execute(string $sql, array $params): int
    {
        $statement = self::$connection->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    /** The id MySQL gave the row you just INSERTed. */
    public function lastInsertId(): int
    {
        return (int) self::$connection->lastInsertId();
    }

    public function beginTransaction(): void
    {
        self::$connection->beginTransaction();
    }

    public function commit(): void
    {
        self::$connection->commit();
    }

    public function rollback(): void
    {
        if (self::$connection->inTransaction()) {
            self::$connection->rollBack();
        }
    }
}
