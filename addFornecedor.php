<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'models/Fornecedor.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem permissão para INSERIR fornecedores
// IMPORTANTE: Verificamos permissão em 'fornecedores.php' (página principal)
$temPermissao = $controllerAcesso->verificarEAutorizar('inserir', 'fornecedores.php', false);

if (!$temPermissao) {
    // Se não tiver permissão, redirecionar para error.php
    header('Location: error.php?codigo=403&tipo=warning&mensagem=Sem permissão para inserir fornecedores');
    exit;
}

// NÃO registrar acesso à página atual (addFornecedor.php não está na tabela)
// Apenas verificar permissões

$menuActive = 'fornecedores';
$isEdit = isset($_GET['id']);
$fornecedor = null;
$usuarioFornecedor = null;

// Função para verificar se fornecedor já é usuário
function verificarFornecedorUsuario($pdo, $fornecedorId) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.id_usuario, u.nome_completo, u.email, u.ativo as usuario_ativo,
                   p.nome_perfil, u.data_criacao as data_cadastro_usuario
            FROM tbl_usuarios u
            LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
            WHERE u.id_fornecedor = ? AND u.ativo = 1
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// Função para parsear endereço completo em campos separados
// Formato esperado: "Logradouro, Número - Complemento, Bairro" ou "Logradouro, Número, Bairro"
function parsearEndereco($enderecoCompleto) {
    $resultado = [
        'logradouro' => '',
        'numero' => '',
        'complemento' => '',
        'bairro' => ''
    ];
    
    if (empty($enderecoCompleto)) {
        return $resultado;
    }
    
    $endereco = trim($enderecoCompleto);
    
    // Padrão 1: "Logradouro, Número - Complemento, Bairro"
    // Padrão 2: "Logradouro, Número, Bairro"
    // Padrão 3: "Logradouro, Número"
    // Padrão 4: "Logradouro"
    
    // Primeiro, tentar encontrar número (pode ter vírgula ou espaço antes)
    if (preg_match('/^(.+?)[,\s]+(\d+)(.*)$/', $endereco, $matches)) {
        $resultado['logradouro'] = trim($matches[1]);
        $resultado['numero'] = trim($matches[2]);
        $resto = trim($matches[3]);
        
        if (!empty($resto)) {
            // Verificar se tem hífen seguido de espaço (indicando complemento)
            // Formato: " - Complemento, Bairro" ou " - Complemento"
            if (preg_match('/^-\s+(.+?)(?:,\s+(.+))?$/', $resto, $matchesComplemento)) {
                // Tem complemento com hífen e espaço
                $resultado['complemento'] = trim($matchesComplemento[1]);
                if (isset($matchesComplemento[2]) && !empty(trim($matchesComplemento[2]))) {
                    $resultado['bairro'] = trim($matchesComplemento[2]);
                }
            } else {
                // Não tem hífen, então o que vem depois do número é bairro
                // Remover vírgula inicial se houver
                $resto = ltrim($resto, ',');
                $resto = trim($resto);
                
                // Se ainda tem vírgula no meio, pode ter complemento sem hífen
                // Mas como nosso formato sempre usa hífen para complemento, 
                // se não tem hífen, é só bairro
                if (strpos($resto, ',') !== false) {
                    // Tem vírgula, pode ser "Complemento, Bairro" (sem hífen - formato antigo)
                    $partes = explode(',', $resto, 2);
                    $primeiraParte = trim($partes[0]);
                    $segundaParte = isset($partes[1]) ? trim($partes[1]) : '';
                    
                    // Verificar se primeira parte parece ser complemento
                    $palavrasComplemento = ['apto', 'apartamento', 'sala', 'bloco', 'andar', 'loja'];
                    $textoLower = strtolower($primeiraParte);
                    $ehComplemento = false;
                    foreach ($palavrasComplemento as $palavra) {
                        if (strpos($textoLower, $palavra) !== false) {
                            $ehComplemento = true;
                            break;
                        }
                    }
                    
                    if ($ehComplemento && !empty($segundaParte)) {
                        $resultado['complemento'] = $primeiraParte;
                        $resultado['bairro'] = $segundaParte;
                    } else {
                        // Se não parece complemento, tudo é bairro
                        $resultado['bairro'] = $resto;
                    }
                } else {
                    // Não tem vírgula, é só bairro
                    $resultado['bairro'] = $resto;
                }
            }
        }
    } else {
        // Não encontrou número, assumir que é só logradouro
        $resultado['logradouro'] = $endereco;
    }
    
    return $resultado;
}

