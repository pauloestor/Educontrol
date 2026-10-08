<?php
session_start();

// bloqueia acesso direto sem login
if (empty($_SESSION['user_id'])) {
    header("Location: /ControlePresenca/login.html");
    exit;
}

// segurança extra contra cache do navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão de Presença de Professores - Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- HEADER -->
    <header class="app-header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="app-title">Gestão de Presença</h1>
        </div>
        <div class="header-center">
            <div class="current-time" id="currentTime">00:00</div>
        </div>
        <div class="header-right">
            <button class="notification-btn" id="notificationBtn" aria-label="Notificações">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">3</span>
            </button>
            <button class="theme-toggle" id="themeToggle" aria-label="Alternar tema">
                <i class="fas fa-sun"></i>
            </button>
            <div class="admin-info">

                <img
                    id="adminAvatar"
                    src="img/default-user.png"
                    alt="Foto de perfil"
                    class="admin-avatar">

                <div class="admin-details">

                    <div id="adminName" class="admin-name">
                        Carregando...
                    </div>

                    <div id="adminRole" class="admin-role">
            ...
                    </div>

                </div>

            </div>
            <button class="user-menu-btn" id="userMenuBtn" aria-label="Menu do utilizador">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i>
                <span class="logo-text">EduControl</span>
            </div>
            <button class="sidebar-close" id="sidebarClose" aria-label="Fechar sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="#dashboard" class="nav-item active" data-page="dashboard" title="Dashboard">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="#disciplines" class="nav-item" data-page="disciplines" title="Disciplinas">
                <i class="fas fa-book"></i>
                <span class="nav-label">Disciplinas</span>
            </a>
            <a href="#teachers" class="nav-item" data-page="teachers" title="Professores">
                <i class="fas fa-chalkboard-user"></i>
                <span class="nav-label">Usuários</span>
            </a>
            <a href="#schedules" class="nav-item" data-page="schedules" title="Escalas">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-label">Escalas</span>
            </a>
            <a href="#monitoring" class="nav-item" data-page="monitoring" title="Monitorização">
                <i class="fas fa-video"></i>
                <span class="nav-label">Monitorização</span>
            </a>
            
            <hr class="sidebar-divider">
            <a href="#profile" class="nav-item" data-page="profile" title="Meu Perfil">
                <i class="fas fa-user-circle"></i>
                <span class="nav-label">Meu Perfil</span>
            </a>
            <a href="#settings" class="nav-item" data-page="settings" title="Configurações">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Configurações</span>
            </a>
            <a href="#logout" class="nav-item logout" data-page="logout" title="Sair">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-label">Sair</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        <!-- DASHBOARD PAGE -->
        <section id="dashboard-page" class="page active" data-page="dashboard">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a>
                </div>
                <h2 class="page-title">Bem-vindo ao Painel Administrativo</h2>
            </div>

            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-icon" style="background:#3498db;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Professores</div>
                        <div class="stat-value" id="totalProfessores">0</div>
                        <div class="stat-change" id="professoresMes">-</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#27ae60;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Presentes</div>
                        <div class="stat-value" id="totalPresentes">0</div>
                        <div class="stat-change" id="taxaPresenca">0%</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#e74c3c;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Ausentes</div>
                        <div class="stat-value" id="totalFaltas">0</div>
                        <div class="stat-change">Hoje</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#f39c12;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Atrasados</div>
                        <div class="stat-value" id="totalAtrasos">0</div>
                        <div class="stat-change">Hoje</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#9b59b6;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Disciplinas</div>
                        <div class="stat-value" id="totalDisciplinas">0</div>
                        <div class="stat-change">Ativas</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#16a085;">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Escalas</div>
                        <div class="stat-value" id="totalEscalas">0</div>
                        <div class="stat-change">Este mês</div>
                    </div>
                </div>

            </div>
            <!-- CHARTS SECTION -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Presenças por Mês</h3>
                        <div class="chart-legend">
                            <span class="legend-item"><span class="legend-color" style="background: #3498db;"></span>Presentes</span>
                            <span class="legend-item"><span class="legend-color" style="background: #e74c3c;"></span>Ausentes</span>
                            <span class="legend-item"><span class="legend-color" style="background: #f39c12;"></span>Atrasados</span>
                        </div>
                    </div>
                    <canvas id="barChart" width="400" height="300"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Distribuição Hoje</h3>
                    </div>
                    <canvas id="pieChart" width="300" height="300" style="max-width: 100%;"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Evolução Mensal</h3>
                    </div>
                    <canvas id="lineChart" width="400" height="300"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Taxa de Assiduidade</h3>
                    </div>
                    <canvas id="assidChart" width="300" height="300" style="max-width: 100%;"></canvas>
                </div>
            </div>

            <!-- RECENT ACTIVITIES -->
            <div class="activities-section">
                <div class="section-header">
                    <h3>Atividades Recentes</h3>
                    <a href="#history" class="link-view-all">Ver todas</a>
                </div>
                <div class="activities-list" id="activitiesList">
                    <div class="activity-item">
                        <div class="activity-avatar">
                            <i class="fas fa-check" style="color: #27ae60;"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Marcação de Presença</div>
                            <div class="activity-desc">João Silva marcou presença - 08:30</div>
                            <div class="activity-time">Há 5 minutos</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- DISCIPLINES PAGE -->
        <section id="disciplines-page" class="page" data-page="disciplines">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Disciplinas</span>
                </div>
                <h2 class="page-title">Gestão de Disciplinas</h2>
            </div>

            <div class="page-actions">
                <button class="btn btn-primary" id="addDisciplineBtn">
                    <i class="fas fa-plus"></i> Adicionar Disciplina
                </button>
            </div>

            <div class="table-container">
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Pesquisar disciplinas..." class="search-input" id="disciplinesSearch">
                </div>
                <table class="data-table" id="disciplinesTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Data Criação</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="disciplinesList"></tbody>
                </table>
            </div>
        </section>

        <!-- TEACHERS PAGE -->
        <section id="teachers-page" class="page" data-page="teachers">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Professores</span>
                </div>
                <h2 class="page-title">Gestão de Professores</h2>
            </div>

            <div class="page-actions">
                <button class="btn btn-primary" id="addTeacherBtn">
                    <i class="fas fa-plus"></i> Adicionar Usuários
                </button>
            </div>

            <div class="teachers-grid" id="teachersGrid"></div>
        </section>

        <!-- SCHEDULES PAGE -->
        <section id="schedules-page" class="page" data-page="schedules">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Escalas</span>
                </div>
                <h2 class="page-title">Gestão de Escalas de Trabalho</h2>
            </div>

            <div class="page-actions">
                <button class="btn btn-primary" id="addScheduleBtn">
                    <i class="fas fa-plus"></i> Adicionar Escala
                </button>
            </div>

            <div class="schedules-grid" id="schedulesGrid"></div>
        </section>

        <!-- MONITORING PAGE -->
        <section id="monitoring-page" class="page" data-page="monitoring">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Monitorização</span>
                </div>
                <h2 class="page-title">Monitorização de Presenças</h2>
            </div>

            <!-- MONITORING STATS -->
            <div class="monitoring-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #27ae60;"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Presentes Hoje</div>
                        <div class="stat-value" id="presentToday">38</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #e74c3c;"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Ausentes Hoje</div>
                        <div class="stat-value" id="absentToday">2</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #f39c12;"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Atrasados Hoje</div>
                        <div class="stat-value" id="lateToday">2</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #3498db;"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Total Marcações</div>
                        <div class="stat-value" id="totalMarkings">42</div>
                    </div>
                </div>
            </div>

            <!-- ADVANCED FILTERS -->
            <div class="filters-section">
                <div class="filter-group">
                    <label>Pesquisa:</label>
                    <input type="text" placeholder="Nome ou nº mecanográfico..." id="monitoringSearch" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Data:</label>
                    <input type="date" id="monitoringDate" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Mês:</label>
                    <select id="monitoringMonth" class="filter-input">
                        <option value="">Todos</option>
                        <option value="1">Janeiro</option>
                        <option value="2">Fevereiro</option>
                        <option value="3">Março</option>
                        <option value="4">Abril</option>
                        <option value="5">Maio</option>
                        <option value="6">Junho</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Disciplina:</label>
                    <select id="monitoringDiscipline" class="filter-input"></select>
                </div>
                <div class="filter-group">
                    <label>Estado:</label>
                    <select id="monitoringStatus" class="filter-input">
                        <option value="">Todos</option>
                        <option value="presente">Presente</option>
                        <option value="ausente">Ausente</option>
                        <option value="atrasado">Atrasado</option>
                    </select>
                </div>
                <button class="btn btn-secondary" id="exportBtn"><i class="fas fa-download"></i> Exportar</button>
            </div>

            <div class="table-container">
                <table class="data-table" id="monitoringTable">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>Nº Mecanográfico</th>
                            <th>Disciplina</th>
                            <th>Data</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="monitoringList"></tbody>
                </table>
            </div>
        </section>

        <!-- PROFILE PAGE -->
        <section id="profile-page" class="page" data-page="profile">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Meu Perfil</span>
                </div>
                <h2 class="page-title">Meu Perfil</h2>
            </div>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar-wrapper">

    <img 
        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%233498DB'/%3E%3Ctext x='50' y='65' font-size='50' fill='white' text-anchor='middle' font-weight='bold'%3EAS%3C/text%3E%3C/svg%3E"
        class="profile-avatar"
        id="profileAvatar"
    >

    <!-- OVERLAY HOVER -->
    <div class="avatar-overlay">
        <label for="avatarInput">
            <i class="fas fa-camera"></i>
            <span>Carregar imagem</span>
        </label>
    </div>

           <!-- INPUT OCULTO -->
                    <input type="file" id="avatarInput" accept="image/*">
                </div>
                    <div class="profile-info">
                        <h3 class="profile-name" id="profileName">...</h3>
                        <p class="profile-role" id="profileRole">...</p>
                        <p class="profile-email" id="profileInfoEmail">...</p>
                    </div>
                </div>

                <div class="profile-form">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" id="profileNameInput" disabled>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Género</label>
                            <input type="text" id="profileGender" value="Masculino" disabled>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email"  id="profileEmail" value="admin.silva@school.edu.pt">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="tel" id="profilePhone" >
                        </div>
                        <div class="form-group">
                            <label>Cargo</label>
                            <input type="text" id="profileRoleInput"  disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Data de Admissão</label>
                        <input type="date" id="profileCreatedAt" disabled>
                    </div>
                    <button class="btn btn-primary" id="saveProfileBtn">Guardar Alterações</button>
                </div>
            </div>
        </section>

        <!-- SETTINGS PAGE -->
        <section id="settings-page" class="page" data-page="settings">
            <div class="page-header">
                <div class="breadcrumbs">
                    <a href="#dashboard">Dashboard</a> / <span>Configurações</span>
                </div>
                <h2 class="page-title">Configurações do Sistema</h2>
            </div>

            <div class="settings-container">
                <!-- APPEARANCE -->
                <div class="settings-section">
                    <h3 class="settings-title">Aparência</h3>
                    <div class="setting-item">
                        <div class="setting-label">
                            <label>Tema</label>
                            <p>Escolha o tema claro ou escuro</p>
                        </div>
                        <div class="setting-control">
                            <div class="theme-options">
                                <button class="theme-option active" id="lightThemeBtn" data-theme="light">
                                    <i class="fas fa-sun"></i>
                                    <span>Claro</span>
                                </button>
                                <button class="theme-option" id="darkThemeBtn" data-theme="dark">
                                    <i class="fas fa-moon"></i>
                                    <span>Escuro</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECURITY -->
                <div class="settings-section">
                    <h3 class="settings-title">Segurança</h3>
                    <div class="setting-item">
                        <div class="setting-label">
                            <label>Alterar Palavra-passe</label>
                            <p>Altere sua senha regularmente para maior segurança</p>
                        </div>
                        <button class="btn btn-secondary" id="changePasswordBtn">Alterar Palavra-passe</button>
                    </div>
                </div>

                <!-- SYSTEM -->
                <div class="settings-section">
                    <h3 class="settings-title">Sistema</h3>
                    <div class="setting-item">
                        <div class="setting-label">
                            <label>Nome da Instituição</label>
                        </div>
                        <input type="text" value="Escola Secundária Digital" class="filter-input">
                    </div>
                    <div class="setting-item">
                        <div class="setting-label">
                            <label>Horários Padrão</label>
                            <p>Manhã: 07:30 - 12:15 | Tarde: 13:00 - 17:45</p>
                        </div>
                    </div>
                    <button class="btn btn-primary">Guardar Configurações</button>
                </div>
            </div>
        </section>
    </main>

    <!-- MODALS -->~

    <!-- DISCIPLINAS MODAL-->

