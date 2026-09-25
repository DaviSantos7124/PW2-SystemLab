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

$erro = '';

/* ===== - Excluir Matéria - ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $conexao->begin_transaction();

        // Primeiro remove os vínculos entre professores e matéria
        $sqlVinculos = "
            DELETE FROM tb_professores_materias
            WHERE id_materia = ?
        ";

        $stmtVinculos = $conexao->prepare($sqlVinculos);

        $stmtVinculos->bind_param(
            'i',
            $id
        );

        $stmtVinculos->execute();

        $stmtVinculos->close();

        // Depois remove a matéria
        $sqlMateria = "
            DELETE FROM tb_materias
            WHERE cd_materia = ?
        ";

        $stmtMateria = $conexao->prepare($sqlMateria);

        $stmtMateria->bind_param(
            'i',
            $id
        );

        $stmtMateria->execute();

        $stmtMateria->close();


        $conexao->commit();

        header('Location: materias.php');
        exit;

    } catch (Exception $e) {

        $conexao->rollback();

        $erro = 'Não foi possível excluir a matéria.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Excluir Matéria - SystemLab</title>

    <link rel="stylesheet" href="css/excluir_materia.css">

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

        <section class="confirmacao">

            <div class="icone">
                ⚠️
            </div>

            <h2>
                Excluir matéria?
            </h2>

            <p>
                Você está prestes a excluir a matéria:
            </p>

            <div class="materia">

                <strong>
                    <?= htmlspecialchars($materia['nm_materia']) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($materia['sg_materia']) ?>
                </span>

            </div>

            <p class="aviso">
                Essa ação não poderá ser desfeita.
            </p>

            <?php if ($erro): ?>

                <div class="erro">
                    <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="botoes">

                    <a href="materias.php" class="cancelar">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="excluir"
                    >
                        Sim, excluir
                    </button>

                </div>

            </form>

        </section>

    </main>

</body>

</html>