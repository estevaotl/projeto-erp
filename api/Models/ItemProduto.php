<?php

namespace Api\Models;

use Api\Core\Database;
use PDO;

class ItemProduto {
    const NOME_TABELA = "itens_produtos";

    public function create(array $dadosItem): bool {
        $pdo = Database::getInstance();

        $sql = "INSERT INTO " . self::NOME_TABELA . " 
                (idProduto, preco, estoque, cor, tamanho, referencia) 
                VALUES 
                (:idProduto, :preco, :estoque, :cor, :tamanho, :referencia)";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':idProduto'   => $dadosItem['idProduto'],
            ':referencia'  => $dadosItem['itens']['referencia'],
            ':preco'       => $dadosItem['itens']['preco'],
            ':estoque'     => $dadosItem['itens']['estoque'],
            ':tamanho'     => $dadosItem['itens']['tamanho'],
            ':cor'         => $dadosItem['itens']['cor']
        ]);
    }

    public function obterComRestricoes(array $restricoes): ?array {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE 1 ";
        $parametros = [];

        if (!isset($restricoes['ativosSomente']) || $restricoes['ativosSomente'] === true) {
            $sql .= " AND " . self::NOME_TABELA . ".ativo = 1 ";
        }

        if (isset($restricoes["idProduto"]) && is_numeric($restricoes['idProduto'])) {
            $sql .= " AND " . self::NOME_TABELA . ".idProduto = :idProduto ";
            $parametros['idProduto'] = $restricoes['idProduto'];
        }

        if (isset($restricoes["id"]) && is_numeric($restricoes['id'])) {
            $sql .= " AND " . self::NOME_TABELA . ".id = :id ";
            $parametros['id'] = $restricoes['id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $itensProduto = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $itensProduto ?: null;
    }

    public function desativar(int $idProduto): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE " . self::NOME_TABELA . " SET ativo = 0 WHERE idProduto = :idProduto";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProduto' => $idProduto]);
        return $stmt->rowCount() > 0;
    }
}
