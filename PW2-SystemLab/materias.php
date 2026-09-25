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

$sql = "
    SELECT
        m.cd_materia,
        m.sg_materia,
        m.nm_materia,
        GROUP_CONCAT(
            DISTINCT p.nm_professor
            ORDER BY p.nm_professor
            SEPARATOR ', '
        ) AS professores
    FROM tb_materias m
    LEFT JOIN tb_professores_materias pm
        ON pm.id_materia = m.cd_materia
    LEFT JOIN tb_professores p
        ON p.cd_professor = pm.id_professor
    GROUP BY
        m.cd_materia,
        m.sg_materia,
        m.nm_materia
    ORDER BY m.cd_materia
";

$resultado = $conexao->query($sql);

$materias = [];

if ($resultado) {

    while ($linha = $resultado->fetch_assoc()) {

        $materias[] = $linha;

    }

}

$totalMaterias = count($materias);

$sqlProfessores = "SELECT COUNT(*) AS total FROM tb_professores";

$resultadoProfessores = $conexao->query($sqlProfessores);

$totalProfessores = 0;

if ($resultadoProfessores) {

    $dadosProfessores = $resultadoProfessores->fetch_assoc();

    $totalProfessores = (int) $dadosProfessores['total'];

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Matérias - SystemLab</title>

    <link rel="stylesheet" href="css/materias.css">

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

        <div class="cabecalho">

            <a href="index.php" class="voltar">
                ← Voltar
            </a>

            <div class="cabecalho-linha">

                <div>

                    <h2>
                        Matérias
                    </h2>

                    <p>
                        Gerencie as matérias utilizadas nas reservas.
                    </p>

                </div>

                <a href="nova_materia.php" class="nova-materia">
                    + Nova matéria
                </a>

            </div>

        </div>

        <section class="resumo">

            <div class="resumo-card">

                <div class="icone">
                    📚
                </div>

                <div>

                    <strong>
                        <?= $totalMaterias ?>
                    </strong>

                    <span>
                        Matérias cadastradas
                    </span>

                </div>

            </div>

            <div class="resumo-card">

                <div class="icone">
                    👨‍🏫
                </div>

                <div>

                    <strong>
                        <?= $totalProfessores ?>
                    </strong>

                    <span>
                        Professores
                    </span>

                </div>

            </div>

            <div class="resumo-card">

                <div class="icone">
                    ✅
                </div>

                <div>

                    <strong>
                        <?= $totalMaterias ?>
                    </strong>

                    <span>
                        Matérias ativas
                    </span>

                </div>

            </div>

        </section>

        <section class="tabela-container">

            <div class="tabela-topo">

                <div>

                    <h3>
                        Lista de matérias
                    </h3>

                    <p>
                        Matérias cadastradas no sistema.
                    </p>

                </div>

                <input
                    type="text"
                    id="pesquisa"
                    placeholder="Pesquisar matéria..."
                    autocomplete="off"
                >

            </div>

            <div class="tabela-scroll">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Sigla
                            </th>

                            <th>
                                Matéria
                            </th>

                            <th>
                                Professor(es)
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>

                    <tbody id="lista-materias">

                        <?php foreach ($materias as $materia): ?>

                            <tr>

                                <td>
                                    #<?= $materia['cd_materia'] ?>
                                </td>

                                <td>

                                    <span class="sigla">
                                        <?= htmlspecialchars($materia['sg_materia']) ?>
                                    </span>

                                </td>

                                <td>

                                    <strong class="nome-materia">
                                        <?= htmlspecialchars($materia['nm_materia']) ?>
                                    </strong>

                                </td>

                                <td>
                                    <?= htmlspecialchars($materia['professores'] ?? 'Nenhum professor') ?>
                                </td>

                                <td>

                                    <span class="ativo">
                                        Ativa
                                    </span>

                                </td>

                                <td>

                                    <div class="acoes">

                                        <a
                                            href="editar_materia.php?id=<?= $materia['cd_materia'] ?>"
                                            class="editar"
                                        >
                                            Editar
                                        </a>


                                        <a
                                            href="excluir_materia.php?id=<?= $materia['cd_materia'] ?>"
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

        /* ===== - Pesquisa por nome da matéria - ===== */

        const pesquisa = document.getElementById('pesquisa');

        const linhas = document.querySelectorAll(
            '#lista-materias tr'
        );

        pesquisa.addEventListener('input', function () {

            const texto = this.value
                .toLowerCase()
                .trim();

            linhas.forEach(function (linha) {

                const nomeMateria = linha
                    .querySelector('.nome-materia')
                    .textContent
                    .toLowerCase()
                    .trim();

                if (nomeMateria.includes(texto)) {

                    linha.style.display = '';

                } else {

                    linha.style.display = 'none';

                }

            });

        });

    </script>

</body>

</html>