<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/config.php';

// Verificar horário de funcionamento
require_once 'middleware/horario_middleware.php';

$menuActive = 'materiais';

// Obter informações do usuário logado
$user = getCurrentUser();

// Verificar se é edição
$editando = isset($_GET['id']) && !empty($_GET['id']);
$materialId = $editando ? $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $editando ? 'Editar' : 'Novo'; ?> Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/addmateriais.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-seam fs-3 text-primary"></i>
            <h2 class="mb-0" style="font-weight:700;font-size:2rem;">
                <?php echo $editando ? 'Editar' : 'Novo'; ?> Material
            </h2>
        </div>
        <a href="material" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
    
    <!-- Indicador da Filial Selecionada -->
    <div class="alert alert-info d-flex align-items-center mb-3" id="filial-indicator" style="display: none;">
        <i class="bi bi-building me-2"></i>
        <div>
            <strong><?php echo $editando ? 'Editando material da filial:' : 'Criando material para a filial:'; ?></strong> 
            <span id="filial-nome">Carregando...</span>
            <?php if ($editando): ?>
                <small class="d-block text-muted">Os valores de estoque mínimo/máximo serão salvos nesta filial</small>
            <?php else: ?>
                <small class="d-block text-muted">O material será criado para esta filial</small>
            <?php endif; ?>
        </div>
    </div>
            
    <!-- Alertas -->
    <div id="alertContainer"></div>
            
            <form id="materialForm">
                <div class="row row-cols-1 row-cols-lg-2 g-4">
                    <div class="col-lg-9">
                        <!-- Informações Básicas -->
                        <div class="card-section mb-4">
                            <div class="card-header-blue p-3">Informações Básicas</div>
                            <div class="card-body bg-white p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Código do Material *</label>
                                        <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ex: MAT001" required>
                                        <div class="form-text">Código único para identificação</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Código de Barras</label>
                                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" placeholder="7891234567890">
                                        <div class="form-text">
                                            <input type="checkbox" class="form-check-input me-1" id="usar_codigo_barras"> 
                                            Código de barras para leitura automática
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Nome do Material *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo do material" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Categoria *</label>
                                        <div class="input-group">
                                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                                <option value="">Selecione uma categoria</option>
                                            </select>
                                            <button type="button" class="btn btn-primary" onclick="abrirModalCategorias()" title="Gerenciar Categorias">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Certificado de Aprovação (CA)</label>
                                        <input type="text" class="form-control" id="ca" name="ca" placeholder="Ex: CA-12345, ABC-2024-001">
                                        <div class="form-text" id="ca-help">
                                            <i class="bi bi-info-circle text-info"></i>
                                            Campo obrigatório para materiais EPI (Equipamento de Proteção Individual)
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fornecedor</label>
                                        <div class="input-group">
                                            <select class="form-select" id="id_fornecedor" name="id_fornecedor">
                                                <option value="">Selecione um fornecedor</option>
                                            </select>
                                            <button type="button" class="btn btn-primary" onclick="abrirModalFornecedores()" title="Gerenciar Fornecedores">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Quem vende o material (apenas fornecedores)</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Unidade de Medida *</label>
                                        <select class="form-select" id="id_unidade" name="id_unidade" required>
                                            <option value="">Selecione uma unidade</option>
                                            <option value="1">UN - Unidade</option>
                                            <option value="2">KG - Quilograma</option>
                                            <option value="3">M - Metro</option>
                                            <option value="4">M² - Metro Quadrado</option>
                                            <option value="5">M³ - Metro Cúbico</option>
                                            <option value="6">L - Litro</option>
                                            <option value="7">CX - Caixa</option>
                                            <option value="8">PCT - Pacote</option>
                                            <option value="9">ROL - Rolo</option>
                                            <option value="10">PAR - Par</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fabricante</label>
                                        <div class="input-group">
                                            <select class="form-select" id="id_fabricante" name="id_fabricante">
                                                <option value="">Selecione um fabricante</option>
                                            </select>
                                            <button type="button" class="btn btn-primary" onclick="abrirModalFabricantes()" title="Gerenciar Fabricantes">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Quem fabrica/produz o material (apenas fabricantes)</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="2" placeholder="Descrição detalhada do material"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Controle de Estoque -->
                        <div class="card-section mb-4">
                            <div class="card-header-green p-3">Controle de Estoque</div>
                            <div class="card-body bg-white p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Preço Unitário</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" class="form-control" id="preco_unitario" name="preco_unitario" placeholder="0,00" data-mask="currency">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Localização no Estoque</label>
                                        <input type="text" class="form-control" id="localizacao_estoque" name="localizacao_estoque" placeholder="Ex: Prateleira A-01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Estoque Atual</label>
                                        <input type="text" class="form-control" id="estoque_atual" name="estoque_atual" placeholder="0,00" data-mask="decimal">
                                        <div class="form-text">Atualizado automaticamente pelas movimentações</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" id="label-estoque-minimo">
                                            <span id="label-estoque-minimo-text">Estoque Mínimo</span>
                                            <span id="label-estoque-minimo-editando" style="display: none;"> (Filial Selecionada)</span>
                                        </label>
                                        <input type="text" class="form-control" id="estoque_minimo_padrao" name="estoque_minimo_padrao" placeholder="0,00" data-mask="decimal">
                                        <div class="form-text" id="texto-estoque-minimo">
                                            <i class="bi bi-info-circle text-info"></i>
                                            <span id="texto-estoque-minimo-novo">Valor padrão sugerido. Configure por filial em <strong>Materiais > Editar Estoque</strong></span>
                                            <span id="texto-estoque-minimo-editando" style="display: none;">Será salvo na filial selecionada</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" id="label-estoque-maximo">
                                            <span id="label-estoque-maximo-text">Estoque Máximo</span>
                                            <span id="label-estoque-maximo-editando" style="display: none;"> (Filial Selecionada)</span>
                                        </label>
                                        <input type="text" class="form-control" id="estoque_maximo_padrao" name="estoque_maximo_padrao" placeholder="0,00" data-mask="decimal">
                                        <div class="form-text" id="texto-estoque-maximo">
                                            <i class="bi bi-info-circle text-info"></i>
                                            <span id="texto-estoque-maximo-novo">Valor padrão sugerido. Configure por filial em <strong>Materiais > Editar Estoque</strong></span>
                                            <span id="texto-estoque-maximo-editando" style="display: none;">Será salvo na filial selecionada</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="card-section mb-4">
                            <div class="card-header-lightblue p-3">Observações</div>
                            <div class="card-body bg-white p-4">
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="2" placeholder="Observações adicionais sobre o material"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <!-- Status -->
                        <div class="card-section mb-4">
                            <div class="card-header-gray p-3">Status</div>
                            <div class="card-body bg-white p-4">
                                <div class="switch-status mb-2">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                    <label class="form-label mb-0" for="ativo">Material Ativo</label>
                                </div>
                                <div class="form-text">Materiais inativos não aparecem nas listagens</div>
                            </div>
                        </div>
                        
                        <!-- Informações do Sistema -->
                        <div class="card-section mb-4">
                            <div class="card-header-gray p-3">Informações do Sistema</div>
                            <div class="card-body bg-white p-4">
                                <div class="info-system">
                                    <i class="bi bi-info-circle fs-2 mb-2"></i><br>
                                    <div id="info-sistema">
                                        <?php if ($editando): ?>
                                            <strong>Editando Material</strong><br>
                                            ID: <?php echo $materialId; ?><br>
                                            Última atualização: <span id="ultima-atualizacao">Carregando...</span>
                                        <?php else: ?>
                                            Informações do sistema serão exibidas após salvar o material.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ações -->
                        <div class="card-section mb-4">
                            <div class="card-header-blue p-3">Ações</div>
                            <div class="card-body bg-white p-4">
                                <button type="submit" class="btn btn-primary w-100 mb-2" id="btnSalvar">
                                    <i class="bi bi-save me-1"></i> 
                                    <span id="btnText"><?php echo $editando ? 'Atualizar' : 'Salvar'; ?> Material</span>
                                </button>
                                <a href="material.php" class="btn btn-outline-secondary w-100"><i class="bi bi-list"></i> Listar Materiais</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variáveis globais
