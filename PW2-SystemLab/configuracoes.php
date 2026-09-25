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

/* ===== - Buscar Informações do Sistema - ===== */

// Total de laboratórios
$sqlLaboratorios = "
    SELECT COUNT(*) AS total
    FROM tb_salas
";

$resultadoLaboratorios = $conexao->query($sqlLaboratorios);

$totalLaboratorios = 0;

if ($resultadoLaboratorios) {

    $dados = $resultadoLaboratorios->fetch_assoc();

    $totalLaboratorios = (int) $dados['total'];
}

// Total de computadores
$sqlComputadores = "
    SELECT COUNT(*) AS total
    FROM tb_computadores
";

$resultadoComputadores = $conexao->query($sqlComputadores);

$totalComputadores = 0;

if ($resultadoComputadores) {

    $dados = $resultadoComputadores->fetch_assoc();

    $totalComputadores = (int) $dados['total'];
}

// Maior capacidade de computadores em um laboratório
$sqlCapacidade = "
    SELECT MAX(nr_computadores) AS capacidade
    FROM tb_salas
";

$resultadoCapacidade = $conexao->query($sqlCapacidade);

$capacidadeLaboratorio = 0;

if ($resultadoCapacidade) {

    $dados = $resultadoCapacidade->fetch_assoc();

    $capacidadeLaboratorio = (int) $dados['capacidade'];
}

// Total de professores
$sqlProfessores = "
    SELECT COUNT(*) AS total
    FROM tb_professores
";

$resultadoProfessores = $conexao->query($sqlProfessores);

$totalProfessores = 0;

if ($resultadoProfessores) {

    $dados = $resultadoProfessores->fetch_assoc();

    $totalProfessores = (int) $dados['total'];
}

// Total de matérias
$sqlMaterias = "
    SELECT COUNT(*) AS total
    FROM tb_materias
";

$resultadoMaterias = $conexao->query($sqlMaterias);

$totalMaterias = 0;

if ($resultadoMaterias) {

    $dados = $resultadoMaterias->fetch_assoc();

    $totalMaterias = (int) $dados['total'];
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

    <title>Configurações - SystemLab</title>

    <link
        rel="stylesheet"
        href="css/configuracoes.css"
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
            href="index.php"
            class="voltar"
        >
            ← Voltar para o início
        </a>

        <div class="titulo">

            <h2>
                Configurações
            </h2>

            <p>
                Gerencie as configurações do sistema.
            </p>

        </div>

        <section class="configuracoes">

            <!-- Conta -->

            <div class="config-card">

                <div class="config-icon">
                    👤
                </div>

                <div class="config-info">

                    <h3>
                        Conta do administrador
                    </h3>

                    <p>
                        Informações da conta que está conectada ao sistema.
                    </p>

                </div>

                <div class="config-dados">

                    <div>

                        <span>
                            Nome
                        </span>

                        <strong>
                            <?= htmlspecialchars($_SESSION['usuario']) ?>
                        </strong>

                    </div>

                    <div>

                        <span>
                            Tipo de usuário
                        </span>

                        <strong>
                            Administrador
                        </strong>

                    </div>

                </div>

            </div>

            <!-- Sistema -->

            <div class="config-card">

                <div class="config-icon">
                    ⚙️
                </div>

                <div class="config-info">

                    <h3>
                        Configurações do sistema
                    </h3>

                    <p>
                        Informações gerais sobre o funcionamento do SystemLab.
                    </p>

                </div>

                <div class="config-list">

                    <div class="config-item">

                        <div>

                            <strong>
                                Nome do sistema
                            </strong>

                            <span>
                                SystemLab
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Capacidade dos laboratórios
                            </strong>

                            <span>
                                <?= $capacidadeLaboratorio ?>
                                computadores por laboratório
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Total de laboratórios
                            </strong>

                            <span>
                                <?= $totalLaboratorios ?>
                                laboratórios
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Total de computadores
                            </strong>

                            <span>
                                <?= $totalComputadores ?>
                                computadores
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Total de professores
                            </strong>

                            <span>
                                <?= $totalProfessores ?>
                                professores
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Total de matérias
                            </strong>

                            <span>
                                <?= $totalMaterias ?>
                                matérias
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Reservas -->

            <div class="config-card">

                <div class="config-icon">
                    📅
                </div>

                <div class="config-info">

                    <h3>
                        Regras de reservas
                    </h3>

                    <p>
                        Regras utilizadas para controlar as reservas dos laboratórios.
                    </p>

                </div>

                <div class="config-list">

                    <div class="config-item">

                        <div>

                            <strong>
                                Capacidade por laboratório
                            </strong>

                            <span>
                                <?= $capacidadeLaboratorio ?>
                                computadores por laboratório
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Escolha dos laboratórios
                            </strong>

                            <span>
                                O professor escolhe os laboratórios que deseja reservar.
                            </span>

                        </div>

                    </div>

                    <div class="config-item">

                        <div>

                            <strong>
                                Conflito de horário
                            </strong>

                            <span>
                                O mesmo laboratório não pode ter duas reservas no mesmo horário.
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Sobre -->

            <div class="config-card">

                <div class="config-icon">
                    ℹ️
                </div>

                <div class="config-info">

                    <h3>
                        Sobre o SystemLab
                    </h3>

                    <p>
                        Informações sobre o projeto.
                    </p>

                </div>

                <div class="sobre">

                    <strong>
                        SystemLab
                    </strong>

                    <p>
                        Sistema desenvolvido para facilitar o gerenciamento
                        e a reserva dos laboratórios de informática da ETEC.
                    </p>

                    <span>
                        Versão 1.0
                    </span>

                </div>

            </div>

        </section>

    </main>

</body>

</html>