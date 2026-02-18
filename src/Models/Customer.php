<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class Customer
{
    public string $id;
    public string $name;
    public ?string $website;
    public string $phone;
    public string $email;
    public string $city;
    public string $state;
    public ?string $industry = null;
    public string $status;
    public bool $needsReview = false;
    public ?string $reviewReason = null;
    public string $created_at;
    public string $updated_at;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CALLBACK = 'callback';
    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_CALLBACK => 'Callback',
        self::STATUS_FOLLOW_UP => 'Follow Up',
    ];

    public static function all(array $filters = [], int $page = 1, int $perPage = 10, string $sort = 'created_at', string $direction = 'desc'): array
    {
        $pdo = Connection::getInstance();

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(name ILIKE :search OR email ILIKE :search OR phone ILIKE :search OR city ILIKE :search OR industry ILIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        // Validate sort parameters
        $allowedSorts = ['name', 'email', 'city', 'industry', 'status', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';
        $direction = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';

        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get customers
        $sql = "SELECT * FROM customers {$whereClause} ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $customers = array_map(fn($data) => self::hydrate($data), $stmt->fetchAll());

        return [
            'data' => $customers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }

    public static function find(string $id): ?self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
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
            INSERT INTO customers (name, website, phone, email, city, state, industry, status, needs_review, review_reason)
            VALUES (:name, :website, :phone, :email, :city, :state, :industry, :status, :needs_review, :review_reason)
            RETURNING *
        ");

        $stmt->execute([
            'name' => $data['name'],
            'website' => $data['website'] ?: null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'city' => $data['city'],
            'state' => $data['state'],
            'industry' => $data['industry'] ?? null,
            'status' => $data['status'] ?? self::STATUS_NEW,
            'needs_review' => !empty($data['needs_review']) ? 1 : 0,
            'review_reason' => $data['review_reason'] ?? null,
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function update(array $data): self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("
            UPDATE customers
            SET name = :name,
                website = :website,
                phone = :phone,
                email = :email,
                city = :city,
                state = :state,
                industry = :industry,
                status = :status,
                needs_review = :needs_review,
                review_reason = :review_reason,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
        ");

        $stmt->execute([
            'id' => $this->id,
            'name' => $data['name'] ?? $this->name,
            'website' => $data['website'] ?? $this->website,
            'phone' => $data['phone'] ?? $this->phone,
            'email' => $data['email'] ?? $this->email,
            'city' => $data['city'] ?? $this->city,
            'state' => $data['state'] ?? $this->state,
            'industry' => $data['industry'] ?? $this->industry,
            'status' => $data['status'] ?? $this->status,
            'needs_review' => !empty($data['needs_review'] ?? $this->needsReview) ? 1 : 0,
            'review_reason' => $data['review_reason'] ?? $this->reviewReason,
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function updateStatus(string $status): self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("
            UPDATE customers
            SET status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
        ");

        $stmt->execute([
            'id' => $this->id,
            'status' => $status,
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function delete(): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public static function countByStatus(): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM customers
            GROUP BY status
        ");

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }

        return $counts;
    }

    public static function totalCount(): int
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
        return (int) $stmt->fetchColumn();
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    private static function hydrate(array $data): self
    {
        $customer = new self();
        $customer->id = $data['id'];
        $customer->name = $data['name'];
        $customer->website = $data['website'];
        $customer->phone = $data['phone'];
        $customer->email = $data['email'];
        $customer->city = $data['city'];
        $customer->state = $data['state'];
        $customer->industry = $data['industry'] ?? null;
        $customer->status = $data['status'];
        $customer->needsReview = (bool) ($data['needs_review'] ?? false);
        $customer->reviewReason = $data['review_reason'] ?? null;
        $customer->created_at = $data['created_at'];
        $customer->updated_at = $data['updated_at'];

        return $customer;
    }

    public static function findByEmail(string $email): ?self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return self::hydrate($data);
    }

    public function clearReviewFlag(): self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("
            UPDATE customers
            SET needs_review = FALSE, review_reason = NULL, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
        ");

        $stmt->execute(['id' => $this->id]);

        return self::hydrate($stmt->fetch());
    }
}
