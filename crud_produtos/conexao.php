<?php
    function erroLogInterno($erro){
        error_log("[" . date('Y-m-d H:i:s') . "] " . $erro . PHP_EOL, 3, __DIR__ . 'erros.log');
    }

    $host = 'localhost';
    $dbname = 'db_web_loja';
    $username = 'root';
    $password = '';

    try{
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDPException $e){
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
?>