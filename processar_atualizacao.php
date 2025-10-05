<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'conexao.php';

    function validarCamposObrigatorios(&$erros, $id, $descricao, $categoria, $quantidade, $preco){
        if (empty($descricao) || empty($categoria) || empty($quantidade) || empty($preco)) {
            $erros[] = "Todos os campos são obrigatórios";
        }
        if ($id <= 0 || filter_var($id, FILTER_VALIDATE_INT) === false) {
            $erros[] = "O ID do produto inválido";
        }
        if ($quantidade < 0 || filter_var($quantidade, FILTER_VALIDATE_INT) === false) {
            $erros[] = "A quantidade deve ser um número inteiro não negativo";
        }
        if ($preco <= 0 || filter_var($preco, FILTER_VALIDATE_FLOAT) === false) {
            $erros[] = "O preço deve ser um número maior que zero";
        }
        if (is_numeric($descricao)){
            $erros[] = "A descrição é inválida";
        }
        if (is_numeric($categoria)){
            $erros[] = "A categoria é inválida";
        }
    }

    function retornarProdutoDoBancoDados($pdo, $id){
        $row = null;

        try {
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            erroLogInterno("Erro ao buscar produto: " . $e->getMessage() . "(processar_atualizacao.php)");
        }

        return $row ? $row : null;
    } 

    function verificaSeHouveAtualizacao($produto_atual, $descricao, $categoria, $quantidade, $preco){
        $houve_alteracao = false;
        if (
            $produto_atual['descricao'] != $descricao ||
            $produto_atual['categoria'] != $categoria ||
            $produto_atual['quantidade'] != $quantidade ||
            floatval($produto_atual['preco']) != $preco
        ) {
            $houve_alteracao = true;
        }
        return $houve_alteracao;
    }


    function atualizarProdutoAtualNoBancoDados($pdo, $id, $descricao, $categoria, $quantidade, $preco){
        try{
            $sql = "UPDATE produtos SET 
                    descricao = :descricao,
                    categoria = :categoria, 
                    quantidade = :quantidade,
                    preco = :preco
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $_SESSION['mensagem'] = ["Produto atualizado com sucesso!"];
                $_SESSION['tipo_mensagem'] = "success";
                header("Location: tela_produtos.php?id=$id");
                exit();
            } else {
                $_SESSION['mensagem'] = ["Erro: Nenhuma linha foi atualizada."];
                $_SESSION['tipo_mensagem'] = "danger";
                header("Location: tela_produtos.php?id=$id");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = ["Erro interno: Produto não atualizado."];
            $_SESSION['tipo_mensagem'] = "danger";
            erroLogInterno("Erro ao atualizar o produto: " . $e->getMessage() . "(processar_atualizacao.php)");
            header("Location: tela_produtos.php?id=$id");
            exit();
        }
    }

    function armazenarErrosNaSessaoSeHouver($erros, $id){
        if (!empty($erros)) {   
            $_SESSION['mensagem'] = $erros;
            $_SESSION['tipo_mensagem'] = "danger";
            header("Location: tela_produtos.php?id=$id");
            exit();
        }
    }

    function main($pdo){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); 
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
            
            $erros = [];
            validarCamposObrigatorios($erros, $id, $descricao, $categoria, $quantidade, $preco);

            armazenarErrosNaSessaoSeHouver($erros, $id);

            if(empty($erros)){
                $produto_atual = retornarProdutoDoBancoDados($pdo, $id);
                
                if (!$produto_atual) {
                    $_SESSION['mensagem'] = ["Erro: Produto não encontrado."];
                    $_SESSION['tipo_mensagem'] = "danger";
                    header("Location: tela_produtos.php");
                    exit();
                }
            
                if (!verificaSeHouveAtualizacao($produto_atual, $descricao, $categoria, $quantidade, $preco)) {
                    $_SESSION['mensagem'] = ["Aviso: Nenhuma alteração foi realizada."];
                    $_SESSION['tipo_mensagem'] = "warning";
                    header("Location: tela_produtos.php?id=$id");
                    exit();
                }else{
                    atualizarProdutoAtualNoBancoDados($pdo, $id, $descricao, $categoria, $quantidade, $preco);
                }
            }
            
        } else {
            header("Location: tela_produtos.php");
            exit();
        }
    }

    main($pdo);

?>