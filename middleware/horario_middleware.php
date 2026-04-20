<?php
/**
 * Middleware para verificação de horário de funcionamento
 * Deve ser incluído no início das páginas que precisam de controle de horário
 */

require_once __DIR__ . '/../backend/utils/HorarioFuncionamento.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, não aplicar verificação de horário
    return;
}

// Verificar se é administrador (bypass)
if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'administrador') {
    // Administradores têm acesso irrestrito
    return;
}

// Verificar horário de funcionamento
$horarioFuncionamento = new HorarioFuncionamento();
$resultado = $horarioFuncionamento->verificarHorario();

if (!$resultado['permitido']) {
    // Sistema fora do horário - redirecionar para página de erro
    $_SESSION['erro_horario'] = $resultado['mensagem'];
    
    // Redirecionar para página de erro com tipo específico
    header('Location: error.php?tipo=horario&message=' . urlencode($resultado['mensagem']));
    exit;
}
?>