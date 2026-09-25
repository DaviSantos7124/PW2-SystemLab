<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['tipo'] !== 'administrador') {
    header('Location: index.php');
    exit;
}

require_once 'includes/conexao.php';

$sucesso = '';
$erro = '';

$sigla = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sigla = trim($_POST['sigla'] ?? '');
    $nome = trim($_POST['nome'] ?? '');

    if ($sigla === '' || $nome === '') {

        $erro = 'Preencha todos os campos.';

    } else {

        $sqlVerificar = "
            SELECT cd_materia
            FROM tb_materias
            WHERE sg_materia = ?
            OR nm_materia = ?
        ";

        $stmtVerificar = $conexao->prepare($sqlVerificar);

        $stmtVerificar->bind_param(
            'ss',
            $sigla,
            $nome
        );

        $stmtVerificar->execute();

        $resultado = $stmtVerificar->get_result();

        if ($resultado->num_rows > 0) {

            $erro = 'Já existe uma matéria com essa sigla ou nome.';

        } else {

            $sql = "
                INSERT INTO tb_materias
                (sg_materia, nm_materia)
                VALUES (?, ?)
            ";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                'ss',
                $sigla,
                $nome
            );

            if ($stmt->execute()) {

                $sucesso = 'Matéria cadastrada com sucesso!';

                $sigla = '';
                $nome = '';

            } else {

                $erro = 'Não foi possível cadastrar a matéria.';

            }

            $stmt->close();
        }

        $stmtVerificar->close();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nova Matéria - SystemLab</title>

    <link rel="stylesheet" href="css/nova_materia.css">

</head>

<body>

    <header class="topo">

        <div class="logo">

            <h1>
                SystemLab
            </h1>

            <span>
                Sistema de Laboratórios
            </span>

        </div>

        <div class="usuario">

            <div class="usuario-info">

                <strong>
                    <?= htmlspecialchars($_SESSION['usuario']) ?>
                </strong>

                <span>
                    Administrador
                </span>

            </div>

            <a href="sair.php" class="sair">
                Sair
            </a>

        </div>

    </header>

    <main class="conteudo">

        <a href="materias.php" class="voltar">
            ← Voltar para matérias
        </a>

        <section class="formulario">

            <div class="cabecalho">

                <h2>
                    Adicionar nova matéria
                </h2>

                <p>
                    Cadastre uma nova matéria no SystemLab.
                </p>

            </div>

            <?php if ($sucesso): ?>

                <div class="sucesso">
                    <?= htmlspecialchars($sucesso) ?>
                </div>

            <?php endif; ?>

            <?php if ($erro): ?>

                <div class="erro">
                    <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="campo">

                    <label for="sigla">
                        Sigla
                    </label>

                    <input
                        type="text"
                        id="sigla"
                        name="sigla"
                        maxlength="10"
                        placeholder="Ex: MAT"
                        value="<?= htmlspecialchars($sigla) ?>"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="nome">
                        Nome da matéria
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Digite o nome da matéria"
                        value="<?= htmlspecialchars($nome) ?>"
                        required
                    >

                </div>

                <div class="botoes">

                    <a href="materias.php" class="cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="salvar">
                        Cadastrar matéria
                    </button>

                </div>

            </form>

        </section>

    </main>

</body>

</html>