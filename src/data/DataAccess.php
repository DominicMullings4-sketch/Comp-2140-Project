<?php

/**
 * DataAccess: Data Access layer.
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class DataAccess
{
    private PDO $connection;
    private string $encryptionKey;

    /**
     * Connects to the database using the settings in config/config.php (same PDO connection as INFO2180
     * Lab 5).
     */
    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        if (DB_SSL_CA == '') {
            // XAMPP on your laptop
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD);
        } else {
            // Azure only accepts encrypted connections (SC-4), so give PDO the certificate file.
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_SSL_CA => DB_SSL_CA]);
        }

        // Show a clear error message if any SQL fails.
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->encryptionKey = base64_decode(ENCRYPTION_KEY);
    }

    /**
     * Runs a SELECT and returns every row as an array, exactly like INFO2180 Lab 5 (prepare, execute,
     * fetchAll). Put :name placeholders in the SQL and the values in $params, never form input in the SQL
     * text.
     */
    public function select(string $sql, array $params): array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Runs an INSERT or UPDATE and returns how many rows changed. Track-It never deletes records (SF-3).
     */
    public function execute(string $sql, array $params): int
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    /**
     * Encrypts a value with the team key (AES-256-GCM). The Azure database is already encrypted on disk
     * (SC-4), so use this only for secrets such as two-factor secrets, not for fields you need to search.
     */
    public function encrypt(string $value): string
    {
        // You won't need to change this. It locks the text with the team's key (AES-256-GCM).
        $this->checkKey();
        $iv = random_bytes(12);
        $tag = '';
        $locked = openssl_encrypt($value, 'aes-256-gcm', $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $locked);
    }

    /**
     * Reverses encrypt().
     */
    public function decrypt(string $value): string
    {
        // You won't need to change this. It unlocks text made by encrypt().
        $this->checkKey();
        $raw = base64_decode($value);
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $locked = substr($raw, 28);
        $text = openssl_decrypt($locked, 'aes-256-gcm', $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
        if ($text === false) {
            throw new Exception('Could not decrypt. Is everyone using the same ENCRYPTION_KEY?');
        }
        return $text;
    }

    /** Stops encrypt() and decrypt() with a clear message if config.php has no key. */
    private function checkKey()
    {
        if (strlen($this->encryptionKey) != 32) {
            throw new Exception('Set ENCRYPTION_KEY in config/config.php (see README).');
        }
    }
}
