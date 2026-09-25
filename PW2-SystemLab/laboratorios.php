<?php

session_start();

require_once 'includes/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipo'];

/* ===== - Busca os laboratórios cadastrados no banco - ===== */

$sql = "
    SELECT
        s.cd_sala,
        s.nm_sala,
        s.nr_computadores,
        COUNT(c.cd_computador) AS total_computadores,
        SUM(
            CASE
                WHEN c.ds_status = 'Funcionando' THEN 1
                ELSE 0
            END
        ) AS computadores_funcionando
    FROM tb_salas s
    LEFT JOIN tb_computadores c
        ON c.id_sala = s.cd_sala
    GROUP BY
        s.cd_sala,
        s.nm_sala,
        s.nr_computadores
    ORDER BY s.cd_sala
";

$resultado = $conexao->query($sql);

$laboratorios = [];

if ($resultado) {

    while ($laboratorio = $resultado->fetch_assoc()) {

        $laboratorios[] = $laboratorio;
    }
}

/* ===== - Verifica quais laboratórios possuem reservas - ===== */

//Considera reservas de hoje em diante
$sqlReservas = "
    SELECT DISTINCT
        rs.id_sala
    FROM tb_reservas_salas rs
    INNER JOIN tb_reservas r
        ON r.cd_reserva = rs.id_reserva
    WHERE r.dt_reserva >= CURDATE()
";

$resultadoReservas = $conexao->query($sqlReservas);

$laboratoriosReservados = [];

if ($resultadoReservas) {

    while ($reserva = $resultadoReservas->fetch_assoc()) {

        $laboratoriosReservados[] =
            (int) $reserva['id_sala'];
    }
}

/* ===== - Resumo geral - ===== */

$total_laboratorios = count($laboratorios);

$total_computadores = 0;
$total_funcionando = 0;

foreach ($laboratorios as $laboratorio) {

    $total_computadores +=
        (int) $laboratorio['total_computadores'];

    $total_funcionando +=
        (int) $laboratorio['computadores_funcionando'];
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
        Laboratórios - SystemLab
    </title>

    <link
        rel="stylesheet"
        href="css/laboratorios.css"
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
                <?= htmlspecialchars($usuario) ?>
            </strong>

            <span>
                <?= $tipo === 'administrador'
                    ? 'Administrador'
                    : 'Professor'
                ?>
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

        <h2>
            Laboratórios
        </h2>

        <p>
            Consulte os laboratórios disponíveis e seus computadores.
        </p>

    </div>

    <section class="resumo">

        <div class="resumo-card">

            <div class="icone">
                💻
            </div>

            <div>

                <strong>
                    <?= $total_laboratorios ?>
                </strong>

                <span>
                    Laboratórios
                </span>

            </div>

        </div>

        <div class="resumo-card">

            <div class="icone">
                🖥️
            </div>

            <div>

                <strong>
                    <?= $total_computadores ?>
                </strong>

                <span>
                    Computadores
                </span>

            </div>

        </div>

        <div class="resumo-card">

            <div class="icone">
                ✅
            </div>

            <div>

                <strong>
                    <?= $total_funcionando ?>
                </strong>

                <span>
                    Computadores funcionando
                </span>

            </div>

        </div>

    </section>

    <section class="laboratorios">

        <div class="titulo-secao">

            <h3>
                Lista de laboratórios
            </h3>

            <p>
                Cada laboratório possui 20 computadores.
            </p>

        </div>

        <div class="laboratorios-grid">

            <?php foreach ($laboratorios as $laboratorio): ?>

                <?php

                $id =
                    (int) $laboratorio['cd_sala'];

                $nome =
                    $laboratorio['nm_sala'];

                $total =
                    (int) $laboratorio['total_computadores'];

                $funcionando =
                    (int) $laboratorio['computadores_funcionando'];

                // Verifica se o laboratório possui alguma reserva
                $reservado = in_array(
                    $id,
                    $laboratoriosReservados,
                    true
                );

                // Define o status do laboratório
                if ($total === 0) {

                    $status = 'Sem computadores';
                    $classeStatus = 'sem-computadores';

                } elseif ($reservado) {

                    $status = 'Reservado';
                    $classeStatus = 'reservado';

                } elseif ($funcionando === $total) {

                    $status = 'Disponível';
                    $classeStatus = 'disponivel';

                } else {

                    $status = 'Atenção';
                    $classeStatus = 'atencao';
                }

                ?>

                <div class="laboratorio">

                    <div class="laboratorio-topo">

                        <div class="icone-lab">
                            💻
                        </div>

                        <span class="status <?= $classeStatus ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>

                    </div>

                    <h3>
                        <?= htmlspecialchars($nome) ?>
                    </h3>

                    <div class="informacoes">

                        <div>

                            <span>
                                Computadores
                            </span>

                            <strong>
                                <?= $total ?>
                            </strong>

                        </div>

                        <div>

                            <span>
                                Funcionando
                            </span>

                            <strong>
                                <?= $funcionando ?>
                            </strong>

                        </div>

                    </div>

                    <?php if ($reservado): ?>

                        <span class="botao botao-desabilitado">
                            Laboratório reservado
                        </span>

                    <?php else: ?>

                        <a
                            href="reservas.php?laboratorio=<?= $id ?>"
                            class="botao"
                        >
                            Fazer reserva
                        </a>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</main>

</body>

</html>