if ($isEdit) {
    // Carregar dados do fornecedor para edição
    $id = $_GET['id'];
    
    try {
        $pdo = Conexao::getInstance()->getPdo();
        $fornecedorModel = new Fornecedor($pdo);
        $fornecedor = $fornecedorModel->findById($id);
        
        if (!$fornecedor) {
            // Redirecionar se fornecedor não encontrado
            header('Location: fornecedores.php?error=fornecedor_nao_encontrado');
            exit;
        }
        
        // Verificar se o fornecedor já é usuário do sistema
        $usuarioFornecedor = verificarFornecedorUsuario($pdo, $id);
        
    } catch (Exception $e) {
        // Em caso de erro, redirecionar
        header('Location: fornecedores.php?error=erro_carregar');
        exit;
    }
}

// Parsear endereço se estiver editando
$enderecoParsed = ['logradouro' => '', 'numero' => '', 'complemento' => '', 'bairro' => ''];
if ($fornecedor && !empty($fornecedor['endereco'])) {
    $enderecoParsed = parsearEndereco($fornecedor['endereco']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Novo' ?> Fornecedor | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/addfornecedores.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>

<?php include 'menu.php'; ?>

<main class="main-content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-truck fs-3 text-primary"></i>
            <h2 class="mb-0" style="font-weight:700;font-size:2rem;">
                <?= $isEdit ? 'Editar' : 'Novo' ?> Fornecedor
            </h2>
        </div>
        <a href="fornecedores.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
      
      <form id="formFornecedor" onsubmit="salvarFornecedor(event)">
        <div class="row row-cols-1 row-cols-lg-2 g-4">
          <div class="col-lg-9">
            <!-- Informações Básicas -->
            <div class="card-section mb-4">
              <div class="card-header-blue p-3"><i class="bi bi-info-circle me-2"></i>Informações Básicas</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Razão Social *</label>
                    <input type="text" class="form-control" id="razao_social" name="razao_social" 
                           placeholder="Nome da empresa" required
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['razao_social']) : '' ?>">
                    <div class="form-text">Nome oficial da empresa conforme registro</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" 
                           placeholder="Nome comercial"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['nome_fantasia']) : '' ?>">
                    <div class="form-text">Nome comercial da empresa</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">CNPJ</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" 
                           placeholder="00.000.000/0000-00"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['cnpj']) : '' ?>">
                    <div class="form-text">CNPJ da empresa (formato: 00.000.000/0000-00)</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" 
                           placeholder="000.000.000"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['inscricao_estadual']) : '' ?>">
                    <div class="form-text">Inscrição Estadual da empresa</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Informações de Contato -->
            <div class="card-section mb-4">
              <div class="card-header-green p-3"><i class="bi bi-telephone me-2"></i>Informações de Contato</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" 
                           placeholder="(11) 99999-9999"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['telefone']) : '' ?>">
                    <div class="form-text">Telefone principal da empresa</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="contato@empresa.com"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['email']) : '' ?>">
                    <div class="form-text">E-mail para contato comercial</div>
                    <div id="emailFeedback" class="invalid-feedback" style="display: none;"></div>
                    <div id="emailSuccess" class="valid-feedback" style="display: none;">E-mail disponível</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Contato Principal</label>
                    <input type="text" class="form-control" id="contato_principal" name="contato_principal" 
                           placeholder="Nome do contato principal"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['contato_principal']) : '' ?>">
                    <div class="form-text">Nome da pessoa responsável pelo contato</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Senha de Acesso *</label>
                    <input type="password" class="form-control" id="senha" name="senha" 
                           placeholder="Senha para acesso ao sistema"
                           <?= !$fornecedor ? 'required' : '' ?>>
                    <div class="form-text"><?= $fornecedor ? 'Deixe em branco para manter a senha atual' : 'Senha para o usuário acessar o sistema' ?></div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Endereço -->
            <div class="card-section mb-4">
              
              <div class="card-header-lightblue p-3"><i class="bi bi-geo-alt me-2"></i>Endereço</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">

                <div class="col-md-3">
                    <label class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" 
                           placeholder="00000-000"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['cep']) : '' ?>">
                    <div class="form-text">CEP da empresa (formato: 00000-000)</div>
                    <div id="cepLoading" class="spinner-border spinner-border-sm" role="status" style="display: none; margin-top: 5px;">
                      <span class="visually-hidden">Buscando...</span>
                    </div>
                  </div>

                  
                  <div class="col-md-6">
                    <label class="form-label">Logradouro</label>
                    <input type="text" class="form-control" id="logradouro" name="logradouro" 
                           placeholder="Rua, Avenida, etc."
                           value="<?= htmlspecialchars($enderecoParsed['logradouro']) ?>">
                    <div class="form-text">Nome da rua ou avenida</div>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           placeholder="123"
                           value="<?= htmlspecialchars($enderecoParsed['numero']) ?>">
                    <div class="form-text">Número do endereço</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" 
                           placeholder="Apto, Sala, etc."
                           value="<?= htmlspecialchars($enderecoParsed['complemento']) ?>">
                    <div class="form-text">Complemento do endereço</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" 
                           placeholder="Nome do bairro"
                           value="<?= htmlspecialchars($enderecoParsed['bairro']) ?>">
                    <div class="form-text">Bairro da empresa</div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" 
                           placeholder="Nome da cidade"
                           value="<?= $fornecedor ? htmlspecialchars($fornecedor['cidade']) : '' ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                      <option value="">Selecione...</option>
                      <option value="AC" <?= $fornecedor && $fornecedor['estado'] == 'AC' ? 'selected' : '' ?>>Acre</option>
                      <option value="AL" <?= $fornecedor && $fornecedor['estado'] == 'AL' ? 'selected' : '' ?>>Alagoas</option>
                      <option value="AP" <?= $fornecedor && $fornecedor['estado'] == 'AP' ? 'selected' : '' ?>>Amapá</option>
                      <option value="AM" <?= $fornecedor && $fornecedor['estado'] == 'AM' ? 'selected' : '' ?>>Amazonas</option>
                      <option value="BA" <?= $fornecedor && $fornecedor['estado'] == 'BA' ? 'selected' : '' ?>>Bahia</option>
                      <option value="CE" <?= $fornecedor && $fornecedor['estado'] == 'CE' ? 'selected' : '' ?>>Ceará</option>
                      <option value="DF" <?= $fornecedor && $fornecedor['estado'] == 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                      <option value="ES" <?= $fornecedor && $fornecedor['estado'] == 'ES' ? 'selected' : '' ?>>Espírito Santo</option>
                      <option value="GO" <?= $fornecedor && $fornecedor['estado'] == 'GO' ? 'selected' : '' ?>>Goiás</option>
                      <option value="MA" <?= $fornecedor && $fornecedor['estado'] == 'MA' ? 'selected' : '' ?>>Maranhão</option>
                      <option value="MT" <?= $fornecedor && $fornecedor['estado'] == 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                      <option value="MS" <?= $fornecedor && $fornecedor['estado'] == 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                      <option value="MG" <?= $fornecedor && $fornecedor['estado'] == 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                      <option value="PA" <?= $fornecedor && $fornecedor['estado'] == 'PA' ? 'selected' : '' ?>>Pará</option>
                      <option value="PB" <?= $fornecedor && $fornecedor['estado'] == 'PB' ? 'selected' : '' ?>>Paraíba</option>
                      <option value="PR" <?= $fornecedor && $fornecedor['estado'] == 'PR' ? 'selected' : '' ?>>Paraná</option>
                      <option value="PE" <?= $fornecedor && $fornecedor['estado'] == 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                      <option value="PI" <?= $fornecedor && $fornecedor['estado'] == 'PI' ? 'selected' : '' ?>>Piauí</option>
                      <option value="RJ" <?= $fornecedor && $fornecedor['estado'] == 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                      <option value="RN" <?= $fornecedor && $fornecedor['estado'] == 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                      <option value="RS" <?= $fornecedor && $fornecedor['estado'] == 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                      <option value="RO" <?= $fornecedor && $fornecedor['estado'] == 'RO' ? 'selected' : '' ?>>Rondônia</option>
                      <option value="RR" <?= $fornecedor && $fornecedor['estado'] == 'RR' ? 'selected' : '' ?>>Roraima</option>
                      <option value="SC" <?= $fornecedor && $fornecedor['estado'] == 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                      <option value="SP" <?= $fornecedor && $fornecedor['estado'] == 'SP' ? 'selected' : '' ?>>São Paulo</option>
                      <option value="SE" <?= $fornecedor && $fornecedor['estado'] == 'SE' ? 'selected' : '' ?>>Sergipe</option>
                      <option value="TO" <?= $fornecedor && $fornecedor['estado'] == 'TO' ? 'selected' : '' ?>>Tocantins</option>
                    </select>
                  </div>
                  
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3">
            <!-- Status -->
            <div class="card-section mb-4">
              <div class="card-header-gray p-3"><i class="bi bi-toggle-on me-2"></i>Status</div>
              <div class="card-body bg-white p-4">
                <div class="switch-status mb-3">
                  <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                         <?= !$fornecedor || (isset($fornecedor['ativo']) && $fornecedor['ativo']) ? 'checked' : '' ?>>
                  <label class="form-label mb-0" for="ativo"><strong>Fornecedor Ativo</strong></label>
                </div>
                <div class="form-text mb-3">
                  <i class="bi bi-info-circle text-info"></i>
                  Fornecedores inativos não aparecem nas listagens
                </div>
                
                <hr class="my-3">
                
                <div class="switch-status mb-2">
                  <input class="form-check-input" type="checkbox" id="is_fabricante" name="is_fabricante" 
                         <?= $fornecedor && isset($fornecedor['is_fabricante']) && $fornecedor['is_fabricante'] ? 'checked' : '' ?>>
                  <label class="form-label mb-0" for="is_fabricante">É Fabricante</label>
                </div>
                <div class="form-text">
                  <i class="bi bi-info-circle text-primary"></i>
                  Marque se este fornecedor também fabrica produtos
                </div>
              </div>
            </div>
            
            <!-- Informações do Sistema -->
            <div class="card-section mb-4">
              <div class="card-header-gray p-3"><i class="bi bi-gear me-2"></i>Informações do Sistema</div>
              <div class="card-body bg-white p-4">
                <div class="info-system">
                  <?php if ($fornecedor): ?>
                    <div class="mb-2">
                      <strong>ID:</strong> <?= $fornecedor['id_fornecedor'] ?>
                    </div>
                    <div class="mb-2">
                      <strong>Criado em:</strong><br>
                      <?= date('d/m/Y H:i', strtotime($fornecedor['data_criacao'])) ?>
                    </div>
                    <?php if ($fornecedor['data_atualizacao']): ?>
                    <div class="mb-2">
                      <strong>Atualizado em:</strong><br>
                      <?= date('d/m/Y H:i', strtotime($fornecedor['data_atualizacao'])) ?>
                    </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <i class="bi bi-info-circle fs-2 mb-2"></i><br>
                    Informações do sistema serão exibidas após salvar o fornecedor.
                  <?php endif; ?>
                </div>
              </div>
            </div>
            
            <!-- Status de Usuário do Sistema -->
            <?php if ($isEdit): ?>
            <div class="card-section mb-4">
              <div class="card-header-<?= $usuarioFornecedor ? 'success' : 'warning' ?> p-3">
                <i class="bi bi-person-<?= $usuarioFornecedor ? 'check' : 'x' ?> me-2"></i>
                Status de Usuário
              </div>
              <div class="card-body bg-white p-4">
                <?php if ($usuarioFornecedor): ?>
                  <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                      <strong>Fornecedor já é usuário do sistema!</strong>
                    </div>
                  </div>
                  <div class="user-info">
                    <div class="mb-2">
                      <strong>Nome:</strong> <?= htmlspecialchars($usuarioFornecedor['nome_completo']) ?>
                    </div>
                    <div class="mb-2">
                      <strong>Email:</strong> <?= htmlspecialchars($usuarioFornecedor['email']) ?>
                    </div>
                    <div class="mb-2">
                      <strong>Perfil:</strong> 
                      <span class="badge bg-primary"><?= htmlspecialchars($usuarioFornecedor['nome_perfil'] ?? 'Não definido') ?></span>
                    </div>
                    <div class="mb-2">
                      <strong>Cadastrado em:</strong><br>
                      <?= date('d/m/Y H:i', strtotime($usuarioFornecedor['data_cadastro_usuario'])) ?>
                    </div>
                    <div class="mt-3">
                      <a href="usuarios.php?search=<?= urlencode($usuarioFornecedor['email']) ?>" 
                         class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-person-gear me-1"></i>
                        Ver Usuário
                      </a>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div>
                      <strong>Usuário será criado automaticamente</strong>
                    </div>
                  </div>
                  <p class="text-muted mb-0">
                    Um usuário com perfil de fornecedor será criado automaticamente quando você salvar as alterações deste fornecedor.
                  </p>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
            
            <!-- Ações -->
            <div class="card-section mb-4">
              <div class="card-header-blue p-3"><i class="bi bi-box-arrow-down me-2"></i>Ações</div>
              <div class="card-body bg-white p-4">
                <button type="submit" class="btn btn-primary w-100 mb-2" id="btnSalvar">
                  <i class="bi bi-save me-1"></i> 
                  <span id="btnText"><?= $isEdit ? 'Atualizar' : 'Salvar' ?> Fornecedor</span>
                  <span id="btnLoading" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Salvando...
                  </span>
                </button>
                <a href="fornecedores.php" class="btn btn-outline-secondary w-100">
                  <i class="bi bi-list"></i> Listar Fornecedores
                </a>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>

