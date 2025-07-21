<?php
namespace Api\Models;

use Api\Core\Database;
use PDO;

class ItemProduto {
    public function create(string $email, string $senha): bool {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO users (email, senha) VALUES (:email, :senha)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':senha' => password_hash($senha, PASSWORD_DEFAULT)
        ]);
    }

    public function obterComRestricoes(array $restricoes): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM itens_produtos WHERE itens_produtos.ativo = 1 ";
        $parametros = array();
        if (is_numeric($restricoes['idProduto'])) {
            $sql .= " AND itens_produtos.idProduto = :idProduto ";
            $parametros['idProduto'] = $restricoes['idProduto'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $itensProduto = $stmt->fetch(PDO::FETCH_ASSOC);
        return $itensProduto ?: null;
    }
}

