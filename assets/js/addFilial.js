// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarEstados();
    setupMasks();
    setupFormValidation();
});

// Carregar estados
function carregarEstados() {
    fetch('backend/api/estados.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('estado');
                data.estados.forEach(estado => {
                    const option = document.createElement('option');
                    option.value = estado.uf;
                    option.textContent = estado.nome;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Erro ao carregar estados:', error));
}

// Configurar máscaras
function setupMasks() {
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj');
    cnpjInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        }
    });

    // Máscara para telefone
    const telefoneInputs = ['telefone', 'telefone_responsavel'];
    telefoneInputs.forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            }
        });
    });

    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    cepInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 8) {
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        }
    });
}

// Configurar validação do formulário
function setupFormValidation() {
    const form = document.getElementById('formFilial');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (form.checkValidity()) {
            salvarFilial();
        } else {
            form.classList.add('was-validated');
        }
    });
}

// Salvar filial
function salvarFilial() {
    const form = document.getElementById('formFilial');
    const formData = new FormData(form);
    const data = {};
    
    // Converter FormData para objeto
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    // Mostrar loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
    submitBtn.disabled = true;
    
    fetch('backend/api/filiais.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: result.message,
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'filiais.php';
            });
        } else {
            // Erro
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.message,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao salvar filial. Tente novamente.',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        // Restaurar botão
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Validação de CNPJ
function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]/g, '');
    
    if (cnpj.length !== 14) return false;
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cnpj)) return false;
    
    // Validar primeiro dígito verificador
    let soma = 0;
    let peso = 2;
    for (let i = 11; i >= 0; i--) {
        soma += parseInt(cnpj.charAt(i)) * peso;
        peso = peso === 9 ? 2 : peso + 1;
    }
    let digito = 11 - (soma % 11);
    if (digito > 9) digito = 0;
    if (parseInt(cnpj.charAt(12)) !== digito) return false;
    
    // Validar segundo dígito verificador
    soma = 0;
    peso = 2;
    for (let i = 12; i >= 0; i--) {
        soma += parseInt(cnpj.charAt(i)) * peso;
        peso = peso === 9 ? 2 : peso + 1;
    }
    digito = 11 - (soma % 11);
    if (digito > 9) digito = 0;
    if (parseInt(cnpj.charAt(13)) !== digito) return false;
    
    return true;
}

// Validação de email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validação de CEP
function validarCEP(cep) {
    const cepLimpo = cep.replace(/\D/g, '');
    return cepLimpo.length === 8;
}

// Event listeners para validações em tempo real
document.addEventListener('DOMContentLoaded', function() {
    // Validação de CNPJ
    const cnpjInput = document.getElementById('cnpj');
    cnpjInput.addEventListener('blur', function() {
        const cnpj = this.value;
        if (cnpj && !validarCNPJ(cnpj)) {
            this.setCustomValidity('CNPJ inválido');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Validação de email
    const emailInputs = ['email', 'email_responsavel'];
    emailInputs.forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener('blur', function() {
            const email = this.value;
            if (email && !validarEmail(email)) {
                this.setCustomValidity('Email inválido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Validação de CEP
    const cepInput = document.getElementById('cep');
    cepInput.addEventListener('blur', function() {
        const cep = this.value;
        if (cep && !validarCEP(cep)) {
            this.setCustomValidity('CEP inválido');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Auto-completar endereço pelo CEP
    cepInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarCep(cep);
        }
    });
});

// Buscar CEP via API
function buscarCep(cep) {
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            if (!data.erro) {
                document.getElementById('endereco').value = data.logradouro || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.localidade || '';
                document.getElementById('estado').value = data.uf || '';
            }
        })
        .catch(error => {
            console.error('Erro ao buscar CEP:', error);
        });
}

// Limpar formulário
function limparFormulario() {
    document.getElementById('formFilial').reset();
    document.getElementById('formFilial').classList.remove('was-validated');
    
    // Limpar classes de validação
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
    });
}

// Gerar código automático
function gerarCodigo() {
    const tipo = document.getElementById('tipo').value;
    if (tipo) {
        const prefixo = tipo === 'matriz' ? 'MAT' : 'FIL';
        const timestamp = Date.now().toString().slice(-6);
        document.getElementById('codigo').value = `${prefixo}${timestamp}`;
    }
}

// Event listener para gerar código quando tipo for selecionado
document.getElementById('tipo').addEventListener('change', function() {
    if (!document.getElementById('codigo').value) {
        gerarCodigo();
    }
}); 