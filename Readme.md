# EduControl — Sistema de Controlo de Presença e Folha de Pagamento de Professores

O **EduControl** é um sistema web desenvolvido para auxiliar instituições de ensino no controlo da presença dos professores, gestão de horários e acompanhamento de faltas e atrasos.

O projeto foi desenvolvido individualmente como forma de aplicar conhecimentos de **desenvolvimento web, programação, bases de dados e análise de sistemas**.

> **Estado do projeto:** Em desenvolvimento

---

## Sobre o projeto

O EduControl foi pensado para substituir processos manuais de controlo de presença por um sistema digital.

O professor pode iniciar sessão e registar a sua presença de acordo com o horário definido. O sistema verifica o horário de entrada e permite identificar automaticamente situações de presença normal ou atraso.

O administrador possui acesso às informações dos professores, horários e registos de presença, podendo acompanhar as ocorrências e identificar faltas.

---

## Objetivos

* Automatizar o controlo de presença dos professores;
* Gerir horários e escalas;
* Registar entradas e saídas;
* Identificar atrasos automaticamente;
* Identificar professores ausentes;
* Permitir o acompanhamento das faltas;
* Associar descontos predefinidos às faltas;
* Centralizar as informações num único sistema;
* Aplicar conhecimentos de desenvolvimento Full-Stack.

---

## Funcionalidades

### Professor

* Criar sessão através de autenticação;
* Registar presença;
* Registar entrada e saída;
* Consultar o horário atribuído;
* Identificar situações de atraso;
* Acompanhar os seus registos de presença.

### Administrador

* Aceder ao painel administrativo;
* Gerir professores;
* Gerir disciplinas;
* Gerir horários e escalas;
* Acompanhar os registos de presença;
* Identificar faltas;
* Acompanhar atrasos;
* Gerir descontos associados às faltas.

---

## Lógica de controlo de presença

O sistema utiliza o horário definido para cada professor para determinar a situação da presença.

### Presença normal

Quando o professor regista a entrada dentro do horário permitido, a presença é registada como **Presente**.

### Atraso

Quando o professor regista a entrada depois do horário permitido, a presença pode ser registada como **Atraso**, sendo também armazenada a quantidade de minutos de atraso.

### Falta

Quando existe uma escala prevista para determinado professor, mas não existe um registo de presença, o sistema permite identificar a situação como **Falta**.

### Desconto

O sistema foi projetado para permitir a associação de um desconto predefinido às faltas.

> Exemplo: uma falta pode estar associada a um desconto de **5.000 Kz**, de acordo com as regras definidas pela instituição.

---

## Tecnologias utilizadas

* **HTML5** — estrutura das páginas;
* **CSS3** — estilos e interface;
* **JavaScript** — interatividade e funcionalidades no navegador;
* **PHP** — lógica do sistema e processamento no servidor;
* **MySQL** — armazenamento e gestão dos dados.

---

## Ferramentas utilizadas

* **Visual Studio Code**
* **XAMPP**
* **Google Chrome**
* **Microsoft Edge**

---

## Base de dados

O sistema utiliza **MySQL** para armazenar e organizar as informações.

Entre os principais dados estão:

* Utilizadores;
* Professores;
* Disciplinas;
* Horários e escalas;
* Registos de presença;
* Atrasos;
* Faltas;
* Descontos;
* Observações dos registos.

O projeto utiliza relações entre as diferentes entidades para manter os dados organizados e facilitar a consulta das informações.

---

## Interface do sistema

### Página de Login

A página de autenticação permite que os utilizadores acedam ao sistema através do seu e-mail e palavra-passe.

Inclui funcionalidades como:

* Campo de e-mail;
* Campo de palavra-passe;
* Mostrar/ocultar palavra-passe;
* Recuperação de palavra-passe;
* Mensagens de erro;
* Indicador de carregamento;
* Alternância entre tema claro e escuro.

### Capturas de ecrã

> As imagens abaixo serão adicionadas à medida que as principais telas do sistema forem concluídas.

```text
screenshots/
├── admin.png
├── login.png
└── professor.png
```

---

## Diagrama de casos de uso

O sistema possui um modelo de casos de uso para representar as principais interações entre os utilizadores e o sistema.

A imagem do diagrama pode ser encontrada em:

```text
docs/use-case-diagram.png
```

---

## Estrutura do projeto

Uma possível organização do projeto:

