<?php
    session_start();

    require_once 'leitura_produtos.php';

    function retornarProdutoEdicaoDoBancoDados($pdo, $id_edicao, &$produto_edicao, &$modo_edicao){
        if($id_edicao > 0){
            try{
                $sql = "SELECT * FROM produtos WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id_edicao]);
                $stmt->bindParam(':id', $id_edicao, PDO::PARAM_INT);
                $stmt->execute();
                $produto_edicao = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($produto_edicao) {
                    $modo_edicao = true;
                }

            } catch (PDOException $e) {
                erroLogInterno("Erro ao buscar produto: " . $e->getMessage() . "(processar_atualizacao.php)");
            }
        } 
    }

    $modo_edicao = false;
    $modo_cadastro = false;
    $produto_edicao = null;

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id_edicao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        retornarProdutoEdicaoDoBancoDados($pdo, $id_edicao, $produto_edicao, $modo_edicao);
    }

    if (isset($_SESSION['erros_cadastro'])) {
        $erros = $_SESSION['erros_cadastro'];
        $tipo = 'danger';
        $modo_cadastro = true;
    } elseif (isset($_SESSION['mensagem'])) {
        $erros = is_array($_SESSION['mensagem']) ? $_SESSION['mensagem'] : [$_SESSION['mensagem']];
        $tipo = $_SESSION['tipo_mensagem'] ?? 'info';
        $modo_cadastro = false;
    }

    $descricao = isset($_SESSION['dados_cadastro']['descricao']) ? $_SESSION['dados_cadastro']['descricao'] : '';
    $categoria = isset($_SESSION['dados_cadastro']['categoria']) ? $_SESSION['dados_cadastro']['categoria'] : '';

    unset($_SESSION['erros_cadastro']);
    unset($_SESSION['dados_cadastro']);
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Tela de Produtos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
    <body>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h3 class="text-center text-secondary mt-2"> <?php echo $modo_edicao ? "Atualização de Produto" : 'Cadastro de Produto'; ?> </h3>

                <?php if (!empty($erros)): ?>
                    <div class="alert alert-<?php echo $tipo; ?> d-flex justify-content-between align-items-center" role="alert">
                        <div>
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?php echo htmlspecialchars($erro); ?></li>
                                <?php endforeach; ?>
                            </ul> 
                        </div>                       
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                    
                <form method="POST" action="<?php echo $modo_edicao ? 'processar_atualizacao.php' : 'processar_cadastro.php';?>">
                    <div class="card mb-3 mt-3">
                         <?php if ($modo_edicao): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($produto_edicao['id']); ?>">
                        <?php endif; ?>
                        <div class="form-group m-2">
                            <label for="descricao">Descrição do Produto</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" value="<?php echo $modo_edicao ? htmlspecialchars($produto_edicao['descricao']) : ($modo_cadastro ? $descricao : ''); ?>" placeholder="Informe a descrição do produto" required>
                        </div>
                        <div class="form-group m-2">
                            <label for="categoria">Categoria do Produto</label>
                            <input type="text" class="form-control" id="categoria" name="categoria" value="<?php echo $modo_edicao ? htmlspecialchars($produto_edicao['categoria']) : ($modo_cadastro ? $categoria : ''); ?>" placeholder="Informe a categoria do produto" required>
                        </div>
                        <div class="form-row m-2">
                            <div class="col">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?php echo $modo_edicao ? htmlspecialchars($produto_edicao['quantidade']) : ''; ?>" required>
                            </div>
                            <div class="col">
                                <label for="preco">Preço Unitário (R$)</label>
                                <input type="number" class="form-control" id="preco" name="preco" value="<?php echo $modo_edicao ? htmlspecialchars($produto_edicao['preco']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary w-100"><?php echo $modo_edicao ? "Atualizar Produto" : 'Cadastrar Produto'; ?></button>
                        </div>
                        <div class="col">
                            <?php if ($modo_edicao): ?>
                                <a href="tela_produtos.php" class="btn btn-outline-secondary flex-grow-1 w-100">Voltar ao Modo Cadastro</a>
                            <?php else: ?>
                                <button type="reset" class="btn btn-secondary flex-grow-1 w-100">Limpar Campos</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>  
                
                <div class="col mt-3 text-center">
                    <?php if (empty($produtos)) { ?>
                        <div class="alert alert-warning mt-4 text-center fs-5 col-8 mx-auto" role="alert">
                            Nenhum produto cadastrado!
                        </div>
                    <?php } else { ?> 
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Descrição</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Preço</th>
                                    <th scope="col">Quantidade</th>
                                    <th scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach($produtos as $produto){ 
                                        echo '<tr class="' . (($modo_edicao && $produto['id'] == $produto_edicao['id']) ? 'table-warning' : '') . '">';
                                            echo "<td>".$produto['id']."</td>";
                                            echo "<td>".htmlspecialchars($produto['descricao'])."</td>";
                                            echo "<td>".htmlspecialchars($produto['categoria'])."</td>";
                                            echo "<td>".$produto['preco']."</td>";
                                            echo "<td>".$produto['quantidade']."</td>";
                                        
                                            echo '<td class="col">';
                                                echo "<a href='tela_produtos.php?id={$produto['id']}' class='btn btn-warning'>Atualizar</a> ";
                                                echo "<a href='excluir_produto.php?id={$produto['id']}' class='btn btn-danger' onclick=\"return confirm('Tem certeza que deseja excluir este produto?');\">Excluir</a>";
                                            echo "</td>";
                                        echo "</tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </body>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</html>