<!-- Overlay do Modal -->
<div class="modal-overlay" id="disciplineModal">

    <!-- Container principal do Modal -->
    <div class="modal-container">

        <!-- ===============================
         CABEÇALHO DO MODAL
        ================================ -->
        <div class="modal-header">

            <!-- Ícone + Título -->
            <div class="modal-title-area">
                <div class="modal-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <!-- Título alterado dinamicamente via JS -->
                <div>
                    <h3 id="modalTitle">Cadastrar Disciplina</h3>
                    <p>Preencha os dados da disciplina.</p>
                </div>
            </div>

            <!-- Botão Fechar -->
            <button
                class="close-modal"
                id="closeDisciplineModal"
                title="Fechar">

                <i class="fas fa-times"></i>
            </button>

        </div>

        <!-- ===============================
         CORPO DO MODAL
        ================================ -->
        <form id="disciplineForm">

            <input type="hidden" id="disciplineId">

            <div class="modal-body">

                <!-- Campo Nome -->
                <div class="form-group">

                    <label for="disciplineName">
                        Nome da Disciplina *
                    </label>

                    <div class="input-wrapper">
                        <i class="fas fa-book"></i>

                        <input
                            type="text"
                            id="disciplineName"
                            maxlength="100"
                            placeholder="Ex.: Matemática"
                            required>
                    </div>

                    <small class="error-message"></small>

                </div>

                <!-- Campo Descrição -->
                <div class="form-group">

                    <label for="disciplineDescription">
                        Descrição
                    </label>

                    <div class="textarea-wrapper">

                        <textarea
                            id="disciplineDescription"
                            rows="5"
                            maxlength="500"
                            placeholder="Descreva resumidamente a disciplina...">
                        </textarea>

                    </div>

                    <div class="character-counter">
                        <span id="descriptionCounter">
                            0/500 caracteres
                        </span>
                    </div>

                </div>

                <!-- Campo Estado -->
                <div class="form-group">

                    <label for="disciplineStatus">
                        Estado
                    </label>

                    <div class="input-wrapper">

                        <i class="fas fa-toggle-on"></i>

                        <select id="disciplineStatus">

                            <option value="Ativa" selected>
                                Ativa
                            </option>

                            <option value="Inativa">
                                Inativa
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            <!-- ===============================
             RODAPÉ DO MODAL
            ================================ -->
            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    id="cancelDisciplineBtn">

                    <i class="fas fa-times-circle"></i>
                    Cancelar

                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="saveDisciplineBtn">

                    <i class="fas fa-save"></i>
                    Guardar Disciplina

                </button>

            </div>

        </form>

    </div>

