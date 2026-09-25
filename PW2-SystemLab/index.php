<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/conexao.php';

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipo'];

$turma = $_SESSION['turma'] ?? '';

/* ===== - Informações da Escola - ===== */

$sql_escola = "SELECT * FROM tb_escolas LIMIT 1";
$resultado_escola = $conexao->query($sql_escola);

$escola = $resultado_escola->fetch_assoc();

/* ===== - Próximas aulas do aluno - ===== */

$aulas = [];

if ($tipo === 'aluno' && $turma !== '') {

    $sql_aulas = "SELECT
                    r.dt_reserva,
                    r.hr_inicio,
                    r.hr_fim,
                    r.ds_turma,
                    r.nr_alunos,
                    r.ds_finalidade,
                    p.nm_professor,
                    m.sg_materia,
                    m.nm_materia,
                    s.nm_sala
                FROM tb_reservas r

                INNER JOIN tb_professores p
                    ON p.cd_professor = r.id_professor

                INNER JOIN tb_materias m
                    ON m.cd_materia = r.id_materia

                INNER JOIN tb_reservas_salas rs
                    ON rs.id_reserva = r.cd_reserva

                INNER JOIN tb_salas s
                    ON s.cd_sala = rs.id_sala

                WHERE r.ds_turma = ?
                AND r.dt_reserva >= CURDATE()

                ORDER BY r.dt_reserva ASC, r.hr_inicio ASC
                LIMIT 5";

    $stmt_aulas = $conexao->prepare($sql_aulas);

    if ($stmt_aulas) {

        $stmt_aulas->bind_param("s", $turma);

        $stmt_aulas->execute();

        $resultado_aulas = $stmt_aulas->get_result();

        while ($aula = $resultado_aulas->fetch_assoc()) {

            $aulas[] = $aula;

        }

        $stmt_aulas->close();
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
        Dashboard - SystemLab
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
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

                    <?php if ($tipo === 'administrador'): ?>

                        Administrador

                    <?php elseif ($tipo === 'professor'): ?>

                        Professor

                    <?php else: ?>

                        Aluno

                    <?php endif; ?>

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

        <section class="boas-vindas">

            <div>

                <h2>

                    Olá,
                    <?= htmlspecialchars($usuario) ?>!

                </h2>

                <?php if ($tipo === 'aluno'): ?>

                    <p>
                        Bem-vindo ao SystemLab.
                        Aqui você pode consultar as aulas
                        e os laboratórios da sua turma.
                    </p>

                    <div class="turma">

                        Turma:
                        <strong>
                            <?= htmlspecialchars($turma) ?>
                        </strong>

                    </div>

                <?php elseif ($tipo === 'professor'): ?>

                    <p>
                        Bem-vindo ao SystemLab.
                        Aqui você pode gerenciar suas reservas
                        e consultar os laboratórios.
                    </p>

                <?php else: ?>

                    <p>
                        Bem-vindo ao SystemLab.
                        Aqui você pode gerenciar
                        e consultar os laboratórios da escola.
                    </p>

                <?php endif; ?>

            </div>

        </section>

        <!-- ===== - Aluno - ===== -->
        
        <?php if ($tipo === 'aluno'): ?>

            <section class="aulas-section">

                <div class="titulo-section">

                    <h2>
                        Suas próximas aulas
                    </h2>

                    <span>
                        <?= count($aulas) ?> aula(s) encontrada(s)
                    </span>

                </div>

                <?php if (count($aulas) > 0): ?>

                    <div class="aulas-lista">

                        <?php foreach ($aulas as $aula): ?>

                            <div class="aula-card">

                                <div class="aula-data">

                                    <strong>
                                        <?= date(
                                            'd/m',
                                            strtotime($aula['dt_reserva'])
                                        ) ?>
                                    </strong>

                                    <span>
                                        <?= date(
                                            'Y',
                                            strtotime($aula['dt_reserva'])
                                        ) ?>
                                    </span>

                                </div>

                                <div class="aula-info">

                                    <h3>

                                        <?= htmlspecialchars(
                                            $aula['sg_materia']
                                        ) ?>

                                        -
                                        <?= htmlspecialchars(
                                            $aula['nm_materia']
                                        ) ?>

                                    </h3>

                                    <p>

                                        <strong>
                                            Horário:
                                        </strong>

                                        <?= date(
                                            'H:i',
                                            strtotime($aula['hr_inicio'])
                                        ) ?>

                                        às

                                        <?= date(
                                            'H:i',
                                            strtotime($aula['hr_fim'])
                                        ) ?>

                                    </p>

                                    <p>

                                        <strong>
                                            Professor:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $aula['nm_professor']
                                        ) ?>

                                    </p>

                                    <p>

                                        <strong>
                                            Laboratório:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $aula['nm_sala']
                                        ) ?>

                                    </p>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="sem-aulas">

                        <div class="sem-aulas-icone">
                            📅
                        </div>

                        <h3>
                            Nenhuma aula encontrada
                        </h3>

                        <p>
                            Ainda não existem reservas
                            cadastradas para a sua turma.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        <!-- ===== - Professor / Administrador - ===== -->

        <?php else: ?>

            <section class="cards">

                <a
                    href="reservas.php"
                    class="card"
                >

                    <div class="icone">
                        📅
                    </div>

                    <div>

                        <h3>
                            Reservas
                        </h3>

                        <p>
                            Consultar e realizar reservas
                            dos laboratórios.
                        </p>

                    </div>

                </a>

                <a
                    href="laboratorios.php"
                    class="card"
                >

                    <div class="icone">
                        💻
                    </div>

                    <div>

                        <h3>
                            Laboratórios
                        </h3>

                        <p>
                            Visualizar os laboratórios
                            e computadores.
                        </p>

                    </div>

                </a>

                <?php if ($tipo === 'administrador'): ?>

                    <a
                        href="professores.php"
                        class="card"
                    >

                        <div class="icone">
                            👨‍🏫
                        </div>

                        <div>

                            <h3>
                                Professores
                            </h3>

                            <p>
                                Gerenciar os professores
                                cadastrados.
                            </p>

                        </div>

                    </a>

                    <a
                        href="materias.php"
                        class="card"
                    >

                        <div class="icone">
                            📚
                        </div>

                        <div>

                            <h3>
                                Matérias
                            </h3>

                            <p>
                                Gerenciar as matérias
                                do sistema.
                            </p>

                        </div>

                    </a>

                    <a
                        href="computadores.php"
                        class="card"
                    >

                        <div class="icone">
                            🖥️
                        </div>

                        <div>

                            <h3>
                                Computadores
                            </h3>

                            <p>
                                Gerenciar os computadores
                                dos laboratórios.
                            </p>

                        </div>

                    </a>

                    <a
                        href="configuracoes.php"
                        class="card"
                    >

                        <div class="icone">
                            ⚙️
                        </div>

                        <div>

                            <h3>
                                Configurações
                            </h3>

                            <p>
                                Configurações do sistema.
                            </p>

                        </div>

                    </a>

                <?php endif; ?>

            </section>

        <?php endif; ?>


        <!-- ===== - Informações da Escola - ===== -->

        <?php if ($escola): ?>

            <section class="escola-section">

                <div class="escola-card">


                    <div class="escola-icon">
                        🏫
                    </div>

                    <div class="escola-info">

                        <span>
                            INSTITUIÇÃO
                        </span>

                        <h2>
                            <?= htmlspecialchars(
                                $escola['nm_escola']
                            ) ?>
                        </h2>

                        <p>

                            <?= htmlspecialchars(
                                $escola['ds_endereco']
                            ) ?>

                            —

                            <?= htmlspecialchars(
                                $escola['nm_bairro']
                            ) ?>,

                            <?= htmlspecialchars(
                                $escola['nm_cidade']
                            ) ?>

                            -

                            <?= htmlspecialchars(
                                $escola['sg_uf']
                            ) ?>

                        </p>

                    </div>

                    <div class="escola-contato">

                        <div>

                            <span>
                                TELEFONE
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $escola['nr_telefone']
                                    ?: 'Não informado'
                                ) ?>

                            </strong>

                        </div>

                        <div>

                            <span>
                                E-MAIL
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $escola['nm_email']
                                    ?: 'Não informado'
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </section>

        <?php endif; ?>

    </main>

</body>

</html>