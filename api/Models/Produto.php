<?php

namespace Api\Models;

use Api\Core\Database;
use PDO;

class Produto {
    const NOME_TABELA = "produtos";

    public function obterTodos() {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $produtos ?: null;
    }

    public function criar(string $nome): int {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO " . self::NOME_TABELA . " (nome) VALUES (:nome)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome
        ]);
        return $pdo->lastInsertId();
    }

    public function atualizar(int $id, string $nome): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE " . self::NOME_TABELA . " SET nome = :nome WHERE id = :id AND ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'   => $id,
            ':nome' => $nome
        ]);
        return $stmt->rowCount() > 0;
    }

    public function desativar(int $idProduto): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE " . self::NOME_TABELA . " SET ativo = 0 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idProduto]);
        return $stmt->rowCount() > 0;
    }

    public function obterComRestricoes(array $restricoes): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE 1 ";
        $parametros = array();

        if (!isset($restricoes['ativosSomente']) || $restricoes['ativosSomente'] === true) {
            $sql .= " AND " . self::NOME_TABELA . ".ativo = 1 ";
        }

        if (isset($restricoes['id']) && is_numeric($restricoes['id'])) {
            $sql .= " AND " . self::NOME_TABELA . ".id = :id ";
            $parametros['id'] = $restricoes['id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $produtos ?: null;
    }
}
