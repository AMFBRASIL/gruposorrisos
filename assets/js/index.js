// Variáveis globais
let filialSelecionada = null;
let filiais = [];

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM carregado! Iniciando aplicação...');
    
    // Carregar filial salva primeiro
    carregarFilialSalva();
    
    // Carregar seletor de filiais
    carregarSeletorFiliais();
    
    // Depois carregar outros dados
    setTimeout(() => {
        carregarDashboardData();
        carregarProdutosEstoqueBaixo();
    }, 1000);
});

// Carregar filial salva no localStorage
function carregarFilialSalva() {
    const filialSalva = localStorage.getItem('filialSelecionada');
    if (filialSalva) {
        filialSelecionada = parseInt(filialSalva);
        console.log('📋 Clínica salva carregada:', filialSelecionada);
    } else {
        console.log('📋 Nenhuma Clínica salva encontrada');
    }
}

// Carregar seletor de filiais
async function carregarSeletorFiliais() {
    console.log('🔍 Carregando seletor de filiais...');
    
    const container = document.getElementById('filial-selector-container');
    if (!container) {
        console.error('❌ Container filial-selector-container não encontrado!');
        return;
    }
    
    // Mostrar loading
    container.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Carregando filiais...</p>
        </div>
    `;
    
    try {
        console.log('📡 Fazendo requisição para API...');
        const response = await fetch('backend/api/filiais.php');
        console.log('📥 Status da resposta:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('📋 Dados recebidos:', data);
            
            if (data.success && data.filiais) {
                console.log('✅ Clínicas carregadas:', data.filiais.length);
                filiais = data.filiais;
                renderizarSeletorFiliais();
            } else {
                console.error('❌ Erro na resposta:', data);
                mostrarErroSeletor('Erro na resposta da API');
            }
        } else {
            console.error('❌ Erro HTTP:', response.status);
            mostrarErroSeletor(`Erro HTTP: ${response.status}`);
        }
    } catch (error) {
        console.error('💥 Erro ao carregar filiais:', error);
        mostrarErroSeletor(`Erro: ${error.message}`);
    }
}

// Renderizar seletor de filiais
function renderizarSeletorFiliais() {
    const container = document.getElementById('filial-selector-container');
    
    if (filiais.length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Nenhuma filial cadastrada
            </div>
        `;
        return;
    }
    
    // Encontrar filial selecionada
    const filialAtual = filialSelecionada ? filiais.find(f => f.id === filialSelecionada) : null;
    console.log('🎯 Filial atual:', filialAtual);
    
    // Ordenar filiais: selecionada primeiro, depois as demais por código
    const filiaisOrdenadas = [...filiais].sort((a, b) => {
        // Se a filial A é a selecionada, ela vem primeiro
        if (a.id === filialSelecionada) return -1;
        // Se a filial B é a selecionada, ela vem primeiro
        if (b.id === filialSelecionada) return 1;
        // Caso contrário, ordenar por código
        return a.codigo.localeCompare(b.codigo);
    });
    
    let html = `
        <div class="filial-dropdown">
            <button class="filial-dropdown-toggle ${filialAtual ? 'active' : ''}" onclick="toggleFilialDropdown()">
                <div class="filial-info">
                    <span class="filial-nome">${filialAtual ? filialAtual.nome : 'Selecione uma filial'}</span>
                    <span class="filial-codigo">${filialAtual ? filialAtual.codigo : 'Clique para selecionar'}</span>
                    ${filialAtual && filialAtual.cnpj ? `<span class="filial-cnpj">CNPJ: ${filialAtual.cnpj}</span>` : ''}
                </div>
                <span class="filial-status ${filialAtual && filialAtual.status ? 'ativo' : 'inativo'}">
                    ${filialAtual && filialAtual.status ? 'Ativo' : 'Selecionar'}
                </span>
                <i class="bi bi-chevron-down ms-2"></i>
            </button>
            
            <div class="filial-dropdown-menu" id="filialDropdownMenu">
                <input type="text" class="filial-dropdown-search" placeholder="Buscar filial..." onkeyup="filtrarFiliais(this.value)">
                <div id="filialDropdownList">
                    ${filiaisOrdenadas.map(filial => `
                        <div class="filial-dropdown-item${filialSelecionada === filial.id ? ' active' : ''}" onclick="selecionarFilial(${filial.id})">
                            <div class="filial-info">
                                <span class="filial-nome">${filial.nome}</span>
                                <span class="filial-codigo">${filial.codigo}</span>
                                ${filial.cnpj ? `<span class="filial-cnpj">CNPJ: ${filial.cnpj}</span>` : ''}
                            </div>
                            <span class="filial-status ${filial.status ? 'ativo' : 'inativo'}">
                                ${filial.status ? 'Ativo' : 'Inativo'}
                            </span>
                            ${filialSelecionada === filial.id ? '<i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        
        <div class="mt-2">
            <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='filiais.php'">
                <i class="bi bi-gear me-1"></i>Gerenciar Clínicas
            </button>
        </div>
    `;
    
    container.innerHTML = html;
    console.log('✅ Seletor de Clinicas renderizado');
    
    // Se há uma filial selecionada, mostrar notificação
    if (filialAtual) {
        console.log('🎯 Clínica selecionada:', filialAtual.nome);
        // Atualizar dados do dashboard com a filial selecionada
        carregarDashboardData();
        carregarProdutosEstoqueBaixo();
    }
}

// Toggle do dropdown
function toggleFilialDropdown() {
    const menu = document.getElementById('filialDropdownMenu');
    const toggle = document.querySelector('.filial-dropdown-toggle');
    
    if (menu.classList.contains('show')) {
        menu.classList.remove('show');
        toggle.classList.remove('active');
    } else {
        menu.classList.add('show');
        toggle.classList.add('active');
    }
}

// Filtrar filiais
function filtrarFiliais(query) {
    const list = document.getElementById('filialDropdownList');
    const filteredFiliais = filiais.filter(f =>
        f.nome.toLowerCase().includes(query.toLowerCase()) ||
        f.codigo.toLowerCase().includes(query.toLowerCase()) ||
        (f.cnpj && f.cnpj.toLowerCase().includes(query.toLowerCase()))
    );
    
    // Ordenar filiais filtradas: selecionada primeiro, depois as demais por código
    const filiaisOrdenadas = filteredFiliais.sort((a, b) => {
        // Se a filial A é a selecionada, ela vem primeiro
        if (a.id === filialSelecionada) return -1;
        // Se a filial B é a selecionada, ela vem primeiro
        if (b.id === filialSelecionada) return 1;
        // Caso contrário, ordenar por código
        return a.codigo.localeCompare(b.codigo);
    });
    
    list.innerHTML = filiaisOrdenadas.map(filial => `
        <div class="filial-dropdown-item${filialSelecionada === filial.id ? ' active' : ''}" onclick="selecionarFilial(${filial.id})">
            <div class="filial-info">
                <span class="filial-nome">${filial.nome}</span>
                <span class="filial-codigo">${filial.codigo}</span>
                ${filial.cnpj ? `<span class="filial-cnpj">CNPJ: ${filial.cnpj}</span>` : ''}
            </div>
            <span class="filial-status ${filial.status ? 'ativo' : 'inativo'}">
                ${filial.status ? 'Ativo' : 'Inativo'}
            </span>
            ${filialSelecionada === filial.id ? '<i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}
        </div>
    `).join('');
}

// Selecionar filial
function selecionarFilial(filialId) {
    if (!filialId) {
        filialSelecionada = null;
        localStorage.removeItem('filialSelecionada');
        return;
    }
    
    const filialAnterior = filialSelecionada;
    filialSelecionada = parseInt(filialId);
    
    console.log('🔄 Selecionando filial:', filialSelecionada);
    
    // Salvar seleção no localStorage
    localStorage.setItem('filialSelecionada', filialSelecionada);
    
    // Fechar dropdown
    toggleFilialDropdown();
    
    // Recarregar dados do dashboard
    carregarDashboardData();
    carregarProdutosEstoqueBaixo();
    
    // Mostrar notificação apenas se mudou de filial
    if (filialAnterior !== filialSelecionada) {
        const filial = filiais.find(f => f.id === filialSelecionada);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Filial selecionada!',
                text: `Agora você está visualizando dados da ${filial.nome}`,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert(`Filial selecionada: ${filial.nome}`);
        }
    }
    
    // Re-renderizar o seletor para mostrar a seleção
    renderizarSeletorFiliais();
}

// Carregar dados do dashboard
async function carregarDashboardData() {
    console.log('📊 Carregando dados do dashboard...');
    
    try {
        // Carregar dados básicos do estoque
        await carregarDadosEstoque();
        
        // Carregar dados dos novos quadros
        await carregarPedidosPendentes();
        await carregarAlertas();
        await carregarMovimentacoesHoje();
        await carregarTicketsAbertos();
        await carregarResumoAtividades();
        
    } catch (error) {
        console.error('❌ Erro ao carregar dados do dashboard:', error);
    }
}

// Carregar produtos com estoque baixo
async function carregarProdutosEstoqueBaixo() {
    try {
        console.log('🔍 Carregando produtos com estoque baixo...');
        const params = new URLSearchParams();
        if (filialSelecionada) {
            params.append('filial_id', filialSelecionada);
            console.log('🏢 Filial selecionada:', filialSelecionada);
        } else {
            console.log('⚠️ Nenhuma filial selecionada');
        }
        
        const url = `api/produtos.php?path=estoque-baixo&${params}`;
        console.log('📡 URL da requisição:', url);
        
        const response = await fetch(url);
        console.log('📥 Status da resposta:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('📋 Dados recebidos:', data);
            const container = document.getElementById('produtos-estoque-baixo');
            
            if (data.success && data.data && data.data.length > 0) {
                console.log('✅ Produtos encontrados:', data.data.length);
                let html = '<ul class="list-group list-group-flush">';
                data.data.slice(0, 5).forEach(produto => {
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                ${produto.nome}<br>
                                <small class="text-muted">${produto.categoria_nome || 'Sem categoria'}</small>
                            </div>
                            <div class="text-end">
                                <span class="text-warning">${produto.estoque_atual} ${produto.unidade || 'un'}</span><br>
                                <small class="text-muted">Mín: ${produto.estoque_minimo}</small>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                container.innerHTML = html;
            } else {
                console.log('✅ Nenhum produto com estoque baixo encontrado');
                container.innerHTML = `
                    <div class="text-center text-success">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        <p>Nenhum produto com estoque baixo!</p>
                    </div>
                `;
            }
        } else {
            console.error('❌ Erro na resposta:', response.status, response.statusText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
    } catch (error) {
        console.error('❌ Erro ao carregar produtos com estoque baixo:', error);
        document.getElementById('produtos-estoque-baixo').innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                <p>Erro ao carregar dados</p>
                <small class="text-danger">${error.message}</small>
            </div>
        `;
    }
}