</div>
<!-- DISCIPLINAS MODAL-->


<!-- MODAL USUÁRIOS -->

<div class="modal-overlay" id="userModal">

    <div class="modal-container">

        <!-- ================= HEADER ================= -->
        <div class="modal-header">

            <div class="modal-title-area">
                <i class="fas fa-user-cog modal-icon"></i>

                <div>
                    <h3 id="userModalTitle">Adicionar Usuário</h3>
                    <p>Crie um utilizador do sistema</p>
                </div>
            </div>

            <button class="close-modal" id="closeUserModal">
                <i class="fas fa-times"></i>
            </button>

        </div>

        <!-- ================= FORM ================= -->
        <form id="userForm">

            <div class="modal-body">

                <!-- TIPO DE USUÁRIO -->
                <div class="form-group">

                    <label>Tipo de Usuário *</label>

                    <div class="input-wrapper">
                        <i class="fas fa-user-tag"></i>

                        <select id="userType">
                            <option value="professor">Professor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                </div>

                <!-- NOME -->
                <div class="form-group">
                    <label>Nome Completo *</label>

                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="userName" placeholder="Nome completo" required>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label>Email *</label>

                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="userEmail" placeholder="email@exemplo.com" required>
                    </div>

                    <small class="validation-message" id="emailFeedback"></small>
                </div>

                <!-- TELEFONE -->
                    <div class="form-group">
                        <label>Telefone</label>

                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="text" id="userPhone" maxlength="9">
                        </div>

                        <small class="validation-message" id="phoneFeedback"></small>
                    </div>

                    <!-- GÉNERO -->
                    <div class="form-group">
                        <label>Género</label>

                        <div class="input-wrapper">
                            <i class="fas fa-venus-mars"></i>

                            <select id="userGender">
                                <option value="">Selecionar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                            </select>
                        </div>
                    </div>

                    <!-- DATA NASCIMENTO -->
                    <div class="form-group">
                        <label>Data de Nascimento</label>

                        <div class="input-wrapper">
                            <i class="fas fa-calendar"></i>
                            <input type="date" id="userBirth">
                        </div>
                    </div>


                <!-- SENHA -->
                <div class="form-group">
                    <label>Senha Inicial *</label>

                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="userPassword" placeholder="Senha inicial" required maxlength="4">
                    </div>

                    <small class="validation-message" id="passwordFeedback"></small>
                </div>

                <!-- ================= CAMPOS PROFESSOR ================= -->
                <div id="professorFields">

                    <!-- DISCIPLINA -->
                    <div class="form-group">
                        <label>Disciplina *</label>

                        <div class="input-wrapper">
                            <i class="fas fa-book"></i>

                            <select id="userDiscipline">
                                <option value="">Selecionar disciplina</option>
                            </select>
                        </div>

                        <small class="validation-message" id="disciplineFeedback"></small>
                    </div>

                    <div class="form-group">
                            <label>Salário Base (Kz) *</label>

                        <div class="input-wrapper">
                            <i class="fas fa-money-bill-wave"></i>

                            <input
                                type="number"
                                id="userSalary"
                                placeholder="Ex.: 150000">
                        </div>

                        <small class="validation-message" id="salaryFeedback"></small>
                    </div>

                    
                </div>

            </div>

            <!-- ================= FOOTER ================= -->
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" id="cancelUserBtn">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>

                <button type="submit" class="btn btn-primary" id="saveUserBtn">
                    <i class="fas fa-save"></i>
                    Guardar Usuário
                </button>

            </div>

        </form>

    </div>
