# Sistema de Fornecedores - Grupo Sorrisos

## 📋 Descrição

Sistema para análise de preços de pedidos de compra pelos fornecedores. Permite que fornecedores visualizem pedidos pendentes, definam preços e acompanhem o status dos pedidos.

## 🚀 Funcionalidades

### Para Fornecedores:
- **Login seguro** com email e senha
- **Visualização de pedidos** pendentes para análise
- **Definição de preços** para cada item do pedido
- **Acompanhamento** do status dos pedidos
- **Dashboard** com estatísticas dos pedidos
- **Interface responsiva** para desktop e mobile

### Para Administradores:
- **Envio automático** de pedidos por email
- **Controle de status** dos pedidos
- **Aprovação** de preços definidos pelos fornecedores
- **Histórico completo** de todas as transações

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS**: Bootstrap 5.3
- **Ícones**: Bootstrap Icons
- **Notificações**: SweetAlert2
- **Banco de Dados**: MySQL/MariaDB
- **Email**: PHPMailer (SMTP)

## 📁 Estrutura de Arquivos

```
fornecedor/
├── config.php              # Configurações do sistema
├── login.php               # Tela de login
├── logout.php              # Logout e destruição de sessão
├── analise-precos.php      # Tela principal de análise
├── .htaccess               # Configurações de segurança
└── README.md               # Este arquivo
```

## 🔧 Instalação e Configuração

### 1. Pré-requisitos
- PHP 7.4 ou superior
- MySQL/MariaDB
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL, OpenSSL

### 2. Configuração do Banco
Certifique-se de que as tabelas necessárias existem:
- `tbl_fornecedores` - Dados dos fornecedores
- `tbl_pedidos_compra` - Pedidos de compra
- `tbl_itens_pedido_compra` - Itens dos pedidos
- `tbl_filiais` - Filiais/clínicas
- `tbl_usuarios` - Usuários do sistema

### 3. Configuração de Email
Edite o arquivo `config.php` e configure:
- Servidor SMTP
- Credenciais de email
- Remetente padrão

### 4. Configuração de Sessão
Ajuste as configurações de sessão no `config.php`:
- Tempo de vida da sessão
- Configurações de segurança
- Timezone

## 🔐 Segurança

### Autenticação
- Login com email e senha
- Senhas criptografadas com `password_hash()`
- Sessões seguras com cookies HTTP-only
- Proteção contra ataques de força bruta

### Autorização
- Verificação de sessão em todas as páginas
- Redirecionamento automático para login
- Proteção de arquivos sensíveis

### Validação
- Sanitização de inputs
- Validação de dados
- Proteção contra SQL Injection
- Headers de segurança HTTP

## 📧 Sistema de Email

### Template de Email
- Design responsivo e profissional
- Informações completas do pedido
- Links para aprovação e visualização
- Formatação automática de valores

### Configuração SMTP
- Suporte a Gmail, Outlook, Hostinger
- Configuração automática de charset UTF-8
- Fallback para função nativa do PHP
- Logs detalhados de envio

## 💰 Fluxo de Análise de Preços

### 1. Criação do Pedido
- Administrador cria pedido no sistema principal
- Sistema identifica materiais com estoque baixo
- Pedido é marcado como "pendente"

### 2. Envio para Fornecedor
- Sistema envia email automático
- Fornecedor recebe notificação
- Email contém link para análise

### 3. Análise pelo Fornecedor
- Fornecedor acessa sistema com login
- Visualiza pedidos pendentes
- Define preços para cada item
- Salva alterações

### 4. Aprovação
- Administrador revisa preços
- Aprova ou solicita ajustes
- Pedido avança para produção

## 📱 Interface do Usuário

### Design Responsivo
- Layout adaptável para diferentes dispositivos
- Componentes Bootstrap para consistência
- Ícones intuitivos e navegação clara

### Experiência do Usuário
- Feedback visual para todas as ações
- Validação em tempo real
- Mensagens de erro claras
- Loading states para operações assíncronas

## 🔍 Monitoramento e Logs

### Logs de Sistema
- Erros de aplicação
- Tentativas de login
- Operações de email
- Alterações de preços

### Métricas
- Pedidos pendentes
- Pedidos aprovados
- Pedidos em produção
- Pedidos enviados

## 🚨 Tratamento de Erros

### Validação de Dados
- Verificação de campos obrigatórios
- Validação de formatos (email, valores)
- Sanitização de inputs

### Tratamento de Exceções
- Try-catch em todas as operações críticas
- Logs detalhados de erros
- Mensagens amigáveis para usuários
- Fallbacks para operações críticas

## 📊 Banco de Dados

### Tabelas Principais
```sql
-- Fornecedores
CREATE TABLE tbl_fornecedores (
    id_fornecedor INT PRIMARY KEY AUTO_INCREMENT,
    razao_social VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    cnpj VARCHAR(18),
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Pedidos de Compra
CREATE TABLE tbl_pedidos_compra (
    id_pedido INT PRIMARY KEY AUTO_INCREMENT,
    numero_pedido VARCHAR(50) UNIQUE NOT NULL,
    id_filial INT,
    id_fornecedor INT,
    id_usuario_solicitante INT,
    status ENUM('pendente', 'aprovado', 'em_producao', 'enviado', 'recebido', 'cancelado') DEFAULT 'pendente',
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_entrega_prevista DATE,
    observacoes TEXT,
    FOREIGN KEY (id_filial) REFERENCES tbl_filiais(id_filial),
    FOREIGN KEY (id_fornecedor) REFERENCES tbl_fornecedores(id_fornecedor),
    FOREIGN KEY (id_usuario_solicitante) REFERENCES tbl_usuarios(id_usuario)
);

-- Itens do Pedido
CREATE TABLE tbl_itens_pedido_compra (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    id_pedido INT,
    id_material INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_pedido) REFERENCES tbl_pedidos_compra(id_pedido),
    FOREIGN KEY (id_material) REFERENCES tbl_materiais(id_material)
);
```

## 🔧 Manutenção

### Backup
- Backup regular do banco de dados
- Versionamento de código
- Documentação de alterações

### Atualizações
- Verificação de compatibilidade
- Testes em ambiente de desenvolvimento
- Rollback em caso de problemas

### Monitoramento
- Logs de erro
- Performance do sistema
- Uso de recursos

## 📞 Suporte

### Contato
- **Email**: suporte@gruposorrisos.com.br
- **Telefone**: (XX) XXXX-XXXX
- **Horário**: Segunda a Sexta, 8h às 18h

### Documentação
- Este README
- Comentários no código
- Logs do sistema
- Histórico de alterações

## 📝 Changelog

### Versão 1.0.0 (2024-01-XX)
- ✅ Sistema de login para fornecedores
- ✅ Tela de análise de preços
- ✅ Envio automático de emails
- ✅ Dashboard com estatísticas
- ✅ Interface responsiva
- ✅ Sistema de segurança

## 📄 Licença

Este software é propriedade do Grupo Sorrisos e seu uso é restrito aos fornecedores autorizados.

---

**Desenvolvido com ❤️ para o Grupo Sorrisos** 