// Carregar dados básicos do estoque
async function carregarDadosEstoque() {
    try {
        const params = new URLSearchParams();
        if (filialSelecionada) {
            params.append('filial_id', filialSelecionada);
        }
        
        const response = await fetch(`api/produtos.php?path=estatisticas&${params}`);
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                document.getElementById('total-produtos').textContent = data.total_produtos || 0;
                document.getElementById('estoque-baixo').textContent = data.produtos_estoque_baixo || 0;
                document.getElementById('estoque-zerado').textContent = data.produtos_estoque_zerado || 0;
                document.getElementById('valor-total').textContent = 
                    'R$ ' + (data.valor_total_custo || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            }
        }
    } catch (error) {
        console.error('❌ Erro ao carregar dados do estoque:', error);
    }
}

// Carregar pedidos pendentes
async function carregarPedidosPendentes() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=stats');
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                const pedidosPendentes = data.stats.pedidos_pendentes || 0;
                document.getElementById('pedidos-pendentes').textContent = pedidosPendentes;
            }
        }
    } catch (error) {
        console.error('❌ Erro ao carregar pedidos pendentes:', error);
        document.getElementById('pedidos-pendentes').textContent = '0';
    }
}

// Carregar alertas (estoque baixo + vencimentos)
async function carregarAlertas() {
    try {
        let totalAlertas = 0;
        
        // Alertas de estoque baixo
        const estoqueBaixo = parseInt(document.getElementById('estoque-baixo').textContent) || 0;
        totalAlertas += estoqueBaixo;
        
        // Alertas de vencimento (se houver API)
        try {
            const response = await fetch('backend/api/produtos.php?action=vencimentos-proximos');
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    totalAlertas += data.data.vencimentos_proximos || 0;
                }
            }
        } catch (e) {
            // Se não houver API de vencimentos, ignora
        }
        
        document.getElementById('total-alertas').textContent = totalAlertas;
    } catch (error) {
        console.error('❌ Erro ao carregar alertas:', error);
        document.getElementById('total-alertas').textContent = '0';
    }
}