</div>

<!-- MODAL USUÁRIOS -->

<!-- MODAL ESCALAS -->
 <!-- ================= MODAL DE ESCALAS ================= -->
<div class="modal-overlay" id="scaleModal">

    <div class="modal-container">

        <!-- HEADER -->
        <div class="modal-header">

            <div class="modal-title-area">
                <i class="fas fa-calendar-alt modal-icon"></i>

                <div>
                    <h3 id="scaleModalTitle">Cadastrar Escala</h3>
                    <p>Definição de horários de trabalho</p>
                </div>
            </div>

            <button class="close-modal" id="closeScaleModal">
                <i class="fas fa-times"></i>
            </button>

        </div>

        <!-- FORM -->
        <form id="scaleForm">

            <div class="modal-body">

                <!-- PROFESSOR -->
                <div class="form-group">
                    <label>Professor *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <select id="scaleTeacher">
                            <option value="">Selecionar professor</option>
                        </select>
                    </div>
                </div>

                <!-- MECANOGRAFICO -->
                <div class="form-group">
                    <label>Número Mecanográfico</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="scaleCode" readonly>
                    </div>
                </div>

                <!-- DISCIPLINA -->
                <div class="form-group">
                    <label>Disciplina</label>
                    <div class="input-wrapper">
                        <i class="fas fa-book"></i>
                        <input type="text" id="scaleDiscipline" readonly>
                        
                    </div>

                    <input type="hidden" id="scaleDisciplineId">
                </div>

                <!-- DIAS -->
                <div class="form-group">
                    <label>Dias de Trabalho *</label>

                    <div class="checkbox-all">
                        <input type="checkbox" id="selectAllDays">
                        <label for="selectAllDays"><strong>Selecionar todos</strong></label>
                    </div>

                    <div class="days-grid">
                        <label><input type="checkbox" class="day"> Segunda</label>
                        <label><input type="checkbox" class="day"> Terça</label>
                        <label><input type="checkbox" class="day"> Quarta</label>
                        <label><input type="checkbox" class="day"> Quinta</label>
                        <label><input type="checkbox" class="day"> Sexta</label>
                        <label><input type="checkbox" class="day" disabled> Sábado</label>
                        <label><input type="checkbox" class="day" disabled> Domingo</label>
                    </div>
                </div>

                <!-- PERIODO -->
                <div class="form-group">
                    <label>Período *</label>

                    <div class="radio-group">
                        <label><input type="radio" name="period" value="manha"> Manhã</label>
                        <label><input type="radio" name="period" value="tarde"> Tarde</label>
                        <label><input type="radio" name="period" value="integral"> Integral</label>
                    </div>
                </div>

                <!-- HORAS -->
                <div class="form-group">
                    <label>Hora de Entrada *</label>
                    <input type="time" id="scaleStart">
                </div>

                <div class="form-group">
                    <label>Hora de Saída *</label>
                    <input type="time" id="scaleEnd">
                </div>

                <!-- TOLERANCIA -->
                <div class="form-group">
                    <label>Tolerância (minutos)</label>
                    <input type="number" id="scaleTolerance" value="15">
                </div>

                
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" id="cancelScaleBtn">
                    <i class="fas fa-times"></i> Cancelar
                </button>

                <button type="submit" class="btn btn-primary" id="saveScaleBtn">
                    <i class="fas fa-save"></i> Guardar Escala
                </button>

            </div>

        </form>

    </div>
