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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: materias.php');
    exit;
}

/* ===== - Buscar Matéria - ===== */

$sql = "
    SELECT
        cd_materia,
        sg_materia,
        nm_materia
    FROM tb_materias
    WHERE cd_materia = ?
";

$stmt = $conexao->prepare($sql);

$stmt->bind_param(
    'i',
    $id
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    $stmt->close();

    header('Location: materias.php');
    exit;
}

$materia = $resultado->fetch_assoc();

$stmt->close();


$sucesso = '';
$erro = '';

/* ===== - Atualizar Matéria - ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sigla = trim($_POST['sigla'] ?? '');
    $nome = trim($_POST['nome'] ?? '');

    if ($sigla === '' || $nome === '') {

        $erro = 'Preencha todos os campos.';

    } else {
        
        // Verifica se outra matéria já possui a mesma sigla ou o mesmo nome
        $sqlVerificar = "
            SELECT cd_materia
            FROM tb_materias
            WHERE (sg_materia = ? OR nm_materia = ?)
            AND cd_materia <> ?
        ";

        $stmtVerificar = $conexao->prepare($sqlVerificar);

        $stmtVerificar->bind_param(
            'ssi',
            $sigla,
            $nome,
            $id
        );

        $stmtVerificar->execute();

        $resultadoVerificar = $stmtVerificar->get_result();

        if ($resultadoVerificar->num_rows > 0) {

            $erro = 'Já existe outra matéria com essa sigla ou nome.';

        } else {

            $sqlAtualizar = "
                UPDATE tb_materias
                SET
                    sg_materia = ?,
                    nm_materia = ?
                WHERE cd_materia = ?
            ";

            $stmtAtualizar = $conexao->prepare($sqlAtualizar);

            $stmtAtualizar->bind_param(
                'ssi',
                $sigla,
                $nome,
                $id
            );

            if ($stmtAtualizar->execute()) {

                $sucesso = 'Matéria atualizada com sucesso!';

                $materia['sg_materia'] = $sigla;
                $materia['nm_materia'] = $nome;

            } else {

                $erro = 'Não foi possível atualizar a matéria.';

            }

            $stmtAtualizar->close();
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

    <title>Editar Matéria - SystemLab</title>

    <link rel="stylesheet" href="css/editar_materia.css">

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
                    Editar matéria
                </h2>

                <p>
                    Altere as informações da matéria cadastrada.
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
                        value="<?= htmlspecialchars($materia['sg_materia']) ?>"
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
                        value="<?= htmlspecialchars($materia['nm_materia']) ?>"
                        required
                    >

                </div>


                <div class="botoes">

                    <a href="materias.php" class="cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="salvar">
                        Salvar alterações
                    </button>

                </div>

            </form>

        </section>

    </main>

</body>

</html>