// Carregar movimentações de hoje
async function carregarMovimentacoesHoje() {
    try {
        const response = await fetch('backend/api/movimentacoes.php?action=stats');
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                const movimentacoesHoje = data.data.total_movimentacoes || 0;
                document.getElementById('movimentacoes-hoje').textContent = movimentacoesHoje;
            }
        }
    } catch (error) {
        console.error('❌ Erro ao carregar movimentações:', error);
        document.getElementById('movimentacoes-hoje').textContent = '0';
    }
}

// Carregar tickets abertos
async function carregarTicketsAbertos() {
    try {
        const response = await fetch('backend/api/tickets.php?action=estatisticas');
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                const ticketsAbertos = data.estatisticas.tickets_abertos || 0;
                document.getElementById('tickets-abertos').textContent = ticketsAbertos;
            }
        }
    } catch (error) {
        console.error('❌ Erro ao carregar tickets:', error);
        document.getElementById('tickets-abertos').textContent = '0';
    }
}

// Carregar resumo de atividades
async function carregarResumoAtividades() {
    try {
        const container = document.getElementById('resumo-atividades');
        
        // Criar resumo baseado nos dados já carregados
        const totalProdutos = document.getElementById('total-produtos').textContent;
        const estoqueBaixo = document.getElementById('estoque-baixo').textContent;
        const movimentacoes = document.getElementById('movimentacoes-hoje').textContent;
        const pedidosPendentes = document.getElementById('pedidos-pendentes').textContent;
        
        container.innerHTML = `
            <div class="row g-2">
                <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="fw-bold text-success">${totalProdutos}</div>
                        <small class="text-muted">Produtos</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="fw-bold text-warning">${estoqueBaixo}</div>
                        <small class="text-muted">Estoque Baixo</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="fw-bold text-info">${movimentacoes}</div>
                        <small class="text-muted">Movimentações</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="fw-bold text-primary">${pedidosPendentes}</div>
                        <small class="text-muted">Pedidos</small>
                    </div>
                </div>
            </div>
        `;
        
    } catch (error) {
        console.error('❌ Erro ao carregar resumo de atividades:', error);
        document.getElementById('resumo-atividades').innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                <p>Erro ao carregar</p>
            </div>
        `;
    }
}

// Mostrar erro no seletor
function mostrarErroSeletor(mensagem) {
    const container = document.getElementById('filial-selector-container');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${mensagem}
        </div>
    `;
}

// Fechar dropdown quando clicar fora
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.filial-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        const menu = document.getElementById('filialDropdownMenu');
        const toggle = document.querySelector('.filial-dropdown-toggle');
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            toggle.classList.remove('active');
        }
    }
});