<!-- Modal de Sucesso -->
<div class="modal fade" id="modalSucesso" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sucesso!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="mensagemSucesso"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="window.location.href='fornecedores.php'">
          Ir para Lista
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Máscaras para os campos
function aplicarMascaras() {
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj');
    cnpjInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = value.substring(0, 18);
    });
    
    // Máscara para telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value.substring(0, 15);
    });
    
    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    cepInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        e.target.value = value.substring(0, 9);
    });
    
    // Buscar endereço automaticamente quando CEP for preenchido
    cepInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarEnderecoPorCEP(cep);
        }
    });
    
    // Validação de email em tempo real
    const emailInput = document.getElementById('email');
    let emailValidationTimeout;
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && email.includes('@')) {
            verificarEmailExistente(email);
        }
    });
    
    emailInput.addEventListener('input', function() {
        // Limpar feedbacks ao digitar
        clearTimeout(emailValidationTimeout);
        document.getElementById('emailFeedback').style.display = 'none';
        document.getElementById('emailSuccess').style.display = 'none';
        emailInput.classList.remove('is-invalid', 'is-valid');
    });
}

// Buscar endereço por CEP usando ViaCEP
function buscarEnderecoPorCEP(cep) {
    const cepLoading = document.getElementById('cepLoading');
    cepLoading.style.display = 'inline-block';
    
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            cepLoading.style.display = 'none';
            
            if (data.erro) {
                console.warn('CEP não encontrado');
                return;
            }
            
            // Preencher campos automaticamente apenas se estiverem vazios
            if (data.logradouro) {
                const logradouroAtual = document.getElementById('logradouro').value;
                if (!logradouroAtual || logradouroAtual.trim() === '') {
                    document.getElementById('logradouro').value = data.logradouro;
                }
            }
            
            if (data.bairro) {
                const bairroAtual = document.getElementById('bairro').value;
                if (!bairroAtual || bairroAtual.trim() === '') {
                    document.getElementById('bairro').value = data.bairro;
                }
            }
            
            if (data.localidade) {
                document.getElementById('cidade').value = data.localidade;
            }
            
            if (data.uf) {
                document.getElementById('estado').value = data.uf;
            }
        })
        .catch(error => {
            cepLoading.style.display = 'none';
            console.error('Erro ao buscar CEP:', error);
        });
}

