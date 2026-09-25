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

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $materia = trim($_POST['materia'] ?? '');


    if ($nome === '' || $email === '' || $materia === '') {

        $mensagem = 'Preencha todos os campos.';
        $tipo_mensagem = 'erro';

    } else {

        // Verifica se o e-mail já existe
        $sql = "
            SELECT cd_usuario
            FROM tb_usuarios
            WHERE ds_email = ?
        ";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $mensagem = 'Já existe um usuário cadastrado com esse e-mail.';
            $tipo_mensagem = 'erro';

            $stmt->close();

        } else {

            $stmt->close();

            $conexao->begin_transaction();

            try {

                // Cadastra o professor na Etec
                $sql = "
                    INSERT INTO tb_professores
                    (
                        nm_professor,
                        ds_email,
                        id_escola
                    )
                    VALUES (?, ?, 1)
                ";

                $stmt = $conexao->prepare($sql);

                $stmt->bind_param(
                    "ss",
                    $nome,
                    $email
                );

                if (!$stmt->execute()) {
                    throw new Exception();
                }

                $id_professor = $conexao->insert_id;

                $stmt->close();

                // Procura a matéria
                $sql = "
                    SELECT cd_materia
                    FROM tb_materias
                    WHERE nm_materia = ?
                ";

                $stmt = $conexao->prepare($sql);

                $stmt->bind_param(
                    "s",
                    $materia
                );

                $stmt->execute();

                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {

                    $dados = $resultado->fetch_assoc();

                    $id_materia = $dados['cd_materia'];

                } else {

                    // Caso a matéria ainda não exista, cria uma nova
                    $sigla = strtoupper(
                        substr(
                            preg_replace(
                                '/[^A-Za-z0-9]/',
                                '',
                                $materia
                            ),
                            0,
                            5
                        )
                    );

                    if ($sigla === '') {
                        $sigla = 'MAT';
                    }

                    $stmt->close();

                    $sql = "
                        INSERT INTO tb_materias
                        (
                            sg_materia,
                            nm_materia
                        )
                        VALUES (?, ?)
                    ";

                    $stmt = $conexao->prepare($sql);

                    $stmt->bind_param(
                        "ss",
                        $sigla,
                        $materia
                    );

                    if (!$stmt->execute()) {
                        throw new Exception();
                    }

                    $id_materia = $conexao->insert_id;

                }

                $stmt->close();

                // Relaciona professor e matéria
                $sql = "
                    INSERT INTO tb_professores_materias
                    (
                        id_professor,
                        id_materia
                    )
                    VALUES (?, ?)
                ";

                $stmt = $conexao->prepare($sql);

                $stmt->bind_param(
                    "ii",
                    $id_professor,
                    $id_materia
                );

                if (!$stmt->execute()) {
                    throw new Exception();
                }

                $stmt->close();

                // Cria o acesso do professor
                //Senha inicial: 123456
                $senha = '123456';
                $tipo = 'professor';

                $sql = "
                    INSERT INTO tb_usuarios
                    (
                        nm_usuario,
                        ds_email,
                        ds_senha,
                        ds_tipo,
                        id_professor
                    )
                    VALUES (?, ?, ?, ?, ?)
                ";

                $stmt = $conexao->prepare($sql);

                $stmt->bind_param(
                    "ssssi",
                    $nome,
                    $email,
                    $senha,
                    $tipo,
                    $id_professor
                );

                if (!$stmt->execute()) {
                    throw new Exception();
                }

                $stmt->close();

                $conexao->commit();

                $mensagem = 'Professor cadastrado com sucesso! A senha inicial é 123456.';
                $tipo_mensagem = 'sucesso';

                $_POST = [];

            } catch (Exception $e) {

                $conexao->rollback();

                $mensagem = 'Não foi possível cadastrar o professor.';
                $tipo_mensagem = 'erro';
            }
        }
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

<title>Novo Professor - SystemLab</title>

<link
    rel="stylesheet"
    href="css/novo_professor.css"
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

    <div class="titulo">

        <h2>
            Novo Professor
        </h2>

        <p>
            Cadastre um novo professor no SystemLab.
        </p>

    </div>

    <?php if ($mensagem !== ''): ?>

        <div class="mensagem <?= $tipo_mensagem ?>">

            <?= htmlspecialchars($mensagem) ?>

        </div>

    <?php endif; ?>

    <form
        method="POST"
        class="formulario"
    >

        <div class="campo">

            <label for="nome">
                Nome completo
            </label>

            <input
                type="text"
                id="nome"
                name="nome"
                placeholder="Digite o nome do professor"
                value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                required
            >

        </div>

        <div class="campo">

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite o e-mail"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
            >

        </div>

        <div class="campo">

            <label for="materia">
                Matéria que leciona
            </label>

            <input
                type="text"
                id="materia"
                name="materia"
                placeholder="Digite a matéria que o professor leciona"
                value="<?= htmlspecialchars($_POST['materia'] ?? '') ?>"
                required
            >

        </div>

        <div class="botoes">

            <a
                href="professores.php"
                class="cancelar"
            >
                Cancelar
            </a>

            <button type="submit">
                Cadastrar professor
            </button>

        </div>

    </form>

</main>

</body>

</html>
