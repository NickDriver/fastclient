<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class User
{
    public string $id;
    public string $email;
    public string $password;
    public string $name;
    public string $created_at;
    public string $updated_at;

    public static function find(string $id): ?self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return self::hydrate($data);
    }

    public static function findByEmail(string $email): ?self
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

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
            INSERT INTO users (email, password, name)
            VALUES (:email, :password, :name)
            RETURNING *
        ");

        $stmt->execute([
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
        ]);

        return self::hydrate($stmt->fetch());
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    private static function hydrate(array $data): self
    {
        $user = new self();
        $user->id = $data['id'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->name = $data['name'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];

        return $user;
    }
}
