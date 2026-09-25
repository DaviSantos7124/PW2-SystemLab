# 🖥️ SystemLab

Sistema de gerenciamento e reserva de laboratórios da ETEC.

## 👥 Usuários

### 🔴 Administrador

Pode:

* Ver todas as reservas;
* Cancelar reservas;
* Ver professores;
* Ver matérias;
* Ver laboratórios e computadores;
* Acessar as configurações.

**Login:**

```text
E-mail: admin@systemlab.com
Senha: 123456
```

---

### 🔵 Professor

Pode:

* Criar reservas;
* Escolher a turma;
* Escolher a matéria;
* Escolher um ou mais laboratórios;
* Informar data e horário;
* Ver suas reservas;
* Cancelar suas próprias reservas.

**Exemplo de login:**

```text
E-mail: matheus.calixto@gmail.com
Senha: 123456 (a senha do professor sempre será 123456)
```

Use o e-mail cadastrado no banco para entrar.

---

### 🟢 Aluno

Pode:

* Ver sua turma;
* Ver suas próximas aulas;
* Ver a matéria;
* Ver o professor;
* Ver o laboratório;
* Ver o horário das aulas.

O aluno **não pode criar ou cancelar reservas**.

**Exemplo de login:**

```text
E-mail: davi.araujo12@aluno.cps.sp.gov.br
Senha: 25139 (a senha do aluno sempre será o RM)
```

---

## 🧪 Como testar

1. Entre como **Professor** e faça uma reserva.
2. Escolha uma turma, matéria, data, horário e laboratório.
3. Entre como **Aluno** da mesma turma.
4. A reserva deverá aparecer nas próximas aulas.
5. Tente reservar o mesmo laboratório no mesmo horário para verificar o bloqueio.
6. Entre como **Administrador** para visualizar e gerenciar as reservas.

---

## 🛠️ Tecnologias

* PHP
* MySQL
* HTML
* CSS
* XAMPP

**Projeto acadêmico — ETEC de Itanhaém**
