<?php

namespace Api\Models;

use Api\Core\Database;
use PDO;

class Pedido {
    const NOME_TABELA = "pedidos";

    public function obterTodos() {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . "";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $produtos ?: null;
    }

    public function create(array $dados): int {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO " . self::NOME_TABELA . " (valorPedido, valorFrete, dataPedido, status, idCupom) VALUES (:valorPedido, :valorFrete, NOW(), :status, :idCupom)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':valorPedido' => $dados["valorPedido"],
            ':valorFrete'  => $dados["valorFrete"],
            ':status'      => $dados["status"],
            ':idCupom'     => $dados["idCupom"]
        ]);
        return $pdo->lastInsertId();
    }
}
