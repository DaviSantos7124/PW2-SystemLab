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

/* ===== - Alterar Status do Computador - ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idComputador = isset($_POST['id_computador'])
        ? (int) $_POST['id_computador']
        : 0;

    $status = trim($_POST['status'] ?? '');

    $statusPermitidos = [
        'Funcionando',
        'Não funcionando',
        'Manutenção'
    ];

    if (
        $idComputador > 0 &&
        in_array($status, $statusPermitidos, true)
    ) {

        $sqlAtualizar = "
            UPDATE tb_computadores
            SET ds_status = ?
            WHERE cd_computador = ?
        ";

        $stmt = $conexao->prepare($sqlAtualizar);

        $stmt->bind_param(
            'si',
            $status,
            $idComputador
        );

        $stmt->execute();

        $stmt->close();
    }

    header('Location: computadores.php');
    exit;
}

/* ===== - Buscar Laboratórios e Computadores - ===== */

$sql = "
    SELECT
        s.cd_sala,
        s.nm_sala,
        c.cd_computador,
        c.nm_computador,
        c.ds_status
    FROM tb_salas s
    LEFT JOIN tb_computadores c
        ON c.id_sala = s.cd_sala
    ORDER BY
        s.cd_sala,
        c.cd_computador
";

$resultado = $conexao->query($sql);

$laboratorios = [];

if ($resultado) {

    while ($linha = $resultado->fetch_assoc()) {

        $idSala = $linha['cd_sala'];

        if (!isset($laboratorios[$idSala])) {

            $laboratorios[$idSala] = [
                'nome' => $linha['nm_sala'],
                'computadores' => []
            ];
        }

        if ($linha['cd_computador'] !== null) {

            $laboratorios[$idSala]['computadores'][] = [
                'id' => $linha['cd_computador'],
                'nome' => $linha['nm_computador'],
                'status' => $linha['ds_status']
            ];
        }
    }
}

/* ===== - Resumo - ===== */

$totalLaboratorios = count($laboratorios);

$totalComputadores = 0;
$totalFuncionando = 0;

foreach ($laboratorios as $laboratorio) {

    $totalComputadores += count($laboratorio['computadores']);

    foreach ($laboratorio['computadores'] as $computador) {

        if ($computador['status'] === 'Funcionando') {
            $totalFuncionando++;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Computadores - SystemLab</title>

    <link
        rel="stylesheet"
        href="css/computadores.css"
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

            <div>

                <h2>
                    Computadores
                </h2>

                <p>
                    Gerencie os computadores disponíveis nos laboratórios.
                </p>

            </div>

        </div>

        <!-- Resumo -->

        <section class="resumo">

            <div class="resumo-card">

                <span class="numero">
                    <?= $totalLaboratorios ?>
                </span>

                <span class="texto">
                    Laboratórios
                </span>

            </div>

            <div class="resumo-card">

                <span class="numero">
                    <?= $totalComputadores ?>
                </span>

                <span class="texto">
                    Computadores
                </span>

            </div>

            <div class="resumo-card">

                <span class="numero">
                    <?= $totalFuncionando ?>
                </span>

                <span class="texto">
                    Funcionando
                </span>

            </div>

        </section>

        <!-- Laboratórios -->

        <section class="laboratorios">

            <?php foreach ($laboratorios as $laboratorio): ?>

                <?php

                $totalLab = count(
                    $laboratorio['computadores']
                );

                $funcionandoLab = 0;

                foreach (
                    $laboratorio['computadores']
                    as $computador
                ) {

                    if (
                        $computador['status'] === 'Funcionando'
                    ) {
                        $funcionandoLab++;
                    }
                }

                ?>

                <div class="laboratorio">

                    <div class="laboratorio-topo">

                        <div>

                            <h3>
                                <?= htmlspecialchars($laboratorio['nome']) ?>
                            </h3>

                            <p>
                                <?= $totalLab ?> computadores
                            </p>

                        </div>

                        <?php if (
                            $funcionandoLab === $totalLab &&
                            $totalLab > 0
                        ): ?>

                            <span class="status-lab">
                                Disponível
                            </span>

                        <?php else: ?>

                            <span class="status-lab">
                                Atenção
                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="computadores-grid">

                        <?php foreach (
                            $laboratorio['computadores']
                            as $computador
                        ): ?>

                            <div class="computador">

                                <div class="icone">
                                    💻
                                </div>

                                <div class="computador-info">

                                    <strong>
                                        <?= htmlspecialchars($computador['nome']) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars($computador['status']) ?>
                                    </span>

                                </div>

                                <?php if (
                                    $computador['status'] === 'Funcionando'
                                ): ?>

                                    <span class="status-funcionando"></span>

                                <?php elseif (
                                    $computador['status'] === 'Manutenção'
                                ): ?>

                                    <span
                                        class="status-funcionando"
                                        style="background: #f59e0b;"
                                    ></span>

                                <?php else: ?>

                                    <span
                                        class="status-funcionando"
                                        style="background: #ef4444;"
                                    ></span>

                                <?php endif; ?>

                                <form
                                    method="POST"
                                    style="
                                        margin-top: 8px;
                                        width: 100%;
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="id_computador"
                                        value="<?= $computador['id'] ?>"
                                    >

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        style="
                                            width: 100%;
                                            max-width: 100%;
                                            box-sizing: border-box;
                                            padding: 5px 6px;
                                            font-size: 12px;
                                            border-radius: 6px;
                                            border: 1px solid #ccc;
                                        "
                                    >

                                        <option
                                            value="Funcionando"
                                            <?= $computador['status'] === 'Funcionando' ? 'selected' : '' ?>
                                        >
                                            Funcionando
                                        </option>

                                        <option
                                            value="Não funcionando"
                                            <?= $computador['status'] === 'Não funcionando' ? 'selected' : '' ?>
                                        >
                                            Não funcionando
                                        </option>

                                        <option
                                            value="Manutenção"
                                            <?= $computador['status'] === 'Manutenção' ? 'selected' : '' ?>
                                        >
                                            Manutenção
                                        </option>

                                    </select>

                                </form>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </section>

    </main>

</body>

</html>