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
        nm_professor,
        ds_email
    FROM tb_professores
    WHERE cd_professor = ?
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {

    $stmt->close();

    header('Location: professores.php');
    exit;
}

$professor = $resultado->fetch_assoc();

$stmt->close();

/* ===== - Buscar Matérias do Professor - ===== */

$sql = "
    SELECT
        m.nm_materia
    FROM tb_professores_materias pm
    INNER JOIN tb_materias m
        ON m.cd_materia = pm.id_materia
    WHERE pm.id_professor = ?
    ORDER BY m.nm_materia
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

$materias = [];

while ($materia = $resultado->fetch_assoc()) {

    $materias[] = $materia['nm_materia'];

}

$stmt->close();


$materia_atual = implode(', ', $materias);

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

        $conexao->begin_transaction();

        try {

            // Atualiza o nome e e-mail do professor
            $sql = "
                UPDATE tb_professores
                SET
                    nm_professor = ?,
                    ds_email = ?
                WHERE cd_professor = ?
            ";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssi",
                $nome,
                $email,
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception();
            }

            $stmt->close();

            // Atualiza o usuário de login
            $sql = "
                UPDATE tb_usuarios
                SET
                    nm_usuario = ?,
                    ds_email = ?
                WHERE id_professor = ?
            ";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssi",
                $nome,
                $email,
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception();
            }

            $stmt->close();

            // Remove os vínculos antigos de matérias
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


            /* Permite colocar mais de uma matéria,
             * separando por vírgula.*/

            $lista_materias = array_filter(
                array_map(
                    'trim',
                    explode(',', $materia)
                )
            );


            foreach ($lista_materias as $nome_materia) {

                // Procura a matéria existente
                $sql = "
                    SELECT cd_materia
                    FROM tb_materias
                    WHERE nm_materia = ?
                ";

                $stmt = $conexao->prepare($sql);

                $stmt->bind_param(
                    "s",
                    $nome_materia
                );

                $stmt->execute();

                $resultado = $stmt->get_result();


                if ($resultado->num_rows > 0) {

                    $dados = $resultado->fetch_assoc();

                    $id_materia = $dados['cd_materia'];

                    $stmt->close();

                } else {

                    $stmt->close();

                    // Cria a matéria se ainda não existir
                    $sigla = strtoupper(
                        substr(
                            preg_replace(
                                '/[^A-Za-z0-9]/',
                                '',
                                $nome_materia
                            ),
                            0,
                            5
                        )
                    );

                    if ($sigla === '') {
                        $sigla = 'MAT';
                    }

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
                        $nome_materia
                    );

                    if (!$stmt->execute()) {
                        throw new Exception();
                    }

                    $id_materia = $conexao->insert_id;

                    $stmt->close();
                }


                // Cria o novo vínculo
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
                    $id,
                    $id_materia
                );

                if (!$stmt->execute()) {
                    throw new Exception();
                }

                $stmt->close();
            }


            $conexao->commit();

            $mensagem = 'Professor atualizado com sucesso!';
            $tipo_mensagem = 'sucesso';

            $professor['nm_professor'] = $nome;
            $professor['ds_email'] = $email;
            $materia_atual = $materia;

        } catch (Exception $e) {

            $conexao->rollback();

            $mensagem = 'Não foi possível atualizar o professor.';
            $tipo_mensagem = 'erro';
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

    <title>Editar Professor - SystemLab</title>

    <link
        rel="stylesheet"
        href="css/editar_professor.css"
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
            Editar Professor
        </h2>

        <p>
            Altere as informações do professor selecionado.
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
                value="<?= htmlspecialchars($professor['nm_professor']) ?>"
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
                value="<?= htmlspecialchars($professor['ds_email']) ?>"
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
                value="<?= htmlspecialchars($materia_atual) ?>"
                placeholder="Ex: Matemática"
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
                Salvar alterações
            </button>

        </div>

    </form>

</main>

</body>

</html>