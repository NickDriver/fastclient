<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class CustomerNote
{
    public string $id;
    public string $customerId;
    public string $content;
    public string $created_at;
    public string $updated_at;

    public static function findByCustomerId(string $customerId): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM customer_notes WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customerId]);

        return array_map(fn($data) => self::hydrate($data), $stmt->fetchAll());
    }

    public static function find(string $id): ?self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM customer_notes WHERE id = ?");
        $stmt->execute([$id]);

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return self::hydrate($data);
    }

    public static function create(array $data): self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO customer_notes (customer_id, content)
            VALUES (:customer_id, :content)
            RETURNING *
        ");

        $stmt->execute([
            'customer_id' => $data['customer_id'],
            'content' => $data['content'],
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function update(array $data): self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("
            UPDATE customer_notes
            SET content = :content,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
        ");

        $stmt->execute([
            'id' => $this->id,
            'content' => $data['content'] ?? $this->content,
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function delete(): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("DELETE FROM customer_notes WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    private static function hydrate(array $data): self
    {
        $note = new self();
        $note->id = $data['id'];
        $note->customerId = $data['customer_id'];
        $note->content = $data['content'];
        $note->created_at = $data['created_at'];
        $note->updated_at = $data['updated_at'];

        return $note;
    }
}