```text
ControlePresenca/
│
├── css/
│   ├── admin.css
│   ├── login.css
│   └── professor.css
│
├── js/
│   ├── admin.js
│   ├── alterar_senha.js
│   ├── cadastro_escala.js
│   ├── login.js
│   ├── perfiladmin.js
│   ├── professor_presenca.js
│   └── professor.js
│
├── phpadmin/
│   ├── atualizar_perfil.php
│   ├── atualizar_usuario.php
│   ├── cadastrar_disciplina.php
│   ├── cadastrar_escala.php
│   ├── cadastrar_usuario.php
│   ├── carregar_detalhe.php
│   ├── carregar_monitorizacao.php
│   ├── carregar_perfil.php
│   ├── dashboard.php
│   ├── listar_disciplina.php
│   ├── listar_disciplinas_select.php
│   ├── listar_escalas.php
│   ├── listar_professores_select.php
│   ├── listar_professores.php
│   ├── perfiladmin.php
│   ├── registrar_faltas.php
│   └── upload_foto.php
│
├── phpconexao/
│   ├── conexao.php
│   ├── logout.php
│   └── processar_faltas.php
│
├── phplogin/
│   ├── alterar_senha.php
│   └── login.php
│
├── phpprofessor/
│   ├── atualizar_perfil.php
│   ├── carregar_assiduidade.php
│   ├── carregar_dashboard.php
│   ├── carregar_estado_presenca.php
│   ├── carregar_historico_presencas.php
│   ├── carregar_perfil.php
│   ├── carregar_professor.php
│   ├── marcar_presenca.php
│   ├── marcar_saida.php
│   ├── perfil_logado.php
│   └── upload_foto.php
│
├── phpvalidar/
│   ├── validar_disciplina.php
│   ├── validar_email.php
│   └── validar_telefone.php
│
├── uploads/
│   └── perfis
│
├── admin.php
│
├── alterar_senha.html
│
├── login.html
│
├── professor.php
│
├── database/
│   └── educontrol.sql
│
├── docs/
│   └── use-case-diagram.png
│
├── screenshots/
│
├── .gitignore
│
└── README.md
```

*A estrutura poderá ser alterada conforme o desenvolvimento do projeto.*

---

## 🚀 Como executar o projeto

### 1. Instalar o XAMPP

Instale o **XAMPP** e certifique-se de que os serviços **Apache** e **MySQL** estão disponíveis.

### 2. Clonar o repositório

```bash
git clone https://github.com/SEU-USUARIO/EduControl.git
```

### 3. Colocar o projeto no XAMPP

Copie a pasta do projeto para:

```text
C:\xampp\htdocs\
```

### 4. Criar a base de dados

Abra o **phpMyAdmin** e crie a base de dados do projeto.

Depois importe o ficheiro:

```text
database/educontrol.sql
```

### 5. Configurar a ligação

Verifique as configurações da ligação à base de dados no ficheiro de conexão do projeto.

### 6. Executar

Com o Apache e o MySQL ativos, abra no navegador:

```text
http://localhost/ControlePresenca/
```

---

## Segurança

Este projeto é destinado principalmente a fins de aprendizagem e desenvolvimento.

**Não devem ser publicados no GitHub:**

* Palavras-passe reais;
* Credenciais da base de dados;
* Ficheiros `.env` com informações privadas;
* Dados pessoais reais de professores ou utilizadores;
* Outras informações confidenciais.

Para isso, deve ser utilizado um ficheiro `.gitignore` adequado.

---

## Conhecimentos aplicados

Durante o desenvolvimento do EduControl foram aplicados conhecimentos de:

* Desenvolvimento Web;
* PHP;
* HTML e CSS;
* JavaScript;
* MySQL;
* SQL;
* Modelação de bases de dados;
* Relacionamento entre tabelas;
* Autenticação de utilizadores;
* CRUD;
* Regras de negócio;
* Validação de horários;
* Sistemas de controlo de presença;
* Desenvolvimento Full-Stack.

---

## Estado atual

O **EduControl ainda está em desenvolvimento**.

Algumas funcionalidades podem ser melhoradas ou expandidas à medida que o projeto evolui.

### Próximas melhorias

* Melhorar o painel administrativo;
* Implementar relatórios de presença;
* Melhorar o sistema de descontos;
* Adicionar filtros avançados;
* Melhorar a segurança da autenticação;
* Implementar diferentes níveis de permissões;
* Melhorar a interface;
* Adicionar relatórios exportáveis;
* Realizar mais testes;
* Preparar o sistema para implantação em ambiente real.

---

## Autor

**Paulo António Mateus**

Desenvolvedor Full-Stack
🇦🇴 Angola

**Projeto individual — 2026**

---

## Licença

Este projeto foi desenvolvido para fins de aprendizagem, prática e demonstração de conhecimentos em desenvolvimento de software.

A utilização, modificação ou distribuição do código deve respeitar os termos definidos pelo autor.

---

Se este projeto foi útil ou interessante para si, considere deixar uma estrela no repositório.