</div>
<!-- MODAL ESCALAS -->

<!-- MODAL DE LISTAGEM DE PROFESORES-->
 <!-- ================= MODAL VISUALIZAR UTILIZADOR ================= -->

<div class="modal-overlay" id="viewUserModal">

    <div class="modal-container modal-view-user">

        <!-- HEADER -->
        <div class="modal-header">

            <div class="modal-title-area">

                <i class="fas fa-user-circle modal-icon"></i>

                <div>
                    <h3>Detalhes do Utilizador</h3>
                    <p>Informações completas do utilizador</p>
                </div>

            </div>

            <button
                class="close-modal"
                id="closeViewUserModal">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <!-- BODY -->
        <div class="modal-body">

            <!-- FOTO -->
            <div class="user-profile-header">

                <img
                    id="viewUserPhoto"
                    src="img/default-user.png"
                    class="view-user-photo">

                <h2 id="viewUserName">
                    Nome do Utilizador
                </h2>

                <span
                    id="viewUserType"
                    class="user-type-badge">

                    Professor

                </span>

            </div>

            <!-- ================= DADOS PESSOAIS ================= -->

            <div class="view-section">

                <h4>
                    <i class="fas fa-user"></i>
                    Informações Pessoais
                </h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Nome</label>
                        <span id="viewNome"></span>
                    </div>

                    <div class="view-item">
                        <label>Email</label>
                        <span id="viewEmail"></span>
                    </div>

                    <div class="view-item">
                        <label>Telefone</label>
                        <span id="viewTelefone"></span>
                    </div>

                    <div class="view-item">
                        <label>Género</label>
                        <span id="viewGenero"></span>
                    </div>

                    <div class="view-item">
                        <label>Data de Nascimento</label>
                        <span id="viewNascimento"></span>
                    </div>

                </div>

            </div>

            <!-- ================= DADOS PROFISSIONAIS ================= -->

            <div
                class="view-section"
                id="professionalSection">

                <h4>

                    <i class="fas fa-briefcase"></i>

                    Informações Profissionais

                </h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Nº Mecanográfico</label>
                        <span id="viewMecanografico"></span>
                    </div>

                    <div class="view-item">
                        <label>Disciplina</label>
                        <span id="viewDisciplina"></span>
                    </div>

                    <div class="view-item">
                        <label>Salário Base</label>
                        <span id="viewSalario"></span>
                    </div>

                </div>

            </div>

            <!-- ================= SISTEMA ================= -->

            <div class="view-section">

                <h4>

                    <i class="fas fa-desktop"></i>

                    Informações do Sistema

                </h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Tipo</label>
                        <span id="viewTipo"></span>
                    </div>

                    <div class="view-item">
                        <label>Data de Cadastro</label>
                        <span id="viewCadastro"></span>
                    </div>

                    <div class="view-item">
                        <label>Último Login</label>
                        <span id="viewUltimoLogin"></span>
                    </div>

                </div>

            </div>

        </div>

