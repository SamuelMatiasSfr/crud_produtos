<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'conexao.php';

    function validarCamposObrigatorios(&$erros, $descricao, $categoria, $quantidade, $preco){
        if (empty($descricao) || empty($categoria) || empty($quantidade) || empty($preco)) {
            $erros[] = "Todos os campos são obrigatórios";
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

    function cadastrarProdutoNoBancoDados($pdo, $descricao, $categoria, $quantidade, $preco){
        $sql = "INSERT INTO produtos (descricao, categoria, quantidade, preco) 
        VALUES (:descricao, :categoria, :quantidade, :preco)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'descricao' => $descricao,
            'categoria' => $categoria,
            'quantidade' => $quantidade,
            'preco' => $preco
        ]);
    }   

    function armazenarInformacoesNaSessao($erros, $descricao, $categoria){
        $_SESSION['erros_cadastro'] = $erros;
        $_SESSION['dados_cadastro'] = [
            'descricao' => $descricao,
            'categoria' => $categoria
        ];
    }

    function main($pdo){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
        
            $erros = [];
            validarCamposObrigatorios($erros, $descricao, $categoria, $quantidade, $preco);

            if(empty($erros)){
                try{
                    cadastrarProdutoNoBancoDados($pdo, $descricao, $categoria, $quantidade, $preco);
                }catch(PDOException $e){
                    erroLogInterno("Erro ao cadastrar produto: " . $e->getMessage() . "(processar_cadastro.php)");
                }
            }else{
                armazenarInformacoesNaSessao($erros, $descricao, $categoria);
            }

            header("Location: tela_produtos.php");
            exit;
        }
    }

    main($pdo);

?>