// Obter ID da URL
const urlParams = new URLSearchParams(window.location.search);
const filialId = urlParams.get('id');

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    if (filialId) {
        carregarFilial(filialId);
        setupEventListeners();
    } else {
        mostrarErro('ID da filial não fornecido');
    }
});

// Configurar event listeners
function setupEventListeners() {
    document.getElementById('btn-editar').addEventListener('click', function() {
        window.location.href = `editFilial.php?id=${filialId}`;
    });
}

// Carregar filial
function carregarFilial(id) {
    fetch(`backend/api/filiais.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                preencherDados(data.filial);
                mostrarConteudo();
            } else {
                mostrarErro(data.message || 'Erro ao carregar filial');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarErro('Erro ao carregar filial');
        });
}

// Preencher dados
function preencherDados(filial) {
    // Cabeçalho
    document.getElementById('nome-filial').textContent = filial.nome;
    document.getElementById('codigo-filial').textContent = `Código: ${filial.codigo}`;
    
    // Status e tipo badges
    const statusBadge = document.getElementById('status-badge');
    const tipoBadge = document.getElementById('tipo-badge');
    
    statusBadge.textContent = filial.status === 'ativa' ? 'Ativa' : 'Inativa';
    statusBadge.className = `badge fs-6 ${filial.status === 'ativa' ? 'bg-success' : 'bg-warning'}`;
    
    tipoBadge.textContent = filial.tipo === 'matriz' ? 'Matriz' : 'Filial';
    tipoBadge.className = `badge fs-6 ms-2 ${filial.tipo === 'matriz' ? 'bg-primary' : 'bg-secondary'}`;
    
    // Informações básicas
    document.getElementById('codigo').textContent = filial.codigo || '-';
    document.getElementById('nome').textContent = filial.nome || '-';
    document.getElementById('tipo').textContent = formatarTipo(filial.tipo);
    document.getElementById('status').textContent = formatarStatus(filial.status);
    document.getElementById('data_abertura').textContent = formatarData(filial.data_abertura);
    
    // Informações fiscais
    document.getElementById('cnpj').textContent = filial.cnpj || '-';
    document.getElementById('inscricao_estadual').textContent = filial.inscricao_estadual || '-';
    
    // Endereço
    document.getElementById('endereco').textContent = filial.endereco || '-';
    document.getElementById('numero').textContent = filial.numero || '-';
    document.getElementById('complemento').textContent = filial.complemento || '-';
    document.getElementById('bairro').textContent = filial.bairro || '-';
    document.getElementById('cidade').textContent = filial.cidade || '-';
    document.getElementById('estado').textContent = filial.estado || '-';
    document.getElementById('cep').textContent = filial.cep || '-';
    
    // Contato
    document.getElementById('telefone').textContent = filial.telefone || '-';
    document.getElementById('email').textContent = filial.email || '-';
    
    // Responsável
    document.getElementById('responsavel').textContent = filial.responsavel || '-';
    document.getElementById('email_responsavel').textContent = filial.email_responsavel || '-';
    document.getElementById('telefone_responsavel').textContent = filial.telefone_responsavel || '-';
    
    // Estatísticas
    document.getElementById('total_funcionarios').textContent = filial.total_funcionarios || '0';
    document.getElementById('created_at').textContent = formatarDataHora(filial.created_at);
    document.getElementById('updated_at').textContent = formatarDataHora(filial.updated_at);
    
    // Observações
    document.getElementById('observacoes').textContent = filial.observacoes || 'Nenhuma observação registrada.';
}

// Funções de formatação
function formatarTipo(tipo) {
    if (!tipo) return '-';
    return tipo === 'matriz' ? 'Matriz' : 'Filial';
}

function formatarStatus(status) {
    if (!status) return '-';
    return status === 'ativa' ? 'Ativa' : 'Inativa';
}

function formatarData(data) {
    if (!data) return '-';
    try {
        return new Date(data).toLocaleDateString('pt-BR');
    } catch (e) {
        return data;
    }
}

function formatarDataHora(dataHora) {
    if (!dataHora) return '-';
    try {
        const data = new Date(dataHora);
        return data.toLocaleString('pt-BR');
    } catch (e) {
        return dataHora;
    }
}

// Funções de visualização
function mostrarConteudo() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('conteudo').style.display = 'block';
    document.getElementById('erro').style.display = 'none';
}

function mostrarErro(mensagem) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('conteudo').style.display = 'none';
    document.getElementById('erro').style.display = 'block';
    document.getElementById('mensagem-erro').textContent = mensagem;
} 