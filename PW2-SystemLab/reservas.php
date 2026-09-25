<?php

session_start();

require_once 'includes/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipo'];

$mensagem = '';
$erro = '';

/* ===== - Laboratório vindo da página de laboratórios - ===== */

$laboratorioUrl = isset($_GET['laboratorio'])
    ? (int) $_GET['laboratorio']
    : 0;

/* ===== - Dados do formulário - ===== */

$dataSelecionada = $_POST['data'] ?? '';
$inicioSelecionado = $_POST['inicio'] ?? '';
$fimSelecionado = $_POST['fim'] ?? '';

/* ===== - ID do Professor logado - ===== */

$professor_id = $_SESSION['professor_id'] ?? 0;

/* ===== - Cancelar Reserva - ===== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['cancelar_reserva'])
) {

    $id_reserva =
        (int) ($_POST['id_reserva'] ?? 0);

    if ($id_reserva <= 0) {

        $erro =
            'Reserva inválida.';

    } else {

        if ($tipo === 'administrador') {

            $sqlVerifica = "
                SELECT
                    cd_reserva
                FROM tb_reservas
                WHERE cd_reserva = ?
            ";

            $stmtVerifica =
                $conexao->prepare($sqlVerifica);

            if ($stmtVerifica) {

                $stmtVerifica->bind_param(
                    "i",
                    $id_reserva
                );

                $stmtVerifica->execute();

                $resultadoVerifica =
                    $stmtVerifica->get_result();

                $reservaExiste =
                    $resultadoVerifica->num_rows > 0;

                $stmtVerifica->close();

            } else {

                $reservaExiste = false;
            }

        } else {

            $sqlVerifica = "
                SELECT
                    cd_reserva
                FROM tb_reservas
                WHERE cd_reserva = ?
                AND id_professor = ?
            ";

            $stmtVerifica =
                $conexao->prepare($sqlVerifica);

            if ($stmtVerifica) {

                $stmtVerifica->bind_param(
                    "ii",
                    $id_reserva,
                    $professor_id
                );

                $stmtVerifica->execute();

                $resultadoVerifica =
                    $stmtVerifica->get_result();

                $reservaExiste =
                    $resultadoVerifica->num_rows > 0;

                $stmtVerifica->close();

            } else {

                $reservaExiste = false;
            }
        }

        if (!$reservaExiste) {

            $erro =
                'Você não tem permissão para cancelar esta reserva.';

        } else {

            $conexao->begin_transaction();

            try {

                /* Remove os laboratórios vinculados */

                $sqlDeleteSalas = "
                    DELETE FROM tb_reservas_salas
                    WHERE id_reserva = ?
                ";

                $stmtDeleteSalas =
                    $conexao->prepare($sqlDeleteSalas);

                if (!$stmtDeleteSalas) {

                    throw new Exception(
                        'Erro ao preparar o cancelamento dos laboratórios: ' .
                        $conexao->error
                    );
                }

                $stmtDeleteSalas->bind_param(
                    "i",
                    $id_reserva
                );

                if (!$stmtDeleteSalas->execute()) {

                    throw new Exception(
                        'Erro ao remover os laboratórios da reserva: ' .
                        $stmtDeleteSalas->error
                    );
                }

                $stmtDeleteSalas->close();

                /* Remove a reserva principal */

                $sqlDeleteReserva = "
                    DELETE FROM tb_reservas
                    WHERE cd_reserva = ?
                ";

                $stmtDeleteReserva =
                    $conexao->prepare($sqlDeleteReserva);

                if (!$stmtDeleteReserva) {

                    throw new Exception(
                        'Erro ao preparar o cancelamento da reserva: ' .
                        $conexao->error
                    );
                }

                $stmtDeleteReserva->bind_param(
                    "i",
                    $id_reserva
                );

                if (!$stmtDeleteReserva->execute()) {

                    throw new Exception(
                        'Erro ao cancelar a reserva: ' .
                        $stmtDeleteReserva->error
                    );
                }

                $stmtDeleteReserva->close();

                $conexao->commit();

                $mensagem =
                    'Reserva cancelada com sucesso!';

            } catch (Exception $e) {

                $conexao->rollback();

                $erro =
                    $e->getMessage();
            }
        }
    }
}

