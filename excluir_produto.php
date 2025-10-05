<?php

    require_once 'conexao.php';

    function excluirDoBancoDado($pdo, $id){
        $sql = "DELETE FROM produtos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function main($pdo){
        if(isset($_GET['id'])){
            $id = intval($_GET['id']);

            try{
                excluirDoBancoDado($pdo, $id);
                header("Location: tela_produtos.php");
                exit();
            }catch(PDOException $e){
                erroLogInterno("Erro ao deletar produto: " . $e->getMessage() . "(excluir_produto.php)");
            }
            
        }
    }

    main($pdo);

?>