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

/* ===== - Busca os professores cadastrados - ===== */

$sql = "
    SELECT
        p.cd_professor,
        p.nm_professor,
        p.ds_email,
        GROUP_CONCAT(
            DISTINCT m.nm_materia
            ORDER BY m.nm_materia
            SEPARATOR ', '
        ) AS materias
    FROM tb_professores p
    LEFT JOIN tb_professores_materias pm
        ON pm.id_professor = p.cd_professor
    LEFT JOIN tb_materias m
        ON m.cd_materia = pm.id_materia
    GROUP BY
        p.cd_professor,
        p.nm_professor,
        p.ds_email
    ORDER BY p.cd_professor
";

$resultado = $conexao->query($sql);

$professores = [];

if ($resultado) {

    while ($professor = $resultado->fetch_assoc()) {

        $professores[] = $professor;

    }
}

/* ===== - Quantidade de matérias cadastradas - ===== */

$sql_materias = "
    SELECT COUNT(*) AS total
    FROM tb_materias
";

$resultado_materias = $conexao->query($sql_materias);

$total_materias = 0;

if ($resultado_materias) {

    $dados_materias = $resultado_materias->fetch_assoc();

    $total_materias = (int) $dados_materias['total'];
}

$total_professores = count($professores);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Professores - SystemLab</title>

<link
    rel="stylesheet"
    href="css/professores.css"
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

        <div class="usuario-info">

            <strong>
                <?= htmlspecialchars($_SESSION['usuario']) ?>
            </strong>

            <span>
                Administrador
            </span>

        </div>

        <a
            href="sair.php"
            class="sair"
        >
            Sair
        </a>

    </div>

</header>

<main class="conteudo">

    <div class="cabecalho">

        <a
            href="index.php"
            class="voltar"
        >
            ← Voltar
        </a>

        <div class="cabecalho-linha">

            <div>

                <h2>
                    Professores
                </h2>

                <p>
                    Gerencie os professores cadastrados no SystemLab.
                </p>

            </div>

            <a
                href="novo_professor.php"
                class="novo-professor"
            >
                + Novo professor
            </a>

        </div>

    </div>

    <section class="resumo">

        <div class="resumo-card">

            <div class="icone">
                👨‍🏫
            </div>

            <div>

                <strong>
                    <?= $total_professores ?>
                </strong>

                <span>
                    Professores cadastrados
                </span>

            </div>

        </div>

        <div class="resumo-card">

            <div class="icone">
                📚
            </div>

            <div>

                <strong>
                    <?= $total_materias ?>
                </strong>

                <span>
                    Matérias
                </span>

            </div>

        </div>

        <div class="resumo-card">

            <div class="icone">
                ✅
            </div>

            <div>

                <strong>
                    <?= $total_professores ?>
                </strong>

                <span>
                    Professores ativos
                </span>

            </div>

        </div>

    </section>

    <section class="tabela-container">

        <div class="tabela-topo">

            <div>

                <h3>
                    Lista de professores
                </h3>

                <p>
                    Professores cadastrados no sistema.
                </p>

            </div>

            <input
                type="text"
                id="pesquisa"
                placeholder="Pesquisar professor..."
            >

        </div>

        <div class="tabela-scroll">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Professor</th>

                        <th>E-mail</th>

                        <th>Matéria</th>

                        <th>Status</th>

                        <th>Ações</th>

                    </tr>

                </thead>

                <tbody id="lista-professores">

                    <?php foreach ($professores as $professor): ?>

                        <tr>

                            <td>
                                #<?= $professor['cd_professor'] ?>
                            </td>

                            <td>

                                <div class="professor">

                                    <div class="avatar">

                                        <?= strtoupper(
                                            substr(
                                                $professor['nm_professor'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $professor['nm_professor']
                                        ) ?>

                                    </strong>

                                </div>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $professor['ds_email']
                                ) ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $professor['materias'] ?? 'Nenhuma matéria'
                                ) ?>

                            </td>

                            <td>

                                <span class="ativo">
                                    Ativo
                                </span>

                            </td>

                            <td>

                                <div class="acoes">

                                    <a
                                        href="editar_professor.php?id=<?= $professor['cd_professor'] ?>"
                                        class="editar"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        href="excluir_professor.php?id=<?= $professor['cd_professor'] ?>"
                                        class="excluir"
                                    >
                                        Excluir
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script>

    const pesquisa = document.getElementById('pesquisa');

    const linhas = document.querySelectorAll(
        '#lista-professores tr'
    );

    pesquisa.addEventListener('input', function () {

        const texto = this.value.toLowerCase();


        linhas.forEach(function (linha) {

            const conteudo = linha.textContent.toLowerCase();


            if (conteudo.includes(texto)) {

                linha.style.display = '';

            } else {

                linha.style.display = 'none';

            }

        });

    });

</script>

</body>

</html>