</div>

<!-- FOOTER -->
        <div class="modal-footer">

            <button
                class="btn btn-secondary"
                id="closeViewUserBtn">

                <i class="fas fa-times"></i>

                Fechar

            </button>

        </div>

    </div>

</div>

<!-- MODAL VIZUALIZAR DETALHES-->

<!-- MODAL DETALHE PROFESSOR -->
<!-- MODAL DETALHE PROFESSOR -->
<div class="modal-overlay" id="teacherReportModal">

    <div class="modal-container modal-view-user">

        <!-- HEADER -->
        <div class="modal-header">

            <div class="modal-title-area">

                <i class="fas fa-user-circle modal-icon"></i>

                <div>
                    <h3>Detalhes do Professor</h3>
                    <p>Relatório completo de assiduidade e salário</p>
                </div>

            </div>

            <button class="close-modal" id="closeTeacherReportModal">
                <i class="fas fa-times"></i>
            </button>

        </div>

        <!-- BODY -->
        <div class="modal-body">

            <!-- FOTO -->
            <div class="user-profile-header">

                <img id="teacherPhoto"
                     src="img/default-user.png"
                     class="view-user-photo">

                <h2 id="teacherName">Nome do Professor</h2>

                <span id="teacherType" class="user-type-badge">
                    Professor
                </span>

            </div>

            <!-- PESSOAL -->
            <div class="view-section">

                <h4>Informações Pessoais</h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Nome</label>
                        <span id="teacherFullName"></span>
                    </div>

                    <div class="view-item">
                        <label>Email</label>
                        <span id="teacherEmail"></span>
                    </div>

                    <div class="view-item">
                        <label>Telefone</label>
                        <span id="teacherPhone"></span>
                    </div>

                </div>

            </div>

            <!-- PROFISSIONAL -->
            <div class="view-section">

                <h4>Dados Profissionais</h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Nº Mecanográfico</label>
                        <span id="teacherNumber"></span>
                    </div>

                    <div class="view-item">
                        <label>Disciplina</label>
                        <span id="teacherSubject"></span>
                    </div>

                    <div class="view-item">
                        <label>Salário Base</label>
                        <span id="teacherBaseSalary"></span>
                    </div>

                </div>

            </div>

            <!-- ASSIDUIDADE -->
            <div class="view-section">

                <h4>Assiduidade</h4>

                <div class="view-grid">

                    <div class="view-item">
                        <label>Presenças</label>
                        <span id="teacherPresences"></span>
                    </div>

                    <div class="view-item">
                        <label>Atrasos</label>
                        <span id="teacherDelays"></span>
                    </div>

                    <div class="view-item">
                        <label>Faltas</label>
                        <span id="teacherAbsences"></span>
                    </div>

                    <div class="view-item">
                        <label>Descontos</label>
                        <span id="teacherTotalDiscounts"></span>
                    </div>

                </div>

            </div>

            <!-- SALÁRIO -->
            <div class="salary-box">

                <h4>Cálculo Salarial</h4>

                <div class="salary-result">

                    <div>
                        <span>Salário Base:</span>
                        <strong id="teacherSalaryBase"></strong>
                    </div>

                    <div class="danger">
                        <span>Descontos:</span>
                        <strong id="teacherDiscounts"></strong>
                    </div>

                    <div class="total">
                        <span>Salário Final:</span>
                        <strong id="teacherFinalSalary"></strong>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= FIM MODAL VISUALIZAR ================= -->

    <!-- Notification Modal -->
    <div class="modal" id="notificationModal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Notificações</h3>
                <button class="modal-close" data-modal="notificationModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="notification-list" id="notificationList">
                    <div class="notification-item">
                        <i class="fas fa-info-circle" style="color: #3498db;"></i>
                        <div class="notification-text">
                            <p>Novo professor adicionado ao sistema</p>
                            <small>Há 2 horas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal" id="logoutModal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Confirmar Saída</h3>
            </div>
            <div class="modal-body">
                <p>Tem a certeza de que deseja sair do sistema?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="logoutModal">Cancelar</button>
                <button class="btn btn-danger" id="confirmLogoutBtn">Sair</button>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal" id="passwordModal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Alterar Palavra-passe</h3>
                <button class="modal-close" data-modal="passwordModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Palavra-passe Atual</label>
                    <input type="password" placeholder="Digite sua senha atual" id="currentPassword">
                </div>
                <div class="form-group">
                    <label>Nova Palavra-passe</label>
                    <input type="password" placeholder="Digite a nova senha" id="newPassword">
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar"></div>
                    <span class="strength-text">Força da senha</span>
                </div>
                <div class="form-group">
                    <label>Confirmar Palavra-passe</label>
                    <input type="password" placeholder="Confirme a nova senha" id="confirmPassword">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="passwordModal">Cancelar</button>
                <button class="btn btn-primary">Alterar Palavra-passe</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="js/admin.js"></script>
    <script src="js/perfiladmin.js"></script>
</body>
</html>
