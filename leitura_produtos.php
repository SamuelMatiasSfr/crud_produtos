<?php

    require_once 'conexao.php';

    function lerProdutoDoBancoDados($pdo){
        $sql = "SELECT id, descricao, quantidade, categoria, preco  
        FROM produtos ORDER BY descricao ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function main($pdo){
        $produtos = [];

        try{
            $produtos = lerProdutoDoBancoDados($pdo);
        }catch(PDOException $e){
            erroLogInterno("Erro ao consultar produtos: " . $e->getMessage() . "(leitura_produtos.php)");
        }

        return $produtos;
    }

    $produtos = main($pdo);

?>