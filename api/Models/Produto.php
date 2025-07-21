<?php
namespace Api\Models;

use Api\Core\Database;
use PDO;

class Produto {
    public function obterTodos() {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM produtos WHERE ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $produtos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $produtos ?: null;
    }

    public function create(string $email, string $senha): bool {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO users (email, senha) VALUES (:email, :senha)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':senha' => password_hash($senha, PASSWORD_DEFAULT)
        ]);
    }

    public function findByEmail(string $email): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}