let editando = <?php echo $editando ? 'true' : 'false'; ?>;
let materialId = <?php echo $materialId ? "'$materialId'" : 'null'; ?>;

// Função para atualizar labels quando estiver editando
function atualizarLabelsEdicao() {
    if (editando) {
        // Mostrar textos de edição
        const labelMinEditando = document.getElementById('label-estoque-minimo-editando');
        const labelMaxEditando = document.getElementById('label-estoque-maximo-editando');
        const textoMinNovo = document.getElementById('texto-estoque-minimo-novo');
        const textoMaxNovo = document.getElementById('texto-estoque-maximo-novo');
        const textoMinEditando = document.getElementById('texto-estoque-minimo-editando');
        const textoMaxEditando = document.getElementById('texto-estoque-maximo-editando');
        
        if (labelMinEditando) labelMinEditando.style.display = 'inline';
        if (labelMaxEditando) labelMaxEditando.style.display = 'inline';
        if (textoMinNovo) textoMinNovo.style.display = 'none';
        if (textoMaxNovo) textoMaxNovo.style.display = 'none';
        if (textoMinEditando) textoMinEditando.style.display = 'inline';
        if (textoMaxEditando) textoMaxEditando.style.display = 'inline';
    }
}



// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    aplicarMascaras();
    carregarCategorias();
    carregarFornecedores();
    carregarFabricantes();
    carregarFilialSelecionada();
    
    if (editando) {
        carregarMaterial();
        atualizarLabelsEdicao();
    }
    
    // Event listeners
    document.getElementById('materialForm').addEventListener('submit', salvarMaterial);
    document.getElementById('usar_codigo_barras').addEventListener('change', toggleCodigoBarras);

});

// Função para carregar e exibir a filial selecionada
async function carregarFilialSelecionada() {
    const filialId = localStorage.getItem('filialSelecionada');
    const indicator = document.getElementById('filial-indicator');
    const filialNome = document.getElementById('filial-nome');
    
    if (!indicator || !filialNome) {
        console.warn('Elementos do indicador de filial não encontrados');
        return;
    }
    
    if (filialId) {
        try {
            // Buscar informações da filial na API
            const response = await fetch(`backend/api/filiais.php?action=list`);
            const data = await response.json();
            
            if (data.success && data.filiais) {
                const filial = data.filiais.find(f => f.id == filialId);
                if (filial) {
                    filialNome.textContent = filial.nome;
                    indicator.style.display = 'flex';
                    indicator.className = 'alert alert-info d-flex align-items-center mb-3';
                    console.log('✅ Filial exibida:', filial.nome);
                } else {
                    indicator.style.display = 'none';
                }
            } else {
                indicator.style.display = 'none';
            }
        } catch (error) {
            console.error('Erro ao carregar filial:', error);
            // Tentar buscar de outra API
            try {
                const response2 = await fetch(`api/materiais_nova_estrutura.php?action=filiais`);
                const data2 = await response2.json();
                
                if (data2.success && data2.data) {
                    const filial = data2.data.find(f => f.id_filial == filialId);
                    if (filial) {
                        filialNome.textContent = filial.nome_filial;
                        indicator.style.display = 'flex';
                        indicator.className = 'alert alert-info d-flex align-items-center mb-3';
                        console.log('✅ Filial exibida (API alternativa):', filial.nome_filial);
                    } else {
                        indicator.style.display = 'none';
                    }
                } else {
                    indicator.style.display = 'none';
                }
            } catch (error2) {
                console.error('Erro ao carregar filial (API alternativa):', error2);
                indicator.style.display = 'none';
            }
        }
    } else {
        // Nenhuma filial selecionada
        filialNome.textContent = 'Nenhuma filial selecionada';
        indicator.style.display = 'flex';
        indicator.className = 'alert alert-warning d-flex align-items-center mb-3';
        console.log('⚠️ Nenhuma filial selecionada');
    }
}

// Função para aplicar máscaras nos campos de valores
function aplicarMascaras() {
    // Máscara para preço (R$) - formato brasileiro
    $('#preco_unitario').mask('#.##0,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    // Máscara para campos de estoque - formato brasileiro
    $('#estoque_atual').mask('#.##0,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    $('#estoque_minimo_padrao').mask('#.##0,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    $('#estoque_maximo_padrao').mask('#.##0,00', {
        reverse: true,
        placeholder: '0,00'
    });
}

// Funções de carregamento
async function carregarCategorias() {
    try {
        const response = await fetch('api/materiais_nova_estrutura.php?action=categorias');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('id_categoria');
            data.data.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = categoria.nome_categoria;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar categorias',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }
}

async function carregarFornecedores() {
    try {
        // MUDANÇA: Carrega TODOS os fornecedores ativos (fabricantes ou não)
        // Um fabricante pode também ser fornecedor direto
        const response = await fetch('api/fornecedores.php?action=ativos');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('id_fornecedor');
            // Limpar opções existentes (exceto a primeira)
            const selectedValue = select.value;
            select.innerHTML = '<option value="">Selecione um fornecedor</option>';
            data.data.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.razao_social;
                select.appendChild(option);
            });
            // Restaurar valor selecionado se ainda existir
            if (selectedValue) {
                select.value = selectedValue;
            }
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar fornecedores',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }
}

async function carregarFabricantes() {
    try {
        // IMPORTANTE: Carrega apenas fabricantes (is_fabricante = 1)
        const response = await fetch('api/fornecedores.php?action=fabricantes');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('id_fabricante');
            // Limpar opções existentes (exceto a primeira)
            const selectedValue = select.value;
            select.innerHTML = '<option value="">Selecione um fabricante</option>';
            data.data.forEach(fabricante => {
                const option = document.createElement('option');
                option.value = fabricante.id_fornecedor;
                option.textContent = fabricante.razao_social;
                select.appendChild(option);
            });
            // Restaurar valor selecionado se ainda existir
            if (selectedValue) {
                select.value = selectedValue;
            }
        }
    } catch (error) {
        console.error('Erro ao carregar fabricantes:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar fabricantes',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }
}

async function carregarMaterial() {
    try {
        const response = await fetch(`api/materiais_nova_estrutura.php?action=get&id=${materialId}`);
        const data = await response.json();
        
        if (data.success) {
            preencherFormulario(data.data);
            
            // Buscar dados do estoque da filial selecionada
            const filialId = localStorage.getItem('filialSelecionada');
            if (filialId && data.data.id_catalogo) {
                try {
                    const estoqueResponse = await fetch(`api/materiais_nova_estrutura.php?action=buscar-estoque&id_catalogo=${data.data.id_catalogo}&id_filial=${filialId}`);
                    const estoqueData = await estoqueResponse.json();
                    
                    if (estoqueData.success && estoqueData.estoque) {
                        // Preencher campos com valores da filial (se existirem), senão usar padrão
                        const estoque = estoqueData.estoque;
                        
                        // Estoque mínimo: usar valor da filial se existir, senão usar padrão
                        const estoqueMinimo = estoque.estoque_minimo !== null && estoque.estoque_minimo !== undefined 
                            ? estoque.estoque_minimo 
                            : (estoque.estoque_minimo_padrao || data.data.estoque_minimo_padrao || 0);
                        
                        // Estoque máximo: usar valor da filial se existir, senão usar padrão
                        const estoqueMaximo = estoque.estoque_maximo !== null && estoque.estoque_maximo !== undefined 
                            ? estoque.estoque_maximo 
                            : (estoque.estoque_maximo_padrao || data.data.estoque_maximo_padrao || 0);
                        
                        if (estoqueMinimo > 0) {
                            document.getElementById('estoque_minimo_padrao').value = parseFloat(estoqueMinimo).toFixed(2).replace('.', ',');
                        }
                        
                        if (estoqueMaximo > 0) {
                            document.getElementById('estoque_maximo_padrao').value = parseFloat(estoqueMaximo).toFixed(2).replace('.', ',');
                        }
                    }
                } catch (error) {
                    console.warn('Não foi possível carregar dados do estoque da filial:', error);
                }
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao carregar material: ' + data.error,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        console.error('Erro ao carregar material:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar material',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }
}

function preencherFormulario(material) {
    document.getElementById('codigo').value = material.codigo || '';
    document.getElementById('codigo_barras').value = material.codigo_barras || '';
    document.getElementById('ca').value = material.ca || '';
    document.getElementById('nome').value = material.nome || '';
    document.getElementById('descricao').value = material.descricao || '';
    document.getElementById('id_categoria').value = material.id_categoria || '';
    document.getElementById('id_fornecedor').value = material.id_fornecedor || '';
    document.getElementById('id_fabricante').value = material.id_fabricante || '';
    document.getElementById('id_unidade').value = material.id_unidade || '';
    
    // Verificar se é categoria EPI para mostrar campo CA
    if (material.id_categoria) {

    }
    
    // Formatar valores monetários e decimais
    if (material.preco_unitario) {
        document.getElementById('preco_unitario').value = parseFloat(material.preco_unitario).toFixed(2).replace('.', ',');
    }
    
    document.getElementById('localizacao_estoque').value = material.localizacao_estoque || '';
    
    if (material.estoque_atual) {
        document.getElementById('estoque_atual').value = parseFloat(material.estoque_atual).toFixed(2).replace('.', ',');
    }
    
    // Carregar valores padrão do catálogo (não por filial)
    if (material.estoque_minimo_padrao) {
        document.getElementById('estoque_minimo_padrao').value = parseFloat(material.estoque_minimo_padrao).toFixed(2).replace('.', ',');
    }
    
    if (material.estoque_maximo_padrao) {
        document.getElementById('estoque_maximo_padrao').value = parseFloat(material.estoque_maximo_padrao).toFixed(2).replace('.', ',');
    }
    
    document.getElementById('observacoes').value = material.observacoes || '';
    document.getElementById('ativo').checked = material.ativo == 1;
    
    // Atualizar informações do sistema
    if (material.data_atualizacao) {
        document.getElementById('ultima-atualizacao').textContent = new Date(material.data_atualizacao).toLocaleString('pt-BR');
    }
    
    // Habilitar código de barras se existir
    if (material.codigo_barras) {
        document.getElementById('usar_codigo_barras').checked = true;
        toggleCodigoBarras();
    }
}

// Funções de salvamento
async function salvarMaterial(e) {
    e.preventDefault();
    
    const btnSalvar = document.getElementById('btnSalvar');
    const btnText = document.getElementById('btnText');
    
    // Desabilitar botão
    btnSalvar.disabled = true;
    btnText.textContent = editando ? 'Atualizando...' : 'Salvando...';
    
    try {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Converter checkbox para boolean
        data.ativo = data.ativo ? 1 : 0;
        
        // Converter valores formatados para números usando jQuery Mask Plugin
        if (data.preco_unitario) {
            data.preco_unitario = parseFloat($('#preco_unitario').val().replace(/\./g, '').replace(',', '.'));
        }
        
        if (data.estoque_atual) {
            data.estoque_atual = parseFloat($('#estoque_atual').val().replace(/\./g, '').replace(',', '.'));
        }
        
        // Capturar valores padrão do catálogo
        if ($('#estoque_minimo_padrao').val()) {
            data.estoque_minimo_padrao = parseFloat($('#estoque_minimo_padrao').val().replace(/\./g, '').replace(',', '.'));
        }
        
        if ($('#estoque_maximo_padrao').val()) {
            data.estoque_maximo_padrao = parseFloat($('#estoque_maximo_padrao').val().replace(/\./g, '').replace(',', '.'));
        }
        
        // Preparar dados para nova estrutura centralizada
        const filialId = localStorage.getItem('filialSelecionada');
        console.log('🔍 Debug - Filial do localStorage:', filialId);
        console.log('🔍 Debug - Editando?', editando);
        
        if (!editando && !filialId) {
            Swal.fire({
                icon: 'warning',
                title: 'Filial Não Selecionada',
                text: 'Nenhuma filial selecionada. Selecione uma filial na tela inicial.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Separar dados do catálogo dos dados de estoque
        const catalogoData = {
            codigo: data.codigo,
            nome: data.nome,
            descricao: data.descricao,
            id_categoria: data.id_categoria,
            id_fornecedor: data.id_fornecedor,
            id_fabricante: data.id_fabricante,
            id_unidade: data.id_unidade,
            codigo_barras: data.codigo_barras,
            ca: data.ca,
            preco_unitario_padrao: data.preco_unitario,
            estoque_minimo_padrao: data.estoque_minimo_padrao || 0,
            estoque_maximo_padrao: data.estoque_maximo_padrao || 0,
            observacoes: data.observacoes
        };
        
        // Para novos materiais, criar estoque em TODAS as filiais (sem definir mínimo/máximo - será configurado depois por filial)
        let estoqueData = {};
        
        if (!editando) {
            // NOVO MATERIAL: Criar estoque em todas as filiais
            // IMPORTANTE: Não definir estoque_minimo e estoque_maximo aqui - devem ser configurados por filial depois
            estoqueData = {
                criar_em_todas_filiais: true, // Flag para API criar em todas as filiais
                estoque_atual: data.estoque_atual || 0,
                // Não definir estoque_minimo e estoque_maximo - usarão valores padrão do catálogo como fallback
                preco_unitario: data.preco_unitario || 0,
                localizacao_estoque: data.localizacao_estoque || 'A definir',
                observacoes_estoque: data.observacoes_estoque || 'Estoque inicial criado automaticamente. Configure estoque mínimo/máximo por filial.'
            };
        } else {
            // EDIÇÃO: Atualizar estoque da filial atual (se fornecido)
            if (filialId) {
                estoqueData = {
                    id_filial: parseInt(filialId),
                    estoque_atual: data.estoque_atual || 0,
                    // IMPORTANTE: Salvar estoque mínimo/máximo na filial selecionada
                    estoque_minimo: data.estoque_minimo_padrao || null,
                    estoque_maximo: data.estoque_maximo_padrao || null,
                    preco_unitario: data.preco_unitario || 0,
                    localizacao_estoque: data.localizacao_estoque || '',
                    observacoes_estoque: data.observacoes_estoque || ''
                };
            }
        }
        
        // Dados finais para enviar à API
        const finalData = {
            catalogo: catalogoData,
            estoque: estoqueData
        };
        
        console.log('✅ Dados separados para nova estrutura:', finalData);
        console.log('🏥 Criar estoque em todas as filiais:', !editando && estoqueData.criar_em_todas_filiais);
        console.log('📦 Código de barras sendo enviado:', finalData.catalogo.codigo_barras);
        
        const url = editando 
            ? `api/materiais_nova_estrutura.php?action=update&id=${materialId}`
            : 'api/materiais_nova_estrutura.php?action=create';
        
        const method = editando ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(finalData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            
            // Mostrar SweetAlert temporário que fecha automaticamente após 2 segundos
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: result.message,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                background: '#d4edda',
                color: '#155724',
                iconColor: '#28a745'
            });
            
            if (!editando) {
                // Limpar formulário após salvar
                setTimeout(() => {
                    e.target.reset();
                    document.getElementById('ativo').checked = true;
                }, 2000);
            }
        } else {
            // Para erros, usar SweetAlert com botão de confirmação
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + result.error,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        console.error('Erro ao salvar material:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao salvar material: ' + error.message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    } finally {
        // Reabilitar botão
        btnSalvar.disabled = false;
        btnText.textContent = editando ? 'Atualizar Material' : 'Salvar Material';
    }
}

// Funções auxiliares
function toggleCodigoBarras() {
    const checkbox = document.getElementById('usar_codigo_barras');
    const input = document.getElementById('codigo_barras');
    
    if (checkbox.checked) {
        input.required = true;
        input.disabled = false;
        input.readOnly = false;
        input.classList.remove('bg-light');
        input.focus();
    } else {
        input.required = false;
        input.disabled = false; // Mantém habilitado para enviar no form
        input.readOnly = true;  // Mas readonly para não editar
        input.classList.add('bg-light'); // Visual de desabilitado
        // NÃO limpa o valor automaticamente - deixa o usuário decidir
        // Se quiser limpar, ele deve apagar manualmente antes de desmarcar
    }
}



function showAlert(message, type = 'info') {
    const container = document.getElementById('alertContainer');
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    container.innerHTML = alertHtml;
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Validações
document.getElementById('codigo').addEventListener('blur', async function() {
    if (this.value && !editando) {
        try {
            const response = await fetch(`api/materiais_nova_estrutura.php?action=check-codigo&codigo=${this.value}`);
            const data = await response.json();
            
            if (data.exists) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Código Duplicado',
                    text: 'Este código já existe. Escolha outro código.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ffc107'
                });
                this.focus();
            }
        } catch (error) {
            console.error('Erro ao verificar código:', error);
        }
    }
});




// Modal de Gerenciamento de Categorias
let modalCategorias;

function abrirModalCategorias() {
    if (!modalCategorias) {
        modalCategorias = new bootstrap.Modal(document.getElementById('modalCategorias'));
    }
    carregarListaCategorias();
    modalCategorias.show();
}

async function carregarListaCategorias() {
    const tbody = document.querySelector('#tabelaCategorias tbody');
    if (!tbody) {
        console.error('Elemento #tabelaCategorias tbody não encontrado');
        return;
    }
    
    // Mostrar loading
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-hourglass-split fs-1"></i><div class="mt-2">Carregando categorias...</div></td></tr>';
    
    try {
        console.log('📡 Buscando categorias...');
        
        // Usar a mesma API que já funciona no select
        const response = await fetch('api/materiais_nova_estrutura.php?action=categorias', {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📥 Resposta da API:', data);
        
        tbody.innerHTML = '';
        
        // A API api/materiais_nova_estrutura.php retorna { success: true, data: [...] }
        let categorias = [];
        
        if (data.success && data.data && Array.isArray(data.data)) {
            categorias = data.data;
        } else if (Array.isArray(data)) {
            categorias = data;
        }
        
        console.log(`📊 Categorias encontradas: ${categorias.length}`, categorias);
        
        if (categorias.length > 0) {
            categorias.forEach(categoria => {
                const tr = document.createElement('tr');
                const nomeCategoria = categoria.nome_categoria || categoria.nome || '-';
                const nomeEscapado = nomeCategoria.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const isAtivo = categoria.ativo == 1 || categoria.ativo === '1' || categoria.ativo === true || categoria.ativo === undefined;
                const idCategoria = categoria.id_categoria || categoria.id;
                
                tr.innerHTML = `
                    <td>${nomeCategoria}</td>
                    <td>${categoria.descricao || '-'}</td>
                    <td>
                        <span class="badge ${isAtivo ? 'bg-success' : 'bg-secondary'}">
                            ${isAtivo ? 'Ativa' : 'Inativa'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1 p-1" onclick="editarCategoria(${idCategoria})" title="Editar" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-pencil" style="font-size: 0.875rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-danger p-1" onclick="removerCategoria(${idCategoria}, '${nomeEscapado}')" title="Remover" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-trash" style="font-size: 0.875rem;"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><div class="mt-2">Nenhuma categoria encontrada</div></td></tr>';
        }
    } catch (error) {
        console.error('❌ Erro ao carregar categorias:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <div class="mt-2">Erro ao carregar categorias</div>
                    <small class="text-muted">${error.message}</small>
                </td>
            </tr>
        `;
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar categorias: ' + error.message,
            confirmButtonText: 'OK'
        });
    }
}

// Função unificada para salvar (criar ou editar) categoria
async function salvarCategoria() {
    const idCategoria = document.getElementById('editandoCategoriaId').value;
    const nome = document.getElementById('novaCategoriaNome').value.trim();
    const descricao = document.getElementById('novaCategoriaDescricao').value.trim();
    const editando = idCategoria && idCategoria !== '';
    
    if (!nome) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'O nome da categoria é obrigatório',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    try {
        const url = 'backend/api/categorias.php';
        const method = editando ? 'PUT' : 'POST';
        const body = editando ? {
            id: parseInt(idCategoria),
            nome_categoria: nome,
            descricao: descricao,
            ativo: 1
        } : {
            nome_categoria: nome,
            descricao: descricao,
            ativo: 1
        };
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: editando ? 'Categoria atualizada com sucesso' : 'Categoria adicionada com sucesso',
                confirmButtonText: 'OK'
            });
            
            // Limpar formulário e voltar ao modo de criação
            cancelarEdicaoCategoria();
            
            // Recarregar lista e select
            await carregarListaCategorias();
            await carregarCategorias();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || (editando ? 'Erro ao atualizar categoria' : 'Erro ao adicionar categoria'),
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao salvar categoria:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: editando ? 'Erro ao atualizar categoria' : 'Erro ao adicionar categoria',
            confirmButtonText: 'OK'
        });
    }
}

// Função para editar categoria
async function editarCategoria(id) {
    try {
        // Buscar dados da categoria
        const response = await fetch(`backend/api/categorias.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success && data.categoria) {
            const categoria = data.categoria;
            
            // Preencher formulário com dados da categoria
            document.getElementById('editandoCategoriaId').value = categoria.id_categoria || categoria.id;
            document.getElementById('novaCategoriaNome').value = categoria.nome_categoria || categoria.nome || '';
            document.getElementById('novaCategoriaDescricao').value = categoria.descricao || '';
            
            // Alterar título e botão para modo de edição
            document.getElementById('categoria-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Categoria</h6>';
            document.getElementById('btnSalvarCategoriaTexto').textContent = 'Salvar Alterações';
            document.getElementById('btnCancelarEdicao').style.display = 'inline-block';
            
            // Focar no campo nome
            document.getElementById('novaCategoriaNome').focus();
            
            // Scroll para o formulário
            document.getElementById('categoria-form-header').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao carregar dados da categoria',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao carregar categoria para edição:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar categoria para edição',
            confirmButtonText: 'OK'
        });
    }
}

// Função para cancelar edição e voltar ao modo de criação
function cancelarEdicaoCategoria() {
    document.getElementById('editandoCategoriaId').value = '';
    document.getElementById('novaCategoriaNome').value = '';
    document.getElementById('novaCategoriaDescricao').value = '';
    document.getElementById('categoria-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Nova Categoria</h6>';
    document.getElementById('btnSalvarCategoriaTexto').textContent = 'Adicionar Categoria';
    document.getElementById('btnCancelarEdicao').style.display = 'none';
}

async function removerCategoria(id, nome) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar Remoção',
        html: `Tem certeza que deseja remover a categoria <strong>${nome}</strong>?<br><small class="text-muted">Esta ação não pode ser desfeita.</small>`,
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch('backend/api/categorias.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Categoria removida com sucesso',
                    confirmButtonText: 'OK'
                });
                
                // Recarregar lista e select
                await carregarListaCategorias();
                await carregarCategorias();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: data.error || 'Erro ao remover categoria',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Erro ao remover categoria:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao remover categoria',
                confirmButtonText: 'OK'
            });
        }
    }
}

// Permitir salvar categoria com Enter
document.addEventListener('DOMContentLoaded', function() {
    const nomeInput = document.getElementById('novaCategoriaNome');
    if (nomeInput) {
        nomeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                salvarCategoria();
            }
        });
    }
    
    const descricaoInput = document.getElementById('novaCategoriaDescricao');
    if (descricaoInput) {
        descricaoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                salvarCategoria();
            }
        });
    }
});

// ============================================
// Modal de Gerenciamento de Fornecedores
// ============================================
let modalFornecedores;

function abrirModalFornecedores() {
    if (!modalFornecedores) {
        modalFornecedores = new bootstrap.Modal(document.getElementById('modalFornecedores'));
    }
    cancelarEdicaoFornecedor();
    carregarListaFornecedores();
    modalFornecedores.show();
}

async function carregarListaFornecedores() {
    const tbody = document.querySelector('#tabelaFornecedores tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-hourglass-split fs-1"></i><div class="mt-2">Carregando fornecedores...</div></td></tr>';
    
    try {
        const response = await fetch('api/fornecedores.php?action=apenas-fornecedores', {
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        tbody.innerHTML = '';
        
        const fornecedores = (data.success && data.data) ? data.data : [];
        
        if (fornecedores.length > 0) {
            fornecedores.forEach(fornecedor => {
                const tr = document.createElement('tr');
                const razaoSocial = fornecedor.razao_social || '-';
                const razaoEscapada = razaoSocial.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const isAtivo = fornecedor.ativo == 1 || fornecedor.ativo === '1' || fornecedor.ativo === true;
                const idFornecedor = fornecedor.id_fornecedor || fornecedor.id;
                
                tr.innerHTML = `
                    <td>${razaoSocial}</td>
                    <td>${fornecedor.nome_fantasia || '-'}</td>
                    <td>${fornecedor.cnpj || '-'}</td>
                    <td>${fornecedor.email || '-'}</td>
                    <td>
                        <span class="badge ${isAtivo ? 'bg-success' : 'bg-secondary'}">
                            ${isAtivo ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1 p-1" onclick="editarFornecedor(${idFornecedor})" title="Editar" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-pencil" style="font-size: 0.875rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-danger p-1" onclick="removerFornecedor(${idFornecedor}, '${razaoEscapada}')" title="Remover" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-trash" style="font-size: 0.875rem;"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><div class="mt-2">Nenhum fornecedor encontrado</div></td></tr>';
        }
    } catch (error) {
        console.error('❌ Erro ao carregar fornecedores:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <div class="mt-2">Erro ao carregar fornecedores</div>
                    <small class="text-muted">${error.message}</small>
                </td>
            </tr>
        `;
    }
}

async function salvarFornecedor() {
    const idFornecedor = document.getElementById('editandoFornecedorId').value;
    const razaoSocial = document.getElementById('novoFornecedorRazaoSocial').value.trim();
    const nomeFantasia = document.getElementById('novoFornecedorNomeFantasia').value.trim();
    const cnpj = document.getElementById('novoFornecedorCnpj').value.trim();
    const email = document.getElementById('novoFornecedorEmail').value.trim();
    const telefone = document.getElementById('novoFornecedorTelefone').value.trim();
    const editando = idFornecedor && idFornecedor !== '';
    
    if (!razaoSocial) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'A razão social é obrigatória',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    try {
        const url = editando 
            ? `api/fornecedores.php?action=update&id=${idFornecedor}`
            : 'api/fornecedores.php?action=create';
        const method = editando ? 'PUT' : 'POST';
        const body = {
            razao_social: razaoSocial,
            nome_fantasia: nomeFantasia,
            cnpj: cnpj,
            email: email,
            telefone: telefone,
            is_fabricante: 0,
            ativo: 1
        };
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        
        if (data.success || data.id) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: editando ? 'Fornecedor atualizado com sucesso' : 'Fornecedor adicionado com sucesso',
                confirmButtonText: 'OK'
            });
            
            cancelarEdicaoFornecedor();
            await carregarListaFornecedores();
            await carregarFornecedores();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || (editando ? 'Erro ao atualizar fornecedor' : 'Erro ao adicionar fornecedor'),
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao salvar fornecedor:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: editando ? 'Erro ao atualizar fornecedor' : 'Erro ao adicionar fornecedor',
            confirmButtonText: 'OK'
        });
    }
}

async function editarFornecedor(id) {
    try {
        const response = await fetch(`api/fornecedores.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const fornecedor = data.data;
            
            document.getElementById('editandoFornecedorId').value = fornecedor.id_fornecedor || fornecedor.id;
            document.getElementById('novoFornecedorRazaoSocial').value = fornecedor.razao_social || '';
            document.getElementById('novoFornecedorNomeFantasia').value = fornecedor.nome_fantasia || '';
            document.getElementById('novoFornecedorCnpj').value = fornecedor.cnpj || '';
            document.getElementById('novoFornecedorEmail').value = fornecedor.email || '';
            document.getElementById('novoFornecedorTelefone').value = fornecedor.telefone || '';
            
            document.getElementById('fornecedor-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Fornecedor</h6>';
            document.getElementById('btnSalvarFornecedorTexto').textContent = 'Salvar Alterações';
            document.getElementById('btnCancelarEdicaoFornecedor').style.display = 'inline-block';
            
            document.getElementById('novoFornecedorRazaoSocial').focus();
            document.getElementById('fornecedor-form-header').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao carregar dados do fornecedor',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedor para edição:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar fornecedor para edição',
            confirmButtonText: 'OK'
        });
    }
}

function cancelarEdicaoFornecedor() {
    document.getElementById('editandoFornecedorId').value = '';
    document.getElementById('novoFornecedorRazaoSocial').value = '';
    document.getElementById('novoFornecedorNomeFantasia').value = '';
    document.getElementById('novoFornecedorCnpj').value = '';
    document.getElementById('novoFornecedorEmail').value = '';
    document.getElementById('novoFornecedorTelefone').value = '';
    document.getElementById('fornecedor-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Fornecedor</h6>';
    document.getElementById('btnSalvarFornecedorTexto').textContent = 'Adicionar Fornecedor';
    document.getElementById('btnCancelarEdicaoFornecedor').style.display = 'none';
}

async function removerFornecedor(id, nome) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar Remoção',
        html: `Tem certeza que deseja remover o fornecedor <strong>${nome}</strong>?<br><small class="text-muted">Esta ação não pode ser desfeita.</small>`,
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`api/fornecedores.php?action=delete&id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Fornecedor removido com sucesso',
                    confirmButtonText: 'OK'
                });
                
                await carregarListaFornecedores();
                await carregarFornecedores();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: data.error || 'Erro ao remover fornecedor',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Erro ao remover fornecedor:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao remover fornecedor',
                confirmButtonText: 'OK'
            });
        }
    }
}

// ============================================
// Modal de Gerenciamento de Fabricantes
// ============================================
let modalFabricantes;

function abrirModalFabricantes() {
    if (!modalFabricantes) {
        modalFabricantes = new bootstrap.Modal(document.getElementById('modalFabricantes'));
    }
    cancelarEdicaoFabricante();
    carregarListaFabricantes();
    modalFabricantes.show();
}

async function carregarListaFabricantes() {
    const tbody = document.querySelector('#tabelaFabricantes tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-hourglass-split fs-1"></i><div class="mt-2">Carregando fabricantes...</div></td></tr>';
    
    try {
        const response = await fetch('api/fornecedores.php?action=list&is_fabricante=1&ativo=1', {
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        tbody.innerHTML = '';
        
        const fabricantes = data.data || [];
        
        if (fabricantes.length > 0) {
            fabricantes.forEach(fabricante => {
                const tr = document.createElement('tr');
                const razaoSocial = fabricante.razao_social || '-';
                const razaoEscapada = razaoSocial.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const isAtivo = fabricante.ativo == 1 || fabricante.ativo === '1' || fabricante.ativo === true;
                const idFabricante = fabricante.id_fornecedor || fabricante.id;
                
                tr.innerHTML = `
                    <td>${razaoSocial}</td>
                    <td>${fabricante.nome_fantasia || '-'}</td>
                    <td>${fabricante.cnpj || '-'}</td>
                    <td>${fabricante.email || '-'}</td>
                    <td>
                        <span class="badge ${isAtivo ? 'bg-success' : 'bg-secondary'}">
                            ${isAtivo ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1 p-1" onclick="editarFabricante(${idFabricante})" title="Editar" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-pencil" style="font-size: 0.875rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-danger p-1" onclick="removerFabricante(${idFabricante}, '${razaoEscapada}')" title="Remover" style="font-size: 0.75rem; line-height: 1;">
                            <i class="bi bi-trash" style="font-size: 0.875rem;"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><div class="mt-2">Nenhum fabricante encontrado</div></td></tr>';
        }
    } catch (error) {
        console.error('❌ Erro ao carregar fabricantes:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <div class="mt-2">Erro ao carregar fabricantes</div>
                    <small class="text-muted">${error.message}</small>
                </td>
            </tr>
        `;
    }
}

async function salvarFabricante() {
    const idFabricante = document.getElementById('editandoFabricanteId').value;
    const razaoSocial = document.getElementById('novoFabricanteRazaoSocial').value.trim();
    const nomeFantasia = document.getElementById('novoFabricanteNomeFantasia').value.trim();
    const cnpj = document.getElementById('novoFabricanteCnpj').value.trim();
    const email = document.getElementById('novoFabricanteEmail').value.trim();
    const telefone = document.getElementById('novoFabricanteTelefone').value.trim();
    const editando = idFabricante && idFabricante !== '';
    
    if (!razaoSocial) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'A razão social é obrigatória',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    try {
        const url = editando 
            ? `api/fornecedores.php?action=update&id=${idFabricante}`
            : 'api/fornecedores.php?action=create';
        const method = editando ? 'PUT' : 'POST';
        const body = {
            razao_social: razaoSocial,
            nome_fantasia: nomeFantasia,
            cnpj: cnpj,
            email: email,
            telefone: telefone,
            is_fabricante: 1,
            ativo: 1
        };
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        
        if (data.success || data.id) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: editando ? 'Fabricante atualizado com sucesso' : 'Fabricante adicionado com sucesso',
                confirmButtonText: 'OK'
            });
            
            cancelarEdicaoFabricante();
            await carregarListaFabricantes();
            await carregarFabricantes();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || (editando ? 'Erro ao atualizar fabricante' : 'Erro ao adicionar fabricante'),
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao salvar fabricante:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: editando ? 'Erro ao atualizar fabricante' : 'Erro ao adicionar fabricante',
            confirmButtonText: 'OK'
        });
    }
}

async function editarFabricante(id) {
    try {
        const response = await fetch(`api/fornecedores.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const fabricante = data.data;
            
            document.getElementById('editandoFabricanteId').value = fabricante.id_fornecedor || fabricante.id;
            document.getElementById('novoFabricanteRazaoSocial').value = fabricante.razao_social || '';
            document.getElementById('novoFabricanteNomeFantasia').value = fabricante.nome_fantasia || '';
            document.getElementById('novoFabricanteCnpj').value = fabricante.cnpj || '';
            document.getElementById('novoFabricanteEmail').value = fabricante.email || '';
            document.getElementById('novoFabricanteTelefone').value = fabricante.telefone || '';
            
            document.getElementById('fabricante-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Fabricante</h6>';
            document.getElementById('btnSalvarFabricanteTexto').textContent = 'Salvar Alterações';
            document.getElementById('btnCancelarEdicaoFabricante').style.display = 'inline-block';
            
            document.getElementById('novoFabricanteRazaoSocial').focus();
            document.getElementById('fabricante-form-header').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao carregar dados do fabricante',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao carregar fabricante para edição:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar fabricante para edição',
            confirmButtonText: 'OK'
        });
    }
}

function cancelarEdicaoFabricante() {
    document.getElementById('editandoFabricanteId').value = '';
    document.getElementById('novoFabricanteRazaoSocial').value = '';
    document.getElementById('novoFabricanteNomeFantasia').value = '';
    document.getElementById('novoFabricanteCnpj').value = '';
    document.getElementById('novoFabricanteEmail').value = '';
    document.getElementById('novoFabricanteTelefone').value = '';
    document.getElementById('fabricante-form-header').innerHTML = '<h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Fabricante</h6>';
    document.getElementById('btnSalvarFabricanteTexto').textContent = 'Adicionar Fabricante';
    document.getElementById('btnCancelarEdicaoFabricante').style.display = 'none';
}

async function removerFabricante(id, nome) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar Remoção',
        html: `Tem certeza que deseja remover o fabricante <strong>${nome}</strong>?<br><small class="text-muted">Esta ação não pode ser desfeita.</small>`,
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`api/fornecedores.php?action=delete&id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Fabricante removido com sucesso',
                    confirmButtonText: 'OK'
                });
                
                await carregarListaFabricantes();
                await carregarFabricantes();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: data.error || 'Erro ao remover fabricante',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Erro ao remover fabricante:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao remover fabricante',
                confirmButtonText: 'OK'
            });
        }
    }
}

</script>

<!-- Modal de Gerenciamento de Categorias -->
<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCategoriasLabel">
                    <i class="bi bi-tags me-2"></i>Gerenciar Categorias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário para Adicionar/Editar Categoria -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white" id="categoria-form-header">
                        <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Nova Categoria</h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="editandoCategoriaId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome da Categoria *</label>
                                <input type="text" class="form-control" id="novaCategoriaNome" placeholder="Ex: EPI, Limpeza, Escritório...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="novaCategoriaDescricao" placeholder="Descrição opcional da categoria">
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" id="btnSalvarCategoria" onclick="salvarCategoria()">
                                    <i class="bi bi-check-lg me-1"></i><span id="btnSalvarCategoriaTexto">Adicionar Categoria</span>
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnCancelarEdicao" onclick="cancelarEdicaoCategoria()" style="display: none;">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Categorias Existentes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Categorias Existentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0" id="tabelaCategorias">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th width="100">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-hourglass-split fs-1"></i>
                                            <div class="mt-2">Carregando categorias...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Gerenciamento de Fornecedores -->
<div class="modal fade" id="modalFornecedores" tabindex="-1" aria-labelledby="modalFornecedoresLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFornecedoresLabel">
                    <i class="bi bi-truck me-2"></i>Gerenciar Fornecedores
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário para Adicionar/Editar Fornecedor -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white" id="fornecedor-form-header">
                        <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Fornecedor</h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="editandoFornecedorId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Razão Social *</label>
                                <input type="text" class="form-control" id="novoFornecedorRazaoSocial" placeholder="Ex: Empresa XYZ Ltda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome Fantasia</label>
                                <input type="text" class="form-control" id="novoFornecedorNomeFantasia" placeholder="Ex: XYZ Distribuidora">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNPJ</label>
                                <input type="text" class="form-control" id="novoFornecedorCnpj" placeholder="00.000.000/0000-00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="novoFornecedorEmail" placeholder="contato@empresa.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="novoFornecedorTelefone" placeholder="(00) 0000-0000">
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" id="btnSalvarFornecedor" onclick="salvarFornecedor()">
                                    <i class="bi bi-check-lg me-1"></i><span id="btnSalvarFornecedorTexto">Adicionar Fornecedor</span>
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnCancelarEdicaoFornecedor" onclick="cancelarEdicaoFornecedor()" style="display: none;">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Fornecedores Existentes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Fornecedores Existentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0" id="tabelaFornecedores">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Razão Social</th>
                                        <th>Nome Fantasia</th>
                                        <th>CNPJ</th>
                                        <th>E-mail</th>
                                        <th>Status</th>
                                        <th width="100">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-hourglass-split fs-1"></i>
                                            <div class="mt-2">Carregando fornecedores...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Gerenciamento de Fabricantes -->
<div class="modal fade" id="modalFabricantes" tabindex="-1" aria-labelledby="modalFabricantesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFabricantesLabel">
                    <i class="bi bi-gear me-2"></i>Gerenciar Fabricantes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário para Adicionar/Editar Fabricante -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white" id="fabricante-form-header">
                        <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Fabricante</h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="editandoFabricanteId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Razão Social *</label>
                                <input type="text" class="form-control" id="novoFabricanteRazaoSocial" placeholder="Ex: 3M do Brasil Ltda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome Fantasia</label>
                                <input type="text" class="form-control" id="novoFabricanteNomeFantasia" placeholder="Ex: 3M Brasil">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNPJ</label>
                                <input type="text" class="form-control" id="novoFabricanteCnpj" placeholder="00.000.000/0000-00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="novoFabricanteEmail" placeholder="contato@fabricante.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="novoFabricanteTelefone" placeholder="(00) 0000-0000">
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" id="btnSalvarFabricante" onclick="salvarFabricante()">
                                    <i class="bi bi-check-lg me-1"></i><span id="btnSalvarFabricanteTexto">Adicionar Fabricante</span>
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnCancelarEdicaoFabricante" onclick="cancelarEdicaoFabricante()" style="display: none;">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Fabricantes Existentes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Fabricantes Existentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0" id="tabelaFabricantes">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Razão Social</th>
                                        <th>Nome Fantasia</th>
                                        <th>CNPJ</th>
                                        <th>E-mail</th>
                                        <th>Status</th>
                                        <th width="100">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-hourglass-split fs-1"></i>
                                            <div class="mt-2">Carregando fabricantes...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
