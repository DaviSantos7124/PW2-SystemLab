<?php

session_start();

require_once 'includes/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['tipo'] !== 'administrador') {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: professores.php');
    exit;
}

/* ===== - Buscar Professor - ===== */

$sql = "
    SELECT
        cd_professor,
        nm_professor
    FROM tb_professores
    WHERE cd_professor = ?
";

$stmt = $conexao->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {

    $stmt->close();

    header('Location: professores.php');
    exit;
}

$professor = $resultado->fetch_assoc();

$stmt->close();

$nome = $professor['nm_professor'];

/* ===== - Quando confirmar a exclusão - ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conexao->begin_transaction();

    try {

        // Remove as matérias relacionadas
        $sql = "
            DELETE FROM tb_professores_materias
            WHERE id_professor = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "i",
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception();
        }

        $stmt->close();

        // Remove o usuário de login
        $sql = "
            DELETE FROM tb_usuarios
            WHERE id_professor = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "i",
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception();
        }

        $stmt->close();

        // Remove o professor
        $sql = "
            DELETE FROM tb_professores
            WHERE cd_professor = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "i",
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception();
        }

        $stmt->close();

        $conexao->commit();

        header('Location: professores.php');
        exit;

    } catch (Exception $e) {

        $conexao->rollback();

        $erro = 'Não foi possível excluir o professor.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Excluir Professor - SystemLab
    </title>

    <link
        rel="stylesheet"
        href="css/excluir_professor.css"
    >

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

            <div>

                <strong>
                    <?= htmlspecialchars($_SESSION['usuario']) ?>
                </strong>

                <span>
                    Administrador
                </span>

            </div>

            <a href="sair.php">
                Sair
            </a>

        </div>

    </header>

    <main class="conteudo">

        <a
            href="professores.php"
            class="voltar"
        >
            ← Voltar para professores
        </a>

        <div class="confirmacao">

            <div class="icone">
                ⚠️
            </div>

            <h2>
                Excluir professor?
            </h2>

            <p>
                Você está prestes a excluir o professor:
            </p>

            <strong class="nome">

                <?= htmlspecialchars($nome) ?>

            </strong>

            <p class="aviso">
                Essa ação não poderá ser desfeita.
            </p>

            <?php if (isset($erro)): ?>

                <div class="mensagem">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="botoes">

                    <a
                        href="professores.php"
                        class="cancelar"
                    >
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

        </div>

    </main>

</body>

</html>