// Variáveis globais
let paginaAtual = 1;
let movimentacaoParaExcluir = null;
let confirmModal;
let materiais = [];
let materiaisMovimentacao = [];
let materialCounter = 0;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    // Carregar dados iniciais
    carregarEstatisticas();
    carregarMateriais();
    carregarFiliais();
    carregarFornecedores();
    carregarClientes();
    carregarMovimentacoes();
    
    // Event listeners
    document.getElementById('busca').addEventListener('input', debounce(carregarMovimentacoes, 500));
    document.getElementById('filtro-tipo').addEventListener('change', carregarMovimentacoes);
    document.getElementById('data-inicio').addEventListener('change', carregarMovimentacoes);
    document.getElementById('data-fim').addEventListener('change', carregarMovimentacoes);
    
    // Event listeners para o modal
    document.getElementById('tipo_movimentacao').addEventListener('change', toggleCamposMovimentacao);
    document.getElementById('movimentacao-brinde').addEventListener('change', toggleCamposBrinde);
});

// Funções de carregamento
async function carregarEstatisticas() {
    try {
        const response = await fetch('backend/api/movimentacoes.php?action=stats', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            
            document.getElementById('total-movimentacoes').textContent = stats.total_movimentacoes || 0;
            document.getElementById('entradas').textContent = stats.entradas || 0;
            document.getElementById('saidas').textContent = stats.saidas || 0;
            document.getElementById('materiais-movimentados').textContent = stats.materiais_movimentados || 0;
            
            document.getElementById('valor-entradas').textContent = formatarMoeda(stats.valor_entradas || 0);
            document.getElementById('valor-saidas').textContent = formatarMoeda(stats.valor_saidas || 0);
            
            // Estatísticas de brindes
            document.getElementById('materiais-brinde').textContent = stats.materiais_brinde || 0;
            document.getElementById('valor-brindes').textContent = formatarMoeda(stats.valor_brindes || 0);
            document.getElementById('fornecedores-brinde').textContent = stats.fornecedores_brinde || 0;
            
            // Adicionar informação sobre estoque total de brindes se disponível
            if (stats.estoque_total_brindes !== undefined) {
                console.log('🎁 Estoque total de brindes:', stats.estoque_total_brindes);
                // Pode ser usado para mostrar em tooltip ou card adicional
            }
            
            // Remover texto "Carregando..." e mostrar status atualizado
            const statusElement = document.getElementById('status-total-movimentacoes');
            if (statusElement) {
                statusElement.textContent = 'Movimentações dos últimos 7 dias';
                statusElement.className = 'text-muted small';
            }
            
        } else {
            console.error('API retornou erro:', data.error);
            mostrarErro('Erro ao carregar estatísticas');
            
            // Mostrar erro no status
            const statusElement = document.getElementById('status-total-movimentacoes');
            if (statusElement) {
                statusElement.textContent = 'Erro ao carregar';
                statusElement.className = 'text-danger small';
            }
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
        mostrarErro('Erro ao carregar estatísticas');
    }
}

async function carregarMateriais(filialId = null, tipoMovimentacao = null) {
    try {
        // Se não foi passada filial específica, usar a filial selecionada do localStorage
        if (!filialId) {
            filialId = localStorage.getItem('filialSelecionada');
            console.log('🔍 Usando filial do localStorage:', filialId);
        }
        
        let url = 'backend/api/movimentacoes.php?action=materiais';
        
        // Determinar qual filial usar baseado no tipo de movimentação
        let filialParaConsulta = filialId;
        
        if (tipoMovimentacao === 'transferencia') {
            // Para transferência, usar filial de origem
            const filialOrigem = document.getElementById('id_filial_origem')?.value;
            if (filialOrigem) {
                filialParaConsulta = filialOrigem;
                console.log('🔄 Transferência detectada, usando filial de origem:', filialOrigem);
            } else {
                console.log('⚠️ Transferência detectada, mas filial de origem não selecionada');
            }
        } else if (tipoMovimentacao === 'entrada') {
            // Para entrada, usar filial de destino
            filialParaConsulta = filialId;
            console.log('🔄 Entrada detectada, usando filial de destino:', filialId);
        } else if (tipoMovimentacao === 'saida') {
            // Para saída, usar filial de origem
            const filialOrigem = document.getElementById('id_filial_origem')?.value;
            if (filialOrigem) {
                filialParaConsulta = filialOrigem;
                console.log('🔄 Saída detectada, usando filial de origem:', filialOrigem);
            } else {
                console.log('⚠️ Saída detectada, mas filial de origem não selecionada');
            }
        }
        
        // Adicionar filtro de filial se especificada
        if (filialParaConsulta) {
            url += `&filial_id=${filialParaConsulta}`;
            console.log('🔍 Carregando materiais da filial:', filialParaConsulta);
        } else {
            console.log('🔍 Carregando materiais de todas as filiais');
        }
        
        // Adicionar filtro de brindes se especificado
        const filtroBrinde = document.getElementById('filtro-brinde')?.value;
        if (filtroBrinde === 'apenas_brindes') {
            url += `&filtro_brinde=apenas_brindes`;
            console.log('🎁 Filtrando apenas materiais de brinde');
        } else if (filtroBrinde === 'excluir_brindes') {
            url += `&filtro_brinde=excluir_brindes`;
            console.log('📦 Excluindo materiais de brinde');
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            materiais = data.data;
            console.log(`✅ ${materiais.length} materiais carregados da filial ${filialParaConsulta || 'TODAS'}`);
            
            // Log do primeiro material para debug
            if (materiais.length > 0) {
                const primeiroMaterial = materiais[0];
                console.log('📦 Exemplo de material carregado:', {
                    codigo: primeiroMaterial.codigo,
                    nome: primeiroMaterial.nome,
                    estoque_atual: primeiroMaterial.estoque_atual,
                    estoque_minimo: primeiroMaterial.estoque_minimo,
                    estoque_maximo: primeiroMaterial.estoque_maximo,
                    preco_unitario: primeiroMaterial.preco_unitario,
                    id_filial: primeiroMaterial.id_filial
                });
            }
        } else {
            console.error('❌ Erro ao carregar materiais:', data.error);
        }
    } catch (error) {
        console.error('❌ Erro ao carregar materiais:', error);
    }
}

async function carregarFiliais() {
    try {
        const response = await fetch('backend/api/movimentacoes.php?action=filiais');
        const data = await response.json();
        
        if (data.success) {
            const selectOrigem = document.getElementById('id_filial_origem');
            const selectDestino = document.getElementById('id_filial_destino');
            
            data.data.forEach(filial => {
                const option = document.createElement('option');
                option.value = filial.id_filial;
                option.textContent = filial.nome_filial;
                
                selectOrigem.appendChild(option.cloneNode(true));
                selectDestino.appendChild(option);
            });
            
            // Adicionar event listener para recarregar materiais quando filial destino for alterada
            selectDestino.addEventListener('change', function() {
                const filialId = this.value;
                const tipoMovimentacao = document.getElementById('tipo_movimentacao').value;
                
                if (filialId || tipoMovimentacao) {
                    console.log('🔄 Filial destino alterada, recarregando materiais baseado no tipo:', tipoMovimentacao);
                    carregarMateriais(filialId, tipoMovimentacao);
                } else {
                    console.log('🔄 Filial destino limpa, carregando materiais da filial selecionada');
                    carregarMateriais();
                }
            });
            
            // Adicionar event listener para recarregar materiais quando filial origem for alterada
            selectOrigem.addEventListener('change', function() {
                const filialId = this.value;
                const tipoMovimentacao = document.getElementById('tipo_movimentacao').value;
                
                if (filialId || tipoMovimentacao) {
                    console.log('🔄 Filial origem alterada, recarregando materiais baseado no tipo:', tipoMovimentacao);
                    carregarMateriais(filialId, tipoMovimentacao);
                } else {
                    console.log('🔄 Filial origem limpa, carregando materiais da filial selecionada');
                    carregarMateriais();
                }
            });
        }
    } catch (error) {
        console.error('Erro ao carregar filiais:', error);
    }
}



async function carregarClientes() {
    try {
        const response = await fetch('backend/api/movimentacoes.php?action=clientes');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('id_cliente');
            data.data.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = cliente.nome_cliente;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar clientes:', error);
    }
}

async function carregarFornecedores() {
    try {
        const response = await fetch('backend/api/movimentacoes.php?action=fornecedores');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('fornecedor-brinde');
            data.data.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.nome_fornecedor;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores:', error);
    }
}

// Função para toggle dos campos de brinde
function toggleCamposBrinde() {
    const isBrinde = document.getElementById('movimentacao-brinde').checked;
    const fornecedorGroup = document.getElementById('fornecedor-brinde-group');
    const valorEstimadoGroup = document.getElementById('valor-estimado-brinde-group');
    
    if (isBrinde) {
        fornecedorGroup.style.display = 'block';
        valorEstimadoGroup.style.display = 'block';
        
        // Adicionar classes para animação
        setTimeout(() => {
            fornecedorGroup.classList.add('show');
            valorEstimadoGroup.classList.add('show');
        }, 10);
        
        console.log('🎁 Movimentação marcada como brinde');
    } else {
        // Remover classes de animação
        fornecedorGroup.classList.remove('show');
        valorEstimadoGroup.classList.remove('show');
        
        // Ocultar após animação
        setTimeout(() => {
            fornecedorGroup.style.display = 'none';
            valorEstimadoGroup.style.display = 'none';
        }, 300);
        
        console.log('📦 Movimentação não é brinde');
    }
}

async function carregarMovimentacoes(page = 1) {
    try {
        paginaAtual = page;
        const busca = document.getElementById('busca').value;
        const tipo = document.getElementById('filtro-tipo').value;
        const dataInicio = document.getElementById('data-inicio').value;
        const dataFim = document.getElementById('data-fim').value;
        
        let url = `backend/api/movimentacoes.php?action=list&page=${page}&limit=10`;
        
        if (busca) url += `&busca=${encodeURIComponent(busca)}`;
        if (tipo) url += `&tipo=${encodeURIComponent(tipo)}`;
        if (dataInicio) url += `&data_inicio=${encodeURIComponent(dataInicio)}`;
        if (dataFim) url += `&data_fim=${encodeURIComponent(dataFim)}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderizarTabela(data.data.movimentacoes);
            renderizarPaginacao(data.data);
        } else {
            renderizarTabela([]);
            mostrarErro('Erro ao carregar movimentações: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao carregar movimentações:', error);
        renderizarTabela([]);
        mostrarErro('Erro ao carregar movimentações');
    }
}

// Funções de renderização
function renderizarTabela(movimentacoes) {
    const tbody = document.getElementById('movimentacoes-tbody');
    const loading = document.getElementById('loading');
    const tabelaContainer = document.getElementById('tabela-container');
    const paginacao = document.getElementById('paginacao');
    const semDados = document.getElementById('sem-dados');
    
    loading.style.display = 'none';
    
    if (movimentacoes.length === 0) {
        tabelaContainer.style.display = 'none';
        paginacao.style.display = 'none';
        semDados.style.display = 'block';
        return;
    }
    
    tabelaContainer.style.display = 'block';
    paginacao.style.display = 'flex';
    semDados.style.display = 'none';
    
    tbody.innerHTML = movimentacoes.map(movimentacao => `
        <tr>
            <td><strong>${movimentacao.numero_movimentacao}</strong></td>
            <td>${getTipoBadge(movimentacao.tipo_movimentacao)}</td>
            <td>
                <div class="fw-bold">${movimentacao.nome_material}</div>
                <div class="text-muted small">${movimentacao.codigo_material}</div>
            </td>
            <td>${movimentacao.quantidade} ${movimentacao.unidade_material}</td>
            <td>${movimentacao.estoque_anterior_destino || 0} ${movimentacao.unidade_material}</td>
            <td><strong>${movimentacao.estoque_atual_destino || 0} ${movimentacao.unidade_material}</strong></td>
            <td>${formatarMoeda(movimentacao.valor_unitario || 0)}</td>
            <td><strong>${formatarMoeda(movimentacao.valor_total || 0)}</strong></td>
            <td><i class="bi bi-person me-1"></i>${movimentacao.nome_usuario}</td>
            <td>
                <i class="bi bi-calendar me-1"></i>${formatarData(movimentacao.data_movimentacao)}<br>
                <span class="text-muted-strong">${formatarHora(movimentacao.data_movimentacao)}</span>
            </td>
            <td>${getOrigemDestino(movimentacao)}</td>
            <td>${movimentacao.documento || '-'}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="visualizarMovimentacao(${movimentacao.id_movimentacao})" title="Visualizar">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="confirmarExclusao(${movimentacao.id_movimentacao})" title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderizarPaginacao(data) {
    const paginacao = document.getElementById('paginacao');
    const links = document.getElementById('paginacao-links');
    
    if (data.paginas <= 1) {
        paginacao.style.display = 'none';
        return;
    }
    
    paginacao.style.display = 'flex';
    
    const inicio = ((data.pagina_atual - 1) * 10) + 1;
    const fim = Math.min(data.pagina_atual * 10, data.total);
    
    document.getElementById('inicio-pagina').textContent = inicio;
    document.getElementById('fim-pagina').textContent = fim;
    document.getElementById('total-registros').textContent = data.total;
    
    let html = '';
    
    // Botão anterior
    html += `
        <li class="page-item ${data.pagina_atual <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="carregarMovimentacoes(${data.pagina_atual - 1})">Anterior</a>
        </li>
    `;
    
    // Páginas
    for (let i = Math.max(1, data.pagina_atual - 2); i <= Math.min(data.paginas, data.pagina_atual + 2); i++) {
        html += `
            <li class="page-item ${i === data.pagina_atual ? 'active' : ''}">
                <a class="page-link" href="#" onclick="carregarMovimentacoes(${i})">${i}</a>
            </li>
        `;
    }
    
    // Botão próximo
    html += `
        <li class="page-item ${data.pagina_atual >= data.paginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="carregarMovimentacoes(${data.pagina_atual + 1})">Próximo</a>
        </li>
    `;
    
    links.innerHTML = html;
}

// Funções de modal
function abrirModalNovaMovimentacao() {
    console.log('🚀 Abrindo modal de nova movimentação...');
    
    // Limpar campos
    document.getElementById('formNovaMovimentacao').reset();
    document.getElementById('materiais-container').innerHTML = '';
    materiaisMovimentacao = [];
    materialCounter = 0;
    
    // Limpar campos específicos de brinde
    document.getElementById('movimentacao-brinde').checked = false;
    document.getElementById('fornecedor-brinde').value = '';
    document.getElementById('valor-estimado-brinde').value = '';
    document.getElementById('fornecedor-brinde-group').style.display = 'none';
    document.getElementById('valor-estimado-brinde-group').style.display = 'none';
    
    // Ocultar grupos de campos
    document.getElementById('filial-origem-group').style.display = 'none';
    document.getElementById('filial-destino-group').style.display = 'none';
    document.getElementById('cliente-group').style.display = 'none';
    
    // Atualizar total
    atualizarTotalMovimentacao();
    
    // Restaurar estado do botão de salvar
    const btn = document.getElementById('btnSalvarMovimentacao');
    const btnIcon = document.getElementById('btnSalvarMovimentacaoIcon');
    const btnText = document.getElementById('btnSalvarMovimentacaoText');
    const btnCancelar = document.getElementById('btnCancelarMovimentacao');
    
    if (btn) {
        btn.disabled = false;
        if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
        if (btnText) btnText.textContent = 'Salvar Movimentação';
    }
    if (btnCancelar) {
        btnCancelar.disabled = false;
    }
    
    // Recarregar materiais baseado na filial selecionada (se houver)
    const filialDestino = document.getElementById('id_filial_destino').value;
    const tipoMovimentacao = document.getElementById('tipo_movimentacao').value;
    const filialSelecionada = localStorage.getItem('filialSelecionada');
    
    console.log('🔍 Filial destino atual:', filialDestino);
    console.log('🔍 Tipo de movimentação atual:', tipoMovimentacao);
    console.log('🔍 Filial selecionada no sistema:', filialSelecionada);
    
    if (filialDestino || tipoMovimentacao) {
        console.log('🔄 Recarregando materiais baseado no tipo e filial');
        carregarMateriais(filialDestino, tipoMovimentacao);
    } else if (filialSelecionada) {
        console.log('🔄 Usando filial selecionada no sistema para carregar materiais');
        carregarMateriais(filialSelecionada);
    } else {
        console.log('🔄 Nenhuma filial ou tipo selecionado, carregando materiais de todas as filiais');
        carregarMateriais();
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalNovaMovimentacao'));
    
    // Restaurar estado do botão quando o modal for fechado
    modal._element.addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btnSalvarMovimentacao');
        const btnIcon = document.getElementById('btnSalvarMovimentacaoIcon');
        const btnText = document.getElementById('btnSalvarMovimentacaoText');
        const btnCancelar = document.getElementById('btnCancelarMovimentacao');
        
        if (btn) {
            btn.disabled = false;
            if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
            if (btnText) btnText.textContent = 'Salvar Movimentação';
        }
        if (btnCancelar) {
            btnCancelar.disabled = false;
        }
    });
    
    modal.show();
}

// Adicionar material à movimentação
function adicionarMaterial() {
    materialCounter++;
    const materialId = `material-${materialCounter}`;
    
    console.log(`➕ Adicionando material: ${materialId}`);
    console.log(`📊 Contador atual: ${materialCounter}`);
    
    const materialHtml = `
        <div class="material-item" data-material-id="${materialId}">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Material *</label>
                    <div class="autocomplete-container">
                        <input type="text" class="form-control material-autocomplete" 
                               id="${materialId}-search" 
                               placeholder="Digite para buscar material..."
                               data-material-id="${materialId}">
                        <div class="autocomplete-results" id="${materialId}-results" style="display: none;"></div>
                    </div>
                    <input type="hidden" id="${materialId}-id" class="material-id">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" class="form-control material-quantidade" 
                           id="${materialId}-quantidade" 
                           step="0.01" min="0" 
                           oninput="calcularTotalMaterial('${materialId}')">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor Unit.</label>
                    <input type="text" class="form-control material-valor-unitario" 
                           id="${materialId}-valor-unitario" 
                           placeholder="0,00"
                           oninput="calcularTotalMaterial('${materialId}')">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="removerMaterial('${materialId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            

            <!-- Campo de valor total (oculto, usado apenas para cálculos) -->
            <input type="hidden" id="${materialId}-valor-total" class="material-valor-total">
            
            <!-- Informações adicionais do estoque -->
            <div class="row mt-2">
                <div class="col-12">
                    <div id="${materialId}-estoque-info" class="estoque-info" style="display: none;">
                        <!-- Informações adicionais do estoque serão preenchidas aqui -->
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('materiais-container').insertAdjacentHTML('beforeend', materialHtml);
    console.log(`✅ HTML do material ${materialId} inserido no DOM`);
    
    // Configurar autocomplete para o novo material
    configurarAutocomplete(materialId);
    console.log(`🔧 Autocomplete configurado para ${materialId}`);
    
    // Adicionar à lista de materiais
    materiaisMovimentacao.push(materialId);
    console.log(`📋 Material ${materialId} adicionado ao array materiaisMovimentacao`);
    console.log(`📊 Array atual:`, materiaisMovimentacao);
    
    // Atualizar total da movimentação
    console.log('🔄 Chamando atualizarTotalMovimentacao() após adicionar material');
    atualizarTotalMovimentacao();
}

// Configurar autocomplete para um material
function configurarAutocomplete(materialId) {
    const input = document.getElementById(`${materialId}-search`);
    const results = document.getElementById(`${materialId}-results`);
    
    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        
        if (query.length < 2) {
            results.style.display = 'none';
            return;
        }
        
        const filtered = materiais.filter(material => 
            material.codigo.toLowerCase().includes(query) || 
            material.nome.toLowerCase().includes(query)
        );
        
        if (filtered.length > 0) {
            results.innerHTML = filtered.map(material => {
                // Garantir que os valores numéricos estão corretos
                const estoqueAtual = parseFloat(material.estoque_atual || 0);
                const estoqueMinimo = material.estoque_minimo ? parseFloat(material.estoque_minimo) : '';
                const estoqueMaximo = material.estoque_maximo ? parseFloat(material.estoque_maximo) : '';
                const preco = parseFloat(material.preco_unitario || 0);
                
                return `<div class="autocomplete-item" 
                             data-material-id="${material.id_catalogo}" 
                             data-material-codigo="${material.codigo}" 
                             data-material-nome="${material.nome}" 
                             data-material-preco="${preco}" 
                             data-material-fornecedor="${material.nome_fornecedor || ''}" 
                             data-material-estoque-atual="${estoqueAtual}" 
                             data-material-estoque-minimo="${estoqueMinimo}" 
                             data-material-estoque-maximo="${estoqueMaximo}" 
                             data-material-unidade="${material.unidade || ''}">
                    <strong>${material.codigo}</strong> - ${material.nome}
                    ${material.nome_fornecedor ? `<br><small class="text-muted">Fornecedor: ${material.nome_fornecedor}</small>` : ''}
                    <br><small class="text-info">Estoque: ${estoqueAtual.toFixed(2)} ${material.unidade || ''} | Preço: ${formatarMoeda(preco)}</small>
                </div>`;
            }).join('');
            results.style.display = 'block';
        } else {
            results.style.display = 'none';
        }
    });
    
    // Selecionar material do autocomplete
    results.addEventListener('click', function(e) {
        if (e.target.classList.contains('autocomplete-item')) {
            const materialIdBanco = e.target.dataset.materialId;
            const materialCodigo = e.target.dataset.materialCodigo;
            const materialNome = e.target.dataset.materialNome;
            const materialPreco = e.target.dataset.materialPreco;
            const materialFornecedor = e.target.dataset.materialFornecedor;
            const materialEstoqueAtual = e.target.dataset.materialEstoqueAtual;
            const materialEstoqueMinimo = e.target.dataset.materialEstoqueMinimo;
            const materialEstoqueMaximo = e.target.dataset.materialEstoqueMaximo;
            
            // Preencher campos
            const displayText = materialFornecedor ? 
                `${materialCodigo} - ${materialNome} (${materialFornecedor})` : 
                `${materialCodigo} - ${materialNome}`;
            input.value = displayText;
            document.getElementById(`${materialId}-id`).value = materialIdBanco;
            
            // Preencher valor unitário com o preço atual da base de dados
            const valorUnitarioInput = document.getElementById(`${materialId}-valor-unitario`);
            if (materialPreco && materialPreco > 0) {
                valorUnitarioInput.value = formatarMoeda(materialPreco);
                console.log(`💰 Valor unitário preenchido: R$ ${materialPreco}`);
            } else {
                valorUnitarioInput.value = 'R$ 0,00';
                console.log('⚠️ Valor unitário não disponível, definindo como R$ 0,00');
            }
            
            // Preencher quantidade com estoque atual se disponível
            const quantidadeInput = document.getElementById(`${materialId}-quantidade`);
            if (materialEstoqueAtual && materialEstoqueAtual > 0) {
                quantidadeInput.value = materialEstoqueAtual;
                console.log(`📦 Quantidade preenchida com estoque atual: ${materialEstoqueAtual}`);
            } else {
                quantidadeInput.value = '1'; // Valor padrão se não houver estoque
                console.log('📦 Estoque atual não disponível, definindo quantidade como 1');
            }
            
            // Mostrar informações do estoque atual
            const estoqueInfoElement = document.getElementById(`${materialId}-estoque-info`);
            if (estoqueInfoElement) {
                // Formatar valores numéricos
                const estoqueAtual = parseFloat(materialEstoqueAtual || 0).toFixed(2);
                const estoqueMinimo = materialEstoqueMinimo ? parseFloat(materialEstoqueMinimo).toFixed(2) : null;
                const estoqueMaximo = materialEstoqueMaximo ? parseFloat(materialEstoqueMaximo).toFixed(2) : null;
                const precoFormatado = materialPreco ? formatarMoeda(parseFloat(materialPreco)) : 'R$ 0,00';
                
                estoqueInfoElement.innerHTML = `
                    <small class="text-info">
                        <i class="bi bi-info-circle"></i> 
                        Estoque atual: <strong>${estoqueAtual}</strong>
                        ${estoqueMinimo ? `| Mín: ${estoqueMinimo}` : ''}
                        ${estoqueMaximo ? `| Máx: ${estoqueMaximo}` : ''}
                        | Preço: ${precoFormatado}
                    </small>
                `;
                estoqueInfoElement.style.display = 'block';
                
                console.log(`📊 Informações de estoque exibidas:`, {
                    estoque_atual: estoqueAtual,
                    estoque_minimo: estoqueMinimo,
                    estoque_maximo: estoqueMaximo,
                    preco: precoFormatado
                });
            }
            
            results.style.display = 'none';
            
            // Calcular total
            calcularTotalMaterial(materialId);
            
            console.log(`✅ Material selecionado: ${materialCodigo} - ${materialNome}`);
            console.log(`📊 Dados preenchidos:`, {
                estoque_atual: materialEstoqueAtual,
                estoque_minimo: materialEstoqueMinimo,
                estoque_maximo: materialEstoqueMaximo,
                preco: materialPreco,
                unidade: materialEstoqueMaximo ? document.querySelector(`[data-material-id="${materialIdBanco}"]`)?.dataset.materialUnidade : ''
            });
        }
    });
    
    // Aplicar máscara de moeda
    const valorUnitarioInput = document.getElementById(`${materialId}-valor-unitario`);
    valorUnitarioInput.addEventListener('input', function(e) {
        aplicarMascaraMoeda(e);
        calcularTotalMaterial(materialId);
    });
    
    // Adicionar evento para quantidade
    const quantidadeInput = document.getElementById(`${materialId}-quantidade`);
    quantidadeInput.addEventListener('input', function() {
        calcularTotalMaterial(materialId);
    });
    
    // Esconder resultados quando clicar fora
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.style.display = 'none';
        }
    });
}

// Remover material da movimentação
function removerMaterial(materialId) {
    console.log(`🗑️ Removendo material: ${materialId}`);
    console.log(`📊 Array antes da remoção:`, materiaisMovimentacao);
    
    document.querySelector(`[data-material-id="${materialId}"]`).remove();
    console.log(`✅ Elemento ${materialId} removido do DOM`);
    
    materiaisMovimentacao = materiaisMovimentacao.filter(id => id !== materialId);
    console.log(`📋 Material ${materialId} removido do array materiaisMovimentacao`);
    console.log(`📊 Array após remoção:`, materiaisMovimentacao);
    
    console.log('🔄 Chamando atualizarTotalMovimentacao() após remover material');
    atualizarTotalMovimentacao();
}

// Calcular total de um material específico
function calcularTotalMaterial(materialId) {
    console.log(`🧮 calcularTotalMaterial() chamada para: ${materialId}`);
    console.log(`⏰ Timestamp: ${new Date().toLocaleTimeString()}`);
    
    const quantidadeInput = document.getElementById(`${materialId}-quantidade`);
    const valorUnitarioInput = document.getElementById(`${materialId}-valor-unitario`);
    const valorTotalInput = document.getElementById(`${materialId}-valor-total`);
    
    console.log('📊 Campos encontrados:', {
        quantidade: quantidadeInput,
        valorUnitario: valorUnitarioInput,
        valorTotal: valorTotalInput
    });
    
    if (!quantidadeInput || !valorUnitarioInput || !valorTotalInput) {
        console.log('❌ Algum campo não foi encontrado');
        return; // Elementos não existem ainda
    }
    
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    console.log(`📦 Quantidade: "${quantidadeInput.value}" -> ${quantidade}`);
    
    // Extrair valor numérico da máscara de moeda
    let valorUnitario = 0;
    if (valorUnitarioInput.value) {
        // Remover "R$ " e converter vírgula para ponto para parseFloat funcionar corretamente
        const valorLimpo = valorUnitarioInput.value.replace(/R\$\s*/, '').replace(/\./g, '').replace(',', '.');
        valorUnitario = parseFloat(valorLimpo) || 0;
        console.log(`💵 Valor unitário: "${valorUnitarioInput.value}" -> "${valorLimpo}" -> ${valorUnitario}`);
    } else {
        console.log('⚠️ Campo valor unitário vazio');
    }
    
    const valorTotal = quantidade * valorUnitario;
    console.log(`💰 Cálculo: ${quantidade} × ${valorUnitario} = ${valorTotal}`);
    
    valorTotalInput.value = formatarMoeda(valorTotal);
    console.log(`💎 Valor total formatado: "${valorTotalInput.value}"`);
    
    console.log('🔄 Chamando atualizarTotalMovimentacao()');
    atualizarTotalMovimentacao();
}

// Atualizar total da movimentação
function atualizarTotalMovimentacao() {
    console.log('🔄 atualizarTotalMovimentacao() chamada');
    console.log('📋 Array materiaisMovimentacao:', materiaisMovimentacao);
    
    let total = 0;
    const totalElement = document.getElementById('total-movimentacao');
    
    if (!totalElement) {
        console.log('❌ Elemento total-movimentacao não encontrado');
        return; // Elemento não existe ainda
    }
    
    console.log('✅ Elemento total-movimentacao encontrado');
    
    materiaisMovimentacao.forEach(materialId => {
        console.log(`🔍 Processando material: ${materialId}`);
        
        const valorTotalInput = document.getElementById(`${materialId}-valor-total`);
        console.log(`📊 Campo valor-total para ${materialId}:`, valorTotalInput);
        
        if (valorTotalInput && valorTotalInput.value) {
            // Extrair valor numérico da máscara de moeda (R$ X.XXX,XX)
            const valorLimpo = valorTotalInput.value
                .replace(/R\$\s*/, '')  // Remove "R$ " e espaços
                .replace(/\./g, '')     // Remove pontos (separadores de milhares)
                .replace(',', '.');     // Converte vírgula para ponto decimal
            
            const valor = parseFloat(valorLimpo) || 0;
            console.log(`💰 Valor extraído para ${materialId}: "${valorTotalInput.value}" -> "${valorLimpo}" -> ${valor}`);
            total += valor;
        } else {
            console.log(`⚠️ Campo valor-total vazio ou não encontrado para ${materialId}`);
        }
    });
    
    console.log(`🎯 Total calculado: ${total}`);
    totalElement.textContent = formatarMoeda(total);
    console.log(`💵 Total formatado: ${formatarMoeda(total)}`);
}

function toggleCamposMovimentacao() {
    const tipo = document.getElementById('tipo_movimentacao').value;
    const filialOrigemGroup = document.getElementById('filial-origem-group');
    const filialDestinoGroup = document.getElementById('filial-destino-group');
    const clienteGroup = document.getElementById('cliente-group');
    
    console.log('🔄 Tipo de movimentação alterado para:', tipo);
    
    // Ocultar todos os grupos
    filialOrigemGroup.style.display = 'none';
    filialDestinoGroup.style.display = 'none';
    clienteGroup.style.display = 'none';
    
    // Mostrar grupos baseado no tipo
    switch (tipo) {
        case 'entrada':
            filialDestinoGroup.style.display = 'block';
            break;
        case 'saida':
            filialOrigemGroup.style.display = 'block';
            // clienteGroup removido - não necessário para saída
            break;
        case 'transferencia':
            filialOrigemGroup.style.display = 'block';
            filialDestinoGroup.style.display = 'block';
            break;
        case 'ajuste':
            filialOrigemGroup.style.display = 'block';
            break;
    }
    
    // Recarregar materiais baseado no novo tipo e filial selecionada
    const filialDestino = document.getElementById('id_filial_destino').value;
    const filialOrigem = document.getElementById('id_filial_origem').value;
    
    // Determinar qual filial usar para carregar materiais
    let filialParaMateriais = null;
    
    if (tipo === 'entrada') {
        // Para entrada, usar filial de destino ou filial selecionada
        filialParaMateriais = filialDestino || localStorage.getItem('filialSelecionada');
    } else if (tipo === 'saida') {
        // Para saída, usar filial de origem ou filial selecionada
        filialParaMateriais = filialOrigem || localStorage.getItem('filialSelecionada');
    } else if (tipo === 'transferencia') {
        // Para transferência, usar filial de origem
        filialParaMateriais = filialOrigem || localStorage.getItem('filialSelecionada');
    } else if (tipo === 'ajuste') {
        // Para ajuste, usar filial de origem ou filial selecionada
        filialParaMateriais = filialOrigem || localStorage.getItem('filialSelecionada');
    }
    
    console.log('🔄 Recarregando materiais após alteração do tipo para:', tipo);
    console.log('🏢 Filial para materiais:', filialParaMateriais);
    carregarMateriais(filialParaMateriais, tipo);
}

function aplicarMascaraMoeda(event) {
    const input = event.target;
    let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    if (value.length === 0) {
        input.value = '';
        return;
    }
    
    // Converte para número e formata
    const number = parseFloat(value) / 100;
    const valorFormatado = number.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Adicionar prefixo "R$ "
    input.value = `R$ ${valorFormatado}`;
}

async function salvarNovaMovimentacao() {
    // Obter referências ao botão
    const btn = document.getElementById('btnSalvarMovimentacao');
    const btnIcon = document.getElementById('btnSalvarMovimentacaoIcon');
    const btnText = document.getElementById('btnSalvarMovimentacaoText');
    const btnCancelar = document.getElementById('btnCancelarMovimentacao');
    
    // Verificar se os elementos existem
    if (!btn) {
        console.error('❌ Botão btnSalvarMovimentacao não encontrado');
        mostrarErro('Erro: Elemento não encontrado. Recarregue a página.');
        return;
    }
    
    // Verificar se o botão já está desabilitado (evitar múltiplos cliques)
    if (btn.disabled) {
        console.log('⚠️ Botão já está desabilitado, ignorando clique duplicado');
        return;
    }
    
    try {
        const form = document.getElementById('formNovaMovimentacao');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Validar se há materiais adicionados
        if (materiaisMovimentacao.length === 0) {
            mostrarErro('Adicione pelo menos um material à movimentação');
            return;
        }
        
        // Desabilitar botão e mostrar indicador de carregamento
        btn.disabled = true;
        if (btnCancelar) btnCancelar.disabled = true;
        if (btnIcon) btnIcon.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';
        if (btnText) btnText.textContent = 'Salvando...';
        
        console.log('💾 Iniciando salvamento da movimentação...');
        
        // Coletar dados dos materiais
        const materiais = [];
        let totalGeral = 0;
        
        // Coletar dados específicos de brinde
        const isBrinde = document.getElementById('movimentacao-brinde').checked;
        const fornecedorBrinde = document.getElementById('fornecedor-brinde').value;
        const valorEstimadoBrinde = document.getElementById('valor-estimado-brinde').value;
        
        // Validar campos de brinde se for movimentação de brinde
        if (isBrinde) {
            if (!fornecedorBrinde) {
                mostrarErro('Selecione o fornecedor do brinde');
                // Restaurar botão antes de retornar
                btn.disabled = false;
                if (btnCancelar) btnCancelar.disabled = false;
                if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
                if (btnText) btnText.textContent = 'Salvar Movimentação';
                return;
            }
            console.log('🎁 Movimentação de brinde detectada:', {
                fornecedor: fornecedorBrinde,
                valor_estimado: valorEstimadoBrinde
            });
        }
        
        for (let index = 0; index < materiaisMovimentacao.length; index++) {
            const materialId = materiaisMovimentacao[index];
            const idMaterial = document.getElementById(`${materialId}-id`).value;
            const quantidade = parseFloat(document.getElementById(`${materialId}-quantidade`).value);
            const valorUnitarioInput = document.getElementById(`${materialId}-valor-unitario`);
            const valorTotalInput = document.getElementById(`${materialId}-valor-total`);
            
            if (!idMaterial || !quantidade) {
                mostrarErro('Preencha todos os campos obrigatórios dos materiais');
                // Restaurar botão antes de retornar
                btn.disabled = false;
                if (btnCancelar) btnCancelar.disabled = false;
                if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
                if (btnText) btnText.textContent = 'Salvar Movimentação';
                return;
            }
            
            // Extrair valor unitário da máscara
            let valorUnitario = 0;
            if (valorUnitarioInput.value) {
                const valorLimpo = valorUnitarioInput.value
                    .replace(/R\$\s*/, '')  // Remove "R$ " e espaços
                    .replace(/\./g, '')     // Remove pontos (separadores de milhares)
                    .replace(',', '.');     // Converte vírgula para ponto decimal
                valorUnitario = parseFloat(valorLimpo) || 0;
                console.log(`💵 Valor unitário extraído: "${valorUnitarioInput.value}" -> "${valorLimpo}" -> ${valorUnitario}`);
            }
            
            // Extrair valor total da máscara
            let valorTotal = 0;
            if (valorTotalInput.value) {
                const valorLimpo = valorTotalInput.value
                    .replace(/R\$\s*/, '')  // Remove "R$ " e espaços
                    .replace(/\./g, '')     // Remove pontos (separadores de milhares)
                    .replace(',', '.');     // Converte vírgula para ponto decimal
                valorTotal = parseFloat(valorLimpo) || 0;
                console.log(`💰 Valor total extraído: "${valorTotalInput.value}" -> "${valorLimpo}" -> ${valorTotal}`);
            }
            
            totalGeral += valorTotal;
            
            console.log(`🔍 Material ${materialId}:`, {
                id_catalogo: idMaterial,
                quantidade: quantidade,
                valor_unitario: valorUnitario,
                valor_total: valorTotal,
                valor_total_input: valorTotalInput.value
            });
            
            materiais.push({
                id_catalogo: idMaterial,
                quantidade: quantidade,
                valor_unitario: valorUnitario,
                valor_total: valorTotal
            });
        }
        
        // Dados da movimentação
        const dadosMovimentacao = {
            tipo_movimentacao: document.getElementById('tipo_movimentacao').value,
            materiais: materiais,
            id_filial_origem: document.getElementById('id_filial_origem')?.value || null,
            id_filial_destino: document.getElementById('id_filial_destino')?.value || null,
            id_cliente: document.getElementById('id_cliente')?.value || null,
            documento: document.getElementById('documento').value || null,
            observacoes: document.getElementById('observacoes').value || null,
            id_usuario_executor: usuarioLogado.id_usuario,
            // Campos de brinde
            is_brinde: isBrinde ? 1 : 0,
            fornecedor_brinde: fornecedorBrinde || null,
            valor_estimado_brinde: valorEstimadoBrinde ? parseFloat(valorEstimadoBrinde.replace(/[^\d,.-]/g, '').replace(',', '.')) : null
        };
        
        console.log('💰 Total geral calculado:', totalGeral);
        console.log('📦 Materiais coletados:', materiais);
        
        console.log('📤 Dados a serem enviados:', dadosMovimentacao);
        
        const response = await fetch('backend/api/movimentacoes.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosMovimentacao)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('modalNovaMovimentacao')).hide();
            
            // Limpar formulário
            form.reset();
            
            // Recarregar dados
            carregarEstatisticas();
            carregarMovimentacoes(1);
            
            // Mostrar sucesso
            mostrarSucesso('Movimentação criada com sucesso!');
            
            // Restaurar botão (já que o modal será fechado)
            btn.disabled = false;
            if (btnCancelar) btnCancelar.disabled = false;
            if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
            if (btnText) btnText.textContent = 'Salvar Movimentação';
        } else {
            mostrarErro('Erro ao criar movimentação: ' + (data.error || 'Erro desconhecido'));
            
            // Restaurar botão em caso de erro
            btn.disabled = false;
            if (btnCancelar) btnCancelar.disabled = false;
            if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
            if (btnText) btnText.textContent = 'Salvar Movimentação';
        }
    } catch (error) {
        console.error('Erro ao salvar movimentação:', error);
        mostrarErro('Erro ao salvar movimentação');
        
        // Restaurar botão em caso de erro
        btn.disabled = false;
        if (btnCancelar) btnCancelar.disabled = false;
        if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
        if (btnText) btnText.textContent = 'Salvar Movimentação';
    }
}

async function visualizarMovimentacao(id) {
    try {
        const response = await fetch(`backend/api/movimentacoes.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const movimentacao = data.data;
            
            // Preencher informações principais
            document.getElementById('view-numero_movimentacao').textContent = movimentacao.numero_movimentacao;
            document.getElementById('view-data_movimentacao').textContent = formatarDataHora(movimentacao.data_movimentacao);
            
            // Preencher badge de tipo
            const tipoBadge = document.getElementById('view-tipo-badge');
            const tipoText = document.getElementById('view-tipo-text');
            tipoText.textContent = getTipoText(movimentacao.tipo_movimentacao);
            
            // Aplicar classe de cor baseada no tipo
            tipoBadge.className = `movimentacao-badge ${movimentacao.tipo_movimentacao}`;
            
            // Atualizar ícone baseado no tipo
            const icon = tipoBadge.querySelector('i');
            switch(movimentacao.tipo_movimentacao) {
                case 'entrada':
                    icon.className = 'bi bi-arrow-up-circle me-2';
                    break;
                case 'saida':
                    icon.className = 'bi bi-arrow-down-circle me-2';
                    break;
                case 'transferencia':
                    icon.className = 'bi bi-arrow-left-right me-2';
                    break;
                case 'ajuste':
                    icon.className = 'bi bi-gear me-2';
                    break;
                default:
                    icon.className = 'bi bi-arrow-up-circle me-2';
            }
            
            // Preencher informações do material
            document.getElementById('view-material').textContent = `${movimentacao.codigo_material} - ${movimentacao.nome_material}`;
            document.getElementById('view-usuario').textContent = movimentacao.nome_usuario;
            
            // Preencher quantidades e valores
            document.getElementById('view-quantidade').textContent = `${movimentacao.quantidade} ${movimentacao.unidade_material}`;
            document.getElementById('view-valor_unitario').textContent = formatarMoeda(movimentacao.valor_unitario || 0);
            document.getElementById('view-valor_total').textContent = formatarMoeda(movimentacao.valor_total || 0);
            
            // Preencher estoque (usando os campos corretos)
            document.getElementById('view-estoque_anterior').textContent = `${movimentacao.estoque_anterior_destino || 0} ${movimentacao.unidade_material}`;
            document.getElementById('view-estoque_atual').textContent = `${movimentacao.estoque_atual_destino || 0} ${movimentacao.unidade_material}`;
            
            // Preencher custo médio anterior e atual
            document.getElementById('view-custo_medio_anterior').textContent = formatarMoeda(movimentacao.custo_medio_anterior || 0);
            document.getElementById('view-custo_medio_atual').textContent = formatarMoeda(movimentacao.custo_medio_atual || 0);
            
            // Preencher informações adicionais
            document.getElementById('view-origem_destino').textContent = getOrigemDestino(movimentacao);
            document.getElementById('view-documento').textContent = movimentacao.documento || '-';
            document.getElementById('view-observacoes').textContent = movimentacao.observacoes || '-';
            
            const modal = new bootstrap.Modal(document.getElementById('modalVisualizarMovimentacao'));
            modal.show();
        } else {
            mostrarErro('Erro ao carregar movimentação: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao visualizar movimentação:', error);
        mostrarErro('Erro ao visualizar movimentação');
    }
}

function confirmarExclusao(id) {
    movimentacaoParaExcluir = id;
    document.getElementById('confirmMessage').textContent = 'Tem certeza que deseja excluir esta movimentação? Esta ação não pode ser desfeita.';
    confirmModal.show();
}

// Event listener para confirmação de exclusão
document.getElementById('confirmAction').addEventListener('click', async function() {
    if (!movimentacaoParaExcluir) return;
    
    try {
        const response = await fetch(`backend/api/movimentacoes.php?action=delete&id=${movimentacaoParaExcluir}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fechar modal
            confirmModal.hide();
            
            // Recarregar dados
            carregarEstatisticas();
            carregarMovimentacoes(paginaAtual);
            
            // Mostrar sucesso
            mostrarSucesso('Movimentação excluída com sucesso!');
        } else {
            mostrarErro('Erro ao excluir movimentação: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao excluir movimentação:', error);
        mostrarErro('Erro ao excluir movimentação');
    }
});

// Funções utilitárias
function getTipoBadge(tipo) {
    const badges = {
        'entrada': '<span class="badge bg-success">Entrada</span>',
        'saida': '<span class="badge bg-danger">Saída</span>',
        'transferencia': '<span class="badge bg-primary">Transferência</span>',
        'ajuste': '<span class="badge bg-warning">Ajuste</span>'
    };
    return badges[tipo] || tipo;
}

function getTipoText(tipo) {
    const textos = {
        'entrada': 'Entrada',
        'saida': 'Saída',
        'transferencia': 'Transferência',
        'ajuste': 'Ajuste'
    };
    return textos[tipo] || tipo;
}

function getOrigemDestino(movimentacao) {
    switch (movimentacao.tipo_movimentacao) {
        case 'entrada':
            return movimentacao.nome_fornecedor || '-';
        case 'saida':
            return movimentacao.nome_cliente || '-';
        case 'transferencia':
            return `${movimentacao.filial_origem || '-'} → ${movimentacao.filial_destino || '-'}`;
        case 'ajuste':
            return 'Inventário';
        default:
            return '-';
    }
}

function formatarMoeda(valor) {
    if (!valor || valor === 0) {
        return 'R$ 0,00';
    }
    
    // Garantir que é um número, aceitando tanto ponto quanto vírgula como separador decimal
    let valorNumerico;
    if (typeof valor === 'string') {
        // Se for string, converter ponto para vírgula para parseFloat funcionar corretamente
        valorNumerico = parseFloat(valor.replace(',', '.'));
    } else {
        valorNumerico = parseFloat(valor);
    }
    
    if (isNaN(valorNumerico)) {
        return 'R$ 0,00';
    }
    
    // Formatar para moeda brasileira
    const valorFormatado = valorNumerico.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
    
    return valorFormatado;
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data).toLocaleDateString('pt-BR');
}

function formatarHora(data) {
    if (!data) return '-';
    return new Date(data).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatarDataHora(data) {
    if (!data) return '-';
    return new Date(data).toLocaleString('pt-BR');
}

function toggleFiltros() {
    const filtrosAvancados = document.getElementById('filtrosAvancados');
    filtrosAvancados.style.display = filtrosAvancados.style.display === 'none' ? 'block' : 'none';
}

function limparFiltros() {
    document.getElementById('busca').value = '';
    document.getElementById('filtro-tipo').value = '';
    document.getElementById('filtro-status').value = '';
    document.getElementById('filtro-brinde').value = '';
    document.getElementById('data-inicio').value = '';
    document.getElementById('data-fim').value = '';
    carregarMovimentacoes(1);
}

function exportarXLS() {
    mostrarSucesso('Funcionalidade de exportação será implementada em breve!');
}

function imprimir() {
    mostrarSucesso('Funcionalidade de impressão será implementada em breve!');
}

function mostrarSucesso(mensagem) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: mensagem,
        timer: 3000,
        showConfirmButton: false
    });
}

function mostrarErro(mensagem) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: mensagem,
        timer: 5000,
        showConfirmButton: false
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 

// Função de teste para extração de valores
function testarExtracaoValor() {
    console.log('🧪 TESTE DE EXTRAÇÃO DE VALORES');
    
    const valoresTeste = [
        'R$ 318,00',
        'R$ 1.250,50',
        'R$ 0,99',
        'R$ 2.500,00'
    ];
    
    valoresTeste.forEach(valor => {
        const valorLimpo = valor
            .replace(/R\$\s*/, '')  // Remove "R$ " e espaços
            .replace(/\./g, '')     // Remove pontos (separadores de milhares)
            .replace(',', '.');     // Converte vírgula para ponto decimal
        
        const valorNumerico = parseFloat(valorLimpo) || 0;
        
        console.log(`💰 Teste: "${valor}" -> "${valorLimpo}" -> ${valorNumerico}`);
    });
} 

// Função para salvar configurações de estoque
async function salvarConfiguracoesEstoque(materialId) {
    console.log(`💾 Salvando configurações de estoque para: ${materialId}`);
    
    const idMaterial = document.getElementById(`${materialId}-id`).value;
    const estoqueMinimo = parseFloat(document.getElementById(`${materialId}-estoque-minimo-input`).value) || 0;
    const estoqueMaximo = parseFloat(document.getElementById(`${materialId}-estoque-maximo-input`).value) || 0;
    
    if (!idMaterial) {
        mostrarErro('Material não selecionado. Selecione um material primeiro.');
        return;
    }
    
    // Obter a filial selecionada
    const filialSelecionada = localStorage.getItem('filialSelecionada');
    if (!filialSelecionada) {
        mostrarErro('Filial não selecionada. Selecione uma filial no sistema primeiro.');
        return;
    }
    
    const dadosEstoque = {
        id_catalogo: idMaterial,
        id_filial: filialSelecionada,
        estoque_minimo: estoqueMinimo,
        estoque_maximo: estoqueMaximo
    };
    
    try {
        console.log('📡 Enviando dados para API:', dadosEstoque);
        
        const response = await fetch('api/materiais_nova_estrutura.php?action=atualizar-configuracoes-estoque', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosEstoque)
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarSucesso('Configurações de estoque salvas com sucesso!');
            console.log('✅ Configurações salvas:', data);
            
        } else {
            mostrarErro('Erro ao salvar configurações: ' + (data.error || 'Erro desconhecido'));
            console.error('❌ Erro ao salvar:', data);
        }
    } catch (error) {
        console.error('❌ Erro de conexão:', error);
        mostrarErro('Erro de conexão ao salvar configurações');
    }
} 