/* ===== - Buscar matérias do Professor logado - ===== */

$materiasProfessor = [];

if ((int) $professor_id > 0) {

    $sqlMaterias = "
        SELECT
            m.cd_materia,
            m.sg_materia,
            m.nm_materia
        FROM tb_materias m

        INNER JOIN tb_professores_materias pm
            ON pm.id_materia = m.cd_materia

        WHERE pm.id_professor = ?

        ORDER BY m.nm_materia
    ";

    $stmtMaterias =
        $conexao->prepare($sqlMaterias);

    if ($stmtMaterias) {

        $stmtMaterias->bind_param(
            "i",
            $professor_id
        );

        if ($stmtMaterias->execute()) {

            $resultadoMaterias =
                $stmtMaterias->get_result();

            while (
                $linha =
                $resultadoMaterias->fetch_assoc()
            ) {

                $materiasProfessor[] = [
                    'id' =>
                        (int) $linha['cd_materia'],

                    'sigla' =>
                        $linha['sg_materia'],

                    'nome' =>
                        $linha['nm_materia']
                ];
            }
        }

        $stmtMaterias->close();
    }
}

/* ===== - Buscar Laboratórios do Banco - ===== */

$sqlLaboratorios = "
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

$resultadoLaboratorios =
    $conexao->query($sqlLaboratorios);

$laboratorios = [];

if ($resultadoLaboratorios) {

    while (
        $linha =
        $resultadoLaboratorios->fetch_assoc()
    ) {

        $laboratorios[] = [
            'id' =>
                (int) $linha['cd_sala'],

            'nome' =>
                $linha['nm_sala'],

            'capacidade' =>
                (int) $linha['nr_computadores'],

            'total_computadores' =>
                (int) $linha['total_computadores'],

            'computadores_funcionando' =>
                (int) $linha['computadores_funcionando']
        ];
    }
}

/* ===== - Verificar Laboratórios já Reservados - ===== */

$laboratoriosReservados = [];
$dadosReservasLaboratorios = [];

/* ===== - Sem Data e Horário - ===== */

if (
    $dataSelecionada === '' ||
    $inicioSelecionado === '' ||
    $fimSelecionado === '' ||
    $fimSelecionado <= $inicioSelecionado
) {

    $sqlReservados = "
        SELECT
            rs.id_sala,
            p.nm_professor,
            m.nm_materia,
            r.dt_reserva,
            r.hr_inicio,
            r.hr_fim
        FROM tb_reservas_salas rs

        INNER JOIN tb_reservas r
            ON r.cd_reserva = rs.id_reserva

        INNER JOIN tb_professores p
            ON p.cd_professor = r.id_professor

        INNER JOIN tb_materias m
            ON m.cd_materia = r.id_materia

        WHERE r.dt_reserva >= CURDATE()

        ORDER BY
            r.dt_reserva ASC,
            r.hr_inicio ASC
    ";

    $resultadoReservados =
        $conexao->query($sqlReservados);

    if ($resultadoReservados) {

        while (
            $linha =
            $resultadoReservados->fetch_assoc()
        ) {

            $idSala =
                (int) $linha['id_sala'];

            if (
                !isset(
                    $dadosReservasLaboratorios[$idSala]
                )
            ) {

                $dadosReservasLaboratorios[$idSala] = [

                    'professor' =>
                        $linha['nm_professor'],

                    'materia' =>
                        $linha['nm_materia'],

                    'data' =>
                        $linha['dt_reserva'],

                    'inicio' =>
                        $linha['hr_inicio'],

                    'fim' =>
                        $linha['hr_fim']
                ];

                $laboratoriosReservados[] =
                    $idSala;
            }
        }
    }

} else {

    /* Verifica conflitos para o período selecionado */

    foreach ($laboratorios as $laboratorio) {

        $id_sala =
            $laboratorio['id'];

        $sqlConflito = "
            SELECT
                r.cd_reserva,
                p.nm_professor,
                m.nm_materia,
                r.dt_reserva,
                r.hr_inicio,
                r.hr_fim
            FROM tb_reservas r

            INNER JOIN tb_reservas_salas rs
                ON rs.id_reserva = r.cd_reserva

            INNER JOIN tb_professores p
                ON p.cd_professor = r.id_professor

            INNER JOIN tb_materias m
                ON m.cd_materia = r.id_materia

            WHERE rs.id_sala = ?
            AND r.dt_reserva = ?
            AND r.hr_inicio < ?
            AND r.hr_fim > ?

            ORDER BY
                r.hr_inicio ASC

            LIMIT 1
        ";

        $stmtConflito =
            $conexao->prepare($sqlConflito);

        if ($stmtConflito) {

            $stmtConflito->bind_param(
                "isss",
                $id_sala,
                $dataSelecionada,
                $fimSelecionado,
                $inicioSelecionado
            );

            if ($stmtConflito->execute()) {

                $resultadoConflito =
                    $stmtConflito->get_result();

                if (
                    $resultadoConflito->num_rows > 0
                ) {

                    $linha =
                        $resultadoConflito->fetch_assoc();

                    $laboratoriosReservados[] =
                        $id_sala;

                    $dadosReservasLaboratorios[$id_sala] = [

                        'professor' =>
                            $linha['nm_professor'],

                        'materia' =>
                            $linha['nm_materia'],

                        'data' =>
                            $linha['dt_reserva'],

                        'inicio' =>
                            $linha['hr_inicio'],

                        'fim' =>
                            $linha['hr_fim']
                    ];
                }
            }

            $stmtConflito->close();
        }
    }
}

/* ===== - Realizar Reserva - ===== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['cancelar_reserva'])
) {

    $data =
        $_POST['data'] ?? '';

    $turma =
        $_POST['turma'] ?? '';

    $inicio =
        $_POST['inicio'] ?? '';

    $fim =
        $_POST['fim'] ?? '';

    $alunos =
        (int) ($_POST['alunos'] ?? 0);

    $materia =
        (int) ($_POST['materia'] ?? 0);

    $laboratoriosSelecionados =
        $_POST['laboratorios'] ?? [];

    $professor_id =
        $_SESSION['professor_id'] ?? 0;

    if ((int) $professor_id <= 0) {

        $erro =
            'O usuário logado não está vinculado a um professor.';

    } elseif (
        $data === '' ||
        $turma === '' ||
        $inicio === '' ||
        $fim === '' ||
        $alunos <= 0 ||
        $materia <= 0
    ) {

        $erro =
            'Preencha todos os campos.';

    } elseif (empty($laboratoriosSelecionados)) {

        $erro =
            'Selecione pelo menos um laboratório.';

    } elseif ($fim <= $inicio) {

        $erro =
            'O horário de término deve ser maior que o horário de início.';

    } else {

        /* Verifica se a matéria pertence ao professor */

        $materiaValida = false;
        $nomeMateria = '';

        foreach (
            $materiasProfessor
            as $materiaProfessor
        ) {

            if (
                $materiaProfessor['id'] ===
                $materia
            ) {

                $materiaValida = true;

                $nomeMateria =
                    $materiaProfessor['nome'];

                break;
            }
        }

        if (!$materiaValida) {

            $erro =
                'A matéria selecionada não pertence ao professor.';
        }

        if ($erro === '') {

            $laboratoriosSelecionados =
                array_map(
                    'intval',
                    $laboratoriosSelecionados
                );

            $laboratoriosSelecionados =
                array_unique(
                    $laboratoriosSelecionados
                );

            /* Verifica se os laboratórios existem */

            $idsValidos = [];

            foreach (
                $laboratorios
                as $laboratorio
            ) {

                if (
                    in_array(
                        $laboratorio['id'],
                        $laboratoriosSelecionados,
                        true
                    )
                ) {

                    $idsValidos[] =
                        $laboratorio['id'];
                }
            }

            if (empty($idsValidos)) {

                $erro =
                    'Os laboratórios selecionados não são válidos.';

            } else {

                $laboratoriosSelecionados =
                    $idsValidos;

                /* Verifica conflitos */

                $conflito = false;
                $laboratorioConflito = '';

                foreach (
                    $laboratoriosSelecionados
                    as $id_sala
                ) {

                    $sql = "
                        SELECT
                            r.cd_reserva,
                            s.nm_sala,
                            p.nm_professor
                        FROM tb_reservas r

                        INNER JOIN tb_reservas_salas rs
                            ON rs.id_reserva = r.cd_reserva

                        INNER JOIN tb_salas s
                            ON s.cd_sala = rs.id_sala

                        INNER JOIN tb_professores p
                            ON p.cd_professor = r.id_professor

                        WHERE rs.id_sala = ?
                        AND r.dt_reserva = ?
                        AND r.hr_inicio < ?
                        AND r.hr_fim > ?
                    ";

                    $stmt =
                        $conexao->prepare($sql);

                    if (!$stmt) {

                        $erro =
                            'Erro ao verificar disponibilidade: ' .
                            $conexao->error;

                        break;
                    }

                    $stmt->bind_param(
                        "isss",
                        $id_sala,
                        $data,
                        $fim,
                        $inicio
                    );

                    if (!$stmt->execute()) {

                        $erro =
                            'Erro ao verificar disponibilidade: ' .
                            $stmt->error;

                        $stmt->close();

                        break;
                    }

                    $resultado =
                        $stmt->get_result();

                    if (
                        $resultado->num_rows > 0
                    ) {

                        $dadosConflito =
                            $resultado->fetch_assoc();

                        $conflito = true;

                        $laboratorioConflito =
                            $dadosConflito['nm_sala'];

                        $stmt->close();

                        break;
                    }

                    $stmt->close();
                }

                if (
                    $erro === '' &&
                    $conflito
                ) {

                    $erro =
                        'O ' . $laboratorioConflito .
                        ' já está reservado nesse dia e horário. ' .
                        'Escolha outro horário ou outro laboratório.';
                }

                /* Salva a reserva */

                if ($erro === '') {

                    $conexao->begin_transaction();

                    try {

                        $sql = "
                            INSERT INTO tb_reservas
                            (
                                dt_reserva,
                                hr_inicio,
                                hr_fim,
                                ds_turma,
                                nr_alunos,
                                ds_finalidade,
                                id_professor,
                                id_materia
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ";

                        $stmt =
                            $conexao->prepare($sql);

                        if (!$stmt) {

                            throw new Exception(
                                'Erro ao preparar a reserva: ' .
                                $conexao->error
                            );
                        }

                        $stmt->bind_param(
                            "ssssisis",
                            $data,
                            $inicio,
                            $fim,
                            $turma,
                            $alunos,
                            $nomeMateria,
                            $professor_id,
                            $materia
                        );

                        if (!$stmt->execute()) {

                            throw new Exception(
                                'Erro ao salvar a reserva: ' .
                                $stmt->error
                            );
                        }

                        $id_reserva =
                            $conexao->insert_id;

                        $stmt->close();

                        /* Salva os laboratórios */

                        $sql = "
                            INSERT INTO tb_reservas_salas
                            (
                                id_reserva,
                                id_sala
                            )
                            VALUES (?, ?)
                        ";

                        $stmt =
                            $conexao->prepare($sql);

                        if (!$stmt) {

                            throw new Exception(
                                'Erro ao preparar os laboratórios: ' .
                                $conexao->error
                            );
                        }

                        foreach (
                            $laboratoriosSelecionados
                            as $id_sala
                        ) {

                            $stmt->bind_param(
                                "ii",
                                $id_reserva,
                                $id_sala
                            );

                            if (!$stmt->execute()) {

                                throw new Exception(
                                    'Erro ao salvar o laboratório: ' .
                                    $stmt->error
                                );
                            }
                        }

                        $stmt->close();

                        $conexao->commit();

                        $mensagem =
                            'Reserva realizada com sucesso!';

                        $_POST = [];

                        $dataSelecionada = '';
                        $inicioSelecionado = '';
                        $fimSelecionado = '';

                        /* Atualiza laboratórios reservados */

                        $laboratoriosReservados = [];
                        $dadosReservasLaboratorios = [];

                        $sqlReservados = "
                            SELECT
                                rs.id_sala,
                                p.nm_professor,
                                m.nm_materia,
                                r.dt_reserva,
                                r.hr_inicio,
                                r.hr_fim
                            FROM tb_reservas_salas rs

                            INNER JOIN tb_reservas r
                                ON r.cd_reserva = rs.id_reserva

                            INNER JOIN tb_professores p
                                ON p.cd_professor = r.id_professor

                            INNER JOIN tb_materias m
                                ON m.cd_materia = r.id_materia

                            WHERE r.dt_reserva >= CURDATE()

                            ORDER BY
                                r.dt_reserva ASC,
                                r.hr_inicio ASC
                        ";

                        $resultadoReservados =
                            $conexao->query(
                                $sqlReservados
                            );

                        if ($resultadoReservados) {

                            while (
                                $linha =
                                $resultadoReservados->fetch_assoc()
                            ) {

                                $idSala =
                                    (int) $linha['id_sala'];

                                if (
                                    !isset(
                                        $dadosReservasLaboratorios[
                                            $idSala
                                        ]
                                    )
                                ) {

                                    $dadosReservasLaboratorios[
                                        $idSala
                                    ] = [

                                        'professor' =>
                                            $linha['nm_professor'],

                                        'materia' =>
                                            $linha['nm_materia'],

                                        'data' =>
                                            $linha['dt_reserva'],

                                        'inicio' =>
                                            $linha['hr_inicio'],

                                        'fim' =>
                                            $linha['hr_fim']
                                    ];

                                    $laboratoriosReservados[] =
                                        $idSala;
                                }
                            }
                        }

                    } catch (Exception $e) {

                        $conexao->rollback();

                        $erro =
                            $e->getMessage();
                    }
                }
            }
        }
    }
}

/* ===== - Laboratórios Marcados - ===== */

$laboratoriosMarcados = [];

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['cancelar_reserva'])
) {

    $laboratoriosMarcados =
        array_map(
            'intval',
            $_POST['laboratorios'] ?? []
        );

} elseif ($laboratorioUrl > 0) {

    $laboratoriosMarcados =
        [$laboratorioUrl];
}

/* ===== - Buscar Reservas para Exibição - ===== */

$reservasLista = [];

if ($tipo === 'administrador') {

    $sqlListaReservas = "
        SELECT
            r.cd_reserva,
            r.dt_reserva,
            r.hr_inicio,
            r.hr_fim,
            r.ds_turma,
            r.nr_alunos,
            r.ds_finalidade,
            p.nm_professor,
            m.sg_materia,
            m.nm_materia,
            GROUP_CONCAT(
                s.nm_sala
                ORDER BY s.cd_sala
                SEPARATOR ', '
            ) AS laboratorios
        FROM tb_reservas r

        INNER JOIN tb_professores p
            ON p.cd_professor = r.id_professor

        INNER JOIN tb_materias m
            ON m.cd_materia = r.id_materia

        INNER JOIN tb_reservas_salas rs
            ON rs.id_reserva = r.cd_reserva

        INNER JOIN tb_salas s
            ON s.cd_sala = rs.id_sala

        WHERE r.dt_reserva >= CURDATE()

        GROUP BY
            r.cd_reserva,
            r.dt_reserva,
            r.hr_inicio,
            r.hr_fim,
            r.ds_turma,
            r.nr_alunos,
            r.ds_finalidade,
            p.nm_professor,
            m.sg_materia,
            m.nm_materia

        ORDER BY
            r.dt_reserva ASC,
            r.hr_inicio ASC
    ";

} else {

    $sqlListaReservas = "
        SELECT
            r.cd_reserva,
            r.dt_reserva,
            r.hr_inicio,
            r.hr_fim,
            r.ds_turma,
            r.nr_alunos,
            r.ds_finalidade,
            p.nm_professor,
            m.sg_materia,
            m.nm_materia,
            GROUP_CONCAT(
                s.nm_sala
                ORDER BY s.cd_sala
                SEPARATOR ', '
            ) AS laboratorios
        FROM tb_reservas r

        INNER JOIN tb_professores p
            ON p.cd_professor = r.id_professor

        INNER JOIN tb_materias m
            ON m.cd_materia = r.id_materia

        INNER JOIN tb_reservas_salas rs
            ON rs.id_reserva = r.cd_reserva

        INNER JOIN tb_salas s
            ON s.cd_sala = rs.id_sala

        WHERE r.id_professor = ?
        AND r.dt_reserva >= CURDATE()

        GROUP BY
            r.cd_reserva,
            r.dt_reserva,
            r.hr_inicio,
            r.hr_fim,
            r.ds_turma,
            r.nr_alunos,
            r.ds_finalidade,
            p.nm_professor,
            m.sg_materia,
            m.nm_materia

        ORDER BY
            r.dt_reserva ASC,
            r.hr_inicio ASC
    ";
}

if ($tipo === 'administrador') {

    $resultadoLista =
        $conexao->query(
            $sqlListaReservas
        );

} else {

    $stmtLista =
        $conexao->prepare(
            $sqlListaReservas
        );

    if ($stmtLista) {

        $stmtLista->bind_param(
            "i",
            $professor_id
        );

        $stmtLista->execute();

        $resultadoLista =
            $stmtLista->get_result();

    } else {

        $resultadoLista = false;
    }
}

if ($resultadoLista) {

    while (
        $reserva =
        $resultadoLista->fetch_assoc()
    ) {

        $reservasLista[] =
            $reserva;
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
        Reservas - SystemLab
    </title>

    <link
        rel="stylesheet"
        href="css/reservas.css"
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

        <div>

            <a
                href="index.php"
                class="voltar"
            >
                ← Voltar
            </a>

            <h2>
                Reservas
            </h2>

            <p>
                Consulte e faça reservas para os laboratórios.
            </p>

        </div>

    </div>

    <section class="reserva-container">

        <div class="titulo-secao">

            <h3>
                Nova reserva
            </h3>

            <p>
                Preencha os dados abaixo para reservar um ou mais laboratórios.
            </p>

        </div>

        <?php if ($erro !== ''): ?>

            <div class="mensagem erro">
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>

        <?php if ($mensagem !== ''): ?>

            <div class="mensagem sucesso">
                <?= htmlspecialchars($mensagem) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="reservas.php"
        >

            <div class="form-grid">

                <div class="campo">

                    <label for="data">
                        Data da reserva
                    </label>

                    <input
                        type="date"
                        id="data"
                        name="data"
                        value="<?= htmlspecialchars($_POST['data'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="turma">
                        Turma
                    </label>

                    <select
                        id="turma"
                        name="turma"
                        required
                    >

                        <option value="">
                            Selecione a turma
                        </option>

                        <?php

                        $turmas = [
                            '1MDS-N',
                            '2MDS-N',
                            '1MAD-N',
                            '2MAD-N',
                            '1FAR-N',
                            '2FAR-N',
                            '3FAR-N',
                            '1MAD',
                            '2MAD',
                            '3MAD',
                            '1MIN',
                            '2MIN',
                            '3MIN',
                            '1MAM',
                            '2MAM',
                            '3MAM'
                        ];

                        foreach (
                            $turmas
                            as $nome_turma
                        ):

                        ?>

                            <option
                                value="<?= htmlspecialchars($nome_turma) ?>"
                                <?= ($_POST['turma'] ?? '') ===
                                    $nome_turma
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= htmlspecialchars($nome_turma) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="campo">

                    <label for="inicio">
                        Horário de início
                    </label>

                    <input
                        type="time"
                        id="inicio"
                        name="inicio"
                        value="<?= htmlspecialchars($_POST['inicio'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="fim">
                        Horário de término
                    </label>

                    <input
                        type="time"
                        id="fim"
                        name="fim"
                        value="<?= htmlspecialchars($_POST['fim'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="alunos">
                        Quantidade de alunos
                    </label>

                    <input
                        type="number"
                        id="alunos"
                        name="alunos"
                        min="1"
                        max="200"
                        placeholder="Ex: 40"
                        value="<?= htmlspecialchars($_POST['alunos'] ?? '') ?>"
                        required
                    >

                    <small>
                        A quantidade de alunos é apenas informativa.
                    </small>

                </div>

                <div class="campo">

                    <label for="materia">
                        Matéria
                    </label>

                    <select
                        id="materia"
                        name="materia"
                        required
                    >

                        <option value="">
                            Selecione a matéria
                        </option>

                        <?php if (empty($materiasProfessor)): ?>

                            <option value="" disabled>
                                Nenhuma matéria vinculada
                            </option>

                        <?php else: ?>

                            <?php foreach (
                                $materiasProfessor
                                as $materiaProfessor
                            ): ?>

                                <option
                                    value="<?= $materiaProfessor['id'] ?>"
                                    <?= (int) ($_POST['materia'] ?? 0) ===
                                        $materiaProfessor['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $materiaProfessor['sigla']
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        $materiaProfessor['nome']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </select>

                    <small>
                        São exibidas somente as matérias vinculadas ao professor.
                    </small>

                </div>

            </div>

            <div class="laboratorios">

                <div class="laboratorios-titulo">

                    <div>

                        <h3>
                            Laboratórios
                        </h3>

                        <p>
                            Selecione os laboratórios que deseja utilizar.
                        </p>

                    </div>

                    <span class="quantidade">
                        <?= count($laboratorios) ?> disponíveis
                    </span>

                </div>

                <div class="laboratorios-grid">

                    <?php if (empty($laboratorios)): ?>

                        <p>
                            Nenhum laboratório cadastrado.
                        </p>

                    <?php else: ?>

                        <?php foreach (
                            $laboratorios
                            as $laboratorio
                        ): ?>

                            <?php

                            $selecionado =
                                in_array(
                                    $laboratorio['id'],
                                    $laboratoriosMarcados,
                                    true
                                );

                            $todosFuncionando =
                                $laboratorio['total_computadores'] > 0 &&
                                $laboratorio['computadores_funcionando'] ===
                                $laboratorio['total_computadores'];

                            $estaReservado =
                                in_array(
                                    $laboratorio['id'],
                                    $laboratoriosReservados,
                                    true
                                );

                            $dadosReserva =
                                $dadosReservasLaboratorios[
                                    $laboratorio['id']
                                ] ?? null;

                            ?>

                            <label
                                class="laboratorio
                                <?= $estaReservado
                                    ? 'indisponivel'
                                    : '' ?>"
                            >

                                <input
                                    type="checkbox"
                                    name="laboratorios[]"
                                    value="<?= $laboratorio['id'] ?>"
                                    <?= $selecionado
                                        ? 'checked'
                                        : '' ?>
                                    <?= $estaReservado
                                        ? 'disabled'
                                        : '' ?>
                                >

                                <div class="lab-info">

                                    <strong>
                                        <?= htmlspecialchars(
                                            $laboratorio['nome']
                                        ) ?>
                                    </strong>

                                    <span>
                                        <?= $laboratorio[
                                            'total_computadores'
                                        ] ?>
                                        computadores
                                    </span>

                                    <?php if (
                                        $estaReservado &&
                                        $dadosReserva
                                    ): ?>

                                        <span class="reserva-info">

                                            Professor:
                                            <?= htmlspecialchars(
                                                $dadosReserva['professor']
                                            ) ?>

                                        </span>

                                        <span class="reserva-info">

                                            Matéria:
                                            <?= htmlspecialchars(
                                                $dadosReserva['materia']
                                            ) ?>

                                        </span>

                                        <span class="reserva-info">

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $dadosReserva['data']
                                                )
                                            ) ?>

                                            •

                                            <?= date(
                                                'H:i',
                                                strtotime(
                                                    $dadosReserva['inicio']
                                                )
                                            ) ?>

                                            às

                                            <?= date(
                                                'H:i',
                                                strtotime(
                                                    $dadosReserva['fim']
                                                )
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>

                                <?php if ($estaReservado): ?>

                                    <span class="status reservado">
                                        Reservado
                                    </span>

                                <?php elseif ($todosFuncionando): ?>

                                    <span class="status disponivel">
                                        Disponível
                                    </span>

                                <?php else: ?>

                                    <span class="status atencao">
                                        Atenção
                                    </span>

                                <?php endif; ?>

                            </label>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

            <div class="aviso">

                <strong>
                    Importante
                </strong>

                <p>
                    Selecione os laboratórios que deseja utilizar.
                    A quantidade de alunos é apenas informativa.
                    O sistema verifica automaticamente se existe conflito
                    de horário antes de realizar a reserva.
                </p>

            </div>

            <div class="acoes">

                <a
                    href="index.php"
                    class="cancelar"
                >
                    Cancelar
                </a>

                <button type="submit">
                    Fazer reserva
                </button>

            </div>

        </form>

    </section>

    <section class="reservas-lista">

        <div class="titulo-secao">

            <h3>
                <?= $tipo === 'administrador'
                    ? 'Reservas do sistema'
                    : 'Minhas reservas'
                ?>
            </h3>

            <p>
                <?= $tipo === 'administrador'
                    ? 'Consulte e gerencie as reservas realizadas pelos professores.'
                    : 'Consulte e cancele suas reservas.'
                ?>
            </p>

        </div>

        <?php if (empty($reservasLista)): ?>

            <div class="sem-reservas">

                <strong>
                    Nenhuma reserva encontrada.
                </strong>

                <span>
                    As reservas realizadas aparecerão aqui.
                </span>

            </div>

        <?php else: ?>

            <div class="reservas-grid">

                <?php foreach (
                    $reservasLista
                    as $reserva
                ): ?>

                    <div class="reserva-card">

                        <div class="reserva-card-topo">

                            <div>

                                <strong>
                                    <?= htmlspecialchars(
                                        $reserva['nm_materia']
                                    ) ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars(
                                        $reserva['ds_turma']
                                    ) ?>
                                </span>

                            </div>

                            <span class="reserva-id">
                                #<?= (int) $reserva['cd_reserva'] ?>
                            </span>

                        </div>

                        <div class="reserva-detalhes">

                            <div>

                                <span>
                                    Matéria
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $reserva['sg_materia']
                                    ) ?>
                                    -
                                    <?= htmlspecialchars(
                                        $reserva['nm_materia']
                                    ) ?>
                                </strong>

                            </div>

                            <div>

                                <span>
                                    Professor
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $reserva['nm_professor']
                                    ) ?>
                                </strong>

                            </div>

                            <div>

                                <span>
                                    Laboratório(s)
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $reserva['laboratorios']
                                    ) ?>
                                </strong>

                            </div>

                            <div>

                                <span>
                                    Data
                                </span>

                                <strong>
                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $reserva['dt_reserva']
                                        )
                                    ) ?>
                                </strong>

                            </div>

                            <div>

                                <span>
                                    Horário
                                </span>

                                <strong>

                                    <?= date(
                                        'H:i',
                                        strtotime(
                                            $reserva['hr_inicio']
                                        )
                                    ) ?>

                                    às

                                    <?= date(
                                        'H:i',
                                        strtotime(
                                            $reserva['hr_fim']
                                        )
                                    ) ?>

                                </strong>

                            </div>

                            <div>

                                <span>
                                    Alunos
                                </span>

                                <strong>
                                    <?= (int) $reserva['nr_alunos'] ?>
                                </strong>

                            </div>

                        </div>

                        <form
                            method="POST"
                            action="reservas.php"
                            class="form-cancelar"
                            onsubmit="return confirm('Tem certeza que deseja cancelar esta reserva?');"
                        >

                            <input
                                type="hidden"
                                name="cancelar_reserva"
                                value="1"
                            >

                            <input
                                type="hidden"
                                name="id_reserva"
                                value="<?= (int) $reserva['cd_reserva'] ?>"
                            >

                            <button
                                type="submit"
                                class="botao-cancelar-reserva"
                            >
                                Cancelar reserva
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>

</html>