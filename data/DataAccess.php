<?php

/**
 * DataAccess: Data Management layer. The ONE class that talks to MySQL.
 * Every service creates an instance in its constructor and runs its SQL queries through it:
 *
 *     $this->db = new DataAccess();
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
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4'; // Uses config file to input variables needed to establish connection. 
            self::$connection = new PDO($dsn, DB_USER, DB_PASSWORD, 
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   
            ]);
        }
    }

    /** Runs a SELECT query and returns every row (an empty list if none). */
    public function select(string $sql, array $params): array
    {
        $statement = self::$connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
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
