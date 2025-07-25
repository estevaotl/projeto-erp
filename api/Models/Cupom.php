<?php

namespace Api\Models;

use Api\Core\Database;
use PDO;

class Cupom {
    const NOME_TABELA = "cupons";

    public function obterTodos() {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $cupons ?: null;
    }

    public function criar(array $dados): int {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO " . self::NOME_TABELA . " (referencia, validade, valorMinimo, valorDesconto) VALUES (:referencia, :validade, :valorMinimo, :valorDesconto)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':referencia'    => $dados["referencia"],
            ':validade'      => $dados["validade"],
            ':valorMinimo'   => $dados["valorMinimo"],
            ':valorDesconto' => $dados["valorDesconto"],
        ]);
        return $pdo->lastInsertId();
    }

    public function atualizar(array $dados): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE " . self::NOME_TABELA . " SET referencia = :referencia, validade = :validade, valorMinimo = :valorMinimo, valorDesconto = :valorDesconto WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'            => $dados["id"],
            ':referencia'    => $dados["referencia"],
            ':validade'      => $dados["validade"],
            ':valorMinimo'   => $dados["valorMinimo"],
            ':valorDesconto' => $dados["valorDesconto"]
        ]);
        return $stmt->rowCount() > 0;
    }

    public function desativar(int $idCupom): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE " . self::NOME_TABELA . " SET ativo = 0 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idCupom]);
        return $stmt->rowCount() > 0;
    }

    public function obterComRestricoes(array $restricoes): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE 1 ";
        $parametros = array();

        if (!isset($restricoes['ativosSomente']) || $restricoes['ativosSomente'] === true) {
            $sql .= " AND " . self::NOME_TABELA . ".ativo = 1 ";
        }

        if (is_numeric($restricoes['id'])) {
            $sql .= " AND " . self::NOME_TABELA . ".id = :id ";
            $parametros['id'] = $restricoes['id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $cupons ?: null;
    }
}
