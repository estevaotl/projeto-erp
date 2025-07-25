<?php

namespace Api\Models;

use Api\Core\Database;
use PDO;

class ItemPedido {
    const NOME_TABELA = "itens_pedidos";

    public function criar(array $dados): int {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO " . self::NOME_TABELA . " (idPedido, quantidade, idItem) VALUES (:idPedido, :quantidade, :idItem)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idPedido'   => $dados["idPedido"],
            ':quantidade' => $dados["quantidade"],
            ':idItem'     => $dados["idItem"]
        ]);
        return $pdo->lastInsertId();
    }

    public function obterComRestricoes(array $restricoes): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE 1 ";
        $parametros = array();
        if (is_numeric($restricoes['idPedido'])) {
            $sql .= " AND " . self::NOME_TABELA . ".idPedido = :idPedido ";
            $parametros['idPedido'] = $restricoes['idPedido'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $itensPedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $itensPedido ?: null;
    }
}