// Verificar se email já existe
function verificarEmailExistente(email) {
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    const fornecedorId = <?= $fornecedor ? $fornecedor['id_fornecedor'] : 'null' ?>;
    const url = `api/fornecedores.php?action=verificar-email&email=${encodeURIComponent(email)}${isEdit ? '&id=' + fornecedorId : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const emailInput = document.getElementById('email');
            const emailFeedback = document.getElementById('emailFeedback');
            const emailSuccess = document.getElementById('emailSuccess');
            
            if (data.success) {
                if (data.email_existe) {
                    emailInput.classList.add('is-invalid');
                    emailInput.classList.remove('is-valid');
                    emailFeedback.textContent = data.mensagem;
                    emailFeedback.style.display = 'block';
                    emailSuccess.style.display = 'none';
                } else {
                    emailInput.classList.add('is-valid');
                    emailInput.classList.remove('is-invalid');
                    emailFeedback.style.display = 'none';
                    emailSuccess.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao verificar email:', error);
        });
}

// Salvar fornecedor
function salvarFornecedor(event) {
    event.preventDefault();
    
    // Verificar se email é inválido antes de enviar
    const emailInput = document.getElementById('email');
    if (emailInput.classList.contains('is-invalid')) {
        alert('Por favor, corrija o e-mail antes de salvar. Este e-mail já está cadastrado.');
        emailInput.focus();
        return;
    }
    
    const btnSalvar = document.getElementById('btnSalvar');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    // Desabilitar botão e mostrar loading
    btnSalvar.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    
    // Montar endereço completo a partir dos campos separados
    // Formato: "Logradouro, Número - Complemento, Bairro"
    const logradouro = document.getElementById('logradouro').value.trim();
    const numero = document.getElementById('numero').value.trim();
    const complemento = document.getElementById('complemento').value.trim();
    const bairro = document.getElementById('bairro').value.trim();
    
    let enderecoCompleto = '';
    if (logradouro) {
        enderecoCompleto = logradouro;
        if (numero) {
            enderecoCompleto += ', ' + numero;
        }
        if (complemento) {
            enderecoCompleto += ' - ' + complemento;
        }
        if (bairro) {
            // Sempre adiciona vírgula antes do bairro se já tem algo antes
            enderecoCompleto += ', ' + bairro;
        }
    }
    
    // Coletar dados do formulário
    const formData = {
        razao_social: document.getElementById('razao_social').value,
        nome_fantasia: document.getElementById('nome_fantasia').value,
        cnpj: document.getElementById('cnpj').value,
        inscricao_estadual: document.getElementById('inscricao_estadual').value,
        endereco: enderecoCompleto,
        cidade: document.getElementById('cidade').value,
        estado: document.getElementById('estado').value,
        cep: document.getElementById('cep').value,
        telefone: document.getElementById('telefone').value,
        email: document.getElementById('email').value,
        contato_principal: document.getElementById('contato_principal').value,
        senha: document.getElementById('senha').value,
        ativo: document.getElementById('ativo').checked ? 1 : 0,
        is_fabricante: document.getElementById('is_fabricante').checked ? 1 : 0
    };
    
    // Determinar URL e método
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    const url = isEdit ? `api/fornecedores.php?action=update&id=<?= $fornecedor ? $fornecedor['id_fornecedor'] : '' ?>` : 'api/fornecedores.php?action=create';
    const method = isEdit ? 'PUT' : 'POST';
    
    // Enviar requisição
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar modal de sucesso
            document.getElementById('mensagemSucesso').textContent = data.message;
            const modal = new bootstrap.Modal(document.getElementById('modalSucesso'));
            modal.show();
        } else {
            // Mostrar erro
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro ao salvar fornecedor:', error);
        alert('Erro ao salvar fornecedor. Verifique a conexão e tente novamente.');
    })
    .finally(() => {
        // Reabilitar botão
        btnSalvar.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    aplicarMascaras();
});
</script>
</body>
</html>
