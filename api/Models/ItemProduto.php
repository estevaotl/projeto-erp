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
        $sql = "SELECT * FROM " . self::NOME_TABELA . " WHERE itens_produtos.ativo = 1 ";
        $parametros = array();
        if (is_numeric($restricoes['idProduto'])) {
            $sql .= " AND itens_produtos.idProduto = :idProduto ";
            $parametros['idProduto'] = $restricoes['idProduto'];
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
