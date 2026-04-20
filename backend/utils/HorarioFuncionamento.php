<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Configuracao.php';

class HorarioFuncionamento {
    private $configuracao;
    
    public function __construct() {
        $pdo = Conexao::getInstance()->getPdo();
        $this->configuracao = new Configuracao($pdo);
    }
    
    /**
     * Verificar se o sistema está dentro do horário de funcionamento
     * @return array ['permitido' => bool, 'mensagem' => string]
     */
    public function verificarHorario() {
        // Verificar se o controle de horário está ativo
        $horarioAtivo = $this->configuracao->getValor('horario_funcionamento_ativo', '0');
        
        if ($horarioAtivo !== '1') {
            return ['permitido' => true, 'mensagem' => ''];
        }
        
        $agora = new DateTime();
        $diaSemana = (int)$agora->format('w'); // 0 = domingo, 1 = segunda, ..., 6 = sábado
        $horaAtual = $agora->format('H:i');
        
        // Verificar domingo
        if ($diaSemana === 0) {
            $domingoAtivo = $this->configuracao->getValor('horario_domingo_ativo', '0');
            
            if ($domingoAtivo !== '1') {
                return [
                    'permitido' => false,
                    'mensagem' => 'O sistema não está disponível aos domingos.'
                ];
            }
            
            $inicioD = $this->configuracao->getValor('horario_inicio_domingo', '08:00');
            $fimD = $this->configuracao->getValor('horario_fim_domingo', '12:00');
            
            if ($horaAtual < $inicioD || $horaAtual > $fimD) {
                return [
                    'permitido' => false,
                    'mensagem' => "O sistema está disponível aos domingos das {$inicioD} às {$fimD}."
                ];
            }
            
            return ['permitido' => true, 'mensagem' => ''];
        }
        
        // Verificar sábado
        if ($diaSemana === 6) {
            $inicioS = $this->configuracao->getValor('horario_inicio_sabado', '08:00');
            $fimS = $this->configuracao->getValor('horario_fim_sabado', '12:00');
            
            if ($horaAtual < $inicioS || $horaAtual > $fimS) {
                return [
                    'permitido' => false,
                    'mensagem' => "O sistema está disponível aos sábados das {$inicioS} às {$fimS}."
                ];
            }
            
            return ['permitido' => true, 'mensagem' => ''];
        }
        
        // Verificar segunda a sexta (1-5)
        if ($diaSemana >= 1 && $diaSemana <= 5) {
            $inicioSem = $this->configuracao->getValor('horario_inicio_semana', '08:00');
            $fimSem = $this->configuracao->getValor('horario_fim_semana', '18:00');
            
            if ($horaAtual < $inicioSem || $horaAtual > $fimSem) {
                return [
                    'permitido' => false,
                    'mensagem' => "O sistema está disponível de segunda a sexta das {$inicioSem} às {$fimSem}."
                ];
            }
            
            return ['permitido' => true, 'mensagem' => ''];
        }
        
        return ['permitido' => true, 'mensagem' => ''];
    }
    
    /**
     * Middleware para verificar horário de funcionamento
     * Redireciona para página de erro se fora do horário
     */
    public static function verificarAcesso() {
        $horario = new self();
        $resultado = $horario->verificarHorario();
        
        if (!$resultado['permitido']) {
            // Salvar mensagem na sessão para exibir na página de erro
            session_start();
            $_SESSION['erro_horario'] = $resultado['mensagem'];
            
            // Redirecionar para página de erro
            header('Location: /sistemas/_estoquegrupoSorrisos/error.php?tipo=horario');
            exit;
        }
    }
    
    /**
     * Verificar se é um usuário administrador (bypass do horário)
     * @return bool
     */
    private function isAdmin() {
        session_start();
        return isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'Administrador';
    }
    
    /**
     * Verificar horário com bypass para administradores
     */
    public static function verificarAcessoComBypass() {
        $horario = new self();
        
        // Administradores podem acessar a qualquer hora
        if ($horario->isAdmin()) {
            return;
        }
        
        $resultado = $horario->verificarHorario();
        
        if (!$resultado['permitido']) {
            session_start();
            $_SESSION['erro_horario'] = $resultado['mensagem'];
            header('Location: /sistemas/_estoquegrupoSorrisos/error.php?tipo=horario');
            exit;
        }
    }
    
    /**
     * Obter mensagem personalizada de horário
     */
    public function getMensagemHorario() {
        $agora = new DateTime();
        $diaSemana = (int)$agora->format('w'); // 0 = domingo, 6 = sábado
        $horaAtual = $agora->format('H:i');
        
        $mensagem = 'Sistema fora do horário de funcionamento. ';
        
        if ($diaSemana >= 1 && $diaSemana <= 5) {
            // Segunda a sexta
            $inicio = $this->configuracao->getValor('horario_inicio_semana', '08:00');
            $fim = $this->configuracao->getValor('horario_fim_semana', '18:00');
            $mensagem .= "Horário de funcionamento: Segunda a Sexta das {$inicio} às {$fim}.";
        } elseif ($diaSemana == 6) {
            // Sábado
            $inicio = $this->configuracao->getValor('horario_inicio_sabado', '08:00');
            $fim = $this->configuracao->getValor('horario_fim_sabado', '12:00');
            $mensagem .= "Horário de funcionamento: Sábado das {$inicio} às {$fim}.";
        } else {
            // Domingo
            if ($this->configuracao->getValor('horario_domingo_ativo', '0') == '1') {
                $inicio = $this->configuracao->getValor('horario_inicio_domingo', '08:00');
                $fim = $this->configuracao->getValor('horario_fim_domingo', '12:00');
                $mensagem .= "Horário de funcionamento: Domingo das {$inicio} às {$fim}.";
            } else {
                $mensagem .= 'Sistema não funciona aos domingos.';
            }
        }
        
        return $mensagem;
    }
}
?>