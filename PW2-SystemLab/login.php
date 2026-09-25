<?php

session_start();

require_once 'includes/conexao.php';

if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {

        $erro = 'Preencha todos os campos.';

    } else {

        /* ===== - Buscar o Usuário - ===== */

        $sql = "SELECT
                    cd_usuario,
                    nm_usuario,
                    ds_email,
                    ds_senha,
                    ds_tipo,
                    id_professor
                FROM tb_usuarios
                WHERE ds_email = ?";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {

                $usuario = $resultado->fetch_assoc();

                /* ===== - Verifica a Senha - ===== */

                if ($senha === $usuario['ds_senha']) {

                    // Salva os dados básicos do usuário na sessão
                    $_SESSION['usuario'] = $usuario['nm_usuario'];
                    $_SESSION['usuario_id'] = $usuario['cd_usuario'];
                    $_SESSION['tipo'] = $usuario['ds_tipo'];
                    $_SESSION['professor_id'] = $usuario['id_professor'];

                    /* ===== - Se for Aluno busca os dados na tb_alunos - ===== */

                    if ($usuario['ds_tipo'] === 'aluno') {

                        $sqlAluno = "SELECT
                                        cd_aluno,
                                        nm_aluno,
                                        ds_matricula,
                                        ds_turma
                                    FROM tb_alunos
                                    WHERE ds_email = ?";

                        $stmtAluno = $conexao->prepare($sqlAluno);

                        if ($stmtAluno) {

                            $stmtAluno->bind_param("s", $email);

                            $stmtAluno->execute();

                            $resultadoAluno = $stmtAluno->get_result();

                            if ($resultadoAluno->num_rows === 1) {

                                $aluno = $resultadoAluno->fetch_assoc();

                                $_SESSION['aluno_id'] = $aluno['cd_aluno'];
                                $_SESSION['turma'] = $aluno['ds_turma'];
                                $_SESSION['matricula'] = $aluno['ds_matricula'];

                            }

                            $stmtAluno->close();
                        }
                    }

                    /* ===== - Login Realizado - ===== */

                    header('Location: index.php');
                    exit;

                } else {

                    $erro = 'E-mail ou senha incorretos.';

                }

            } else {

                $erro = 'E-mail ou senha incorretos.';

            }

            $stmt->close();

        } else {

            $erro = 'Erro ao consultar o banco de dados.';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SystemLab</title>

    <link rel="stylesheet" href="css/login.css">

</head>

<body>

    <main class="login-container">

        <section class="login-box">

            <div class="logo">

                <h1>
                    SystemLab
                </h1>

                <p>
                    Sistema de Gerenciamento de Laboratórios
                </p>

            </div>

            <?php if ($erro !== ''): ?>

                <div class="erro">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="campo">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="senha">
                        Senha
                    </label>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        required
                    >

                </div>

                <button type="submit">
                    Entrar
                </button>

            </form>

        </section>

    </main>

</body>

</html>