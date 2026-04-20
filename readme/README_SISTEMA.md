# Sistema de Estoque - Grupo Sorrisos

## 📋 Visão Geral

Sistema completo de gestão de estoque desenvolvido para o Grupo Sorrisos, com controle de materiais, movimentações, filiais, fornecedores e relatórios.

## 🏗️ Arquitetura do Sistema

### Estrutura de Pastas
```
├── config/                 # Configurações do sistema
│   ├── config.php         # Configurações gerais e API keys
│   ├── conexao.php        # Classe de conexão com banco de dados
│   └── autoload.php       # Autoloader de classes
├── models/                # Modelos de dados
│   ├── BaseModel.php      # Classe base para todos os modelos
│   ├── Material.php       # Modelo para materiais
│   ├── Movimentacao.php   # Modelo para movimentações
│   ├── Filial.php         # Modelo para filiais
│   ├── Categoria.php      # Modelo para categorias
│   ├── Fornecedor.php     # Modelo para fornecedores
│   ├── UnidadeMedida.php  # Modelo para unidades de medida
│   └── TipoMovimentacao.php # Modelo para tipos de movimentação
├── backend/               # Backend da aplicação
│   ├── controllers/       # Controladores (a serem criados)
│   └── api/              # APIs (a serem criadas)
├── scripts/              # Scripts utilitários
│   └── init_system.php   # Script de inicialização do sistema
├── database/             # Banco de dados
│   └── gruposorrisos.sql # Estrutura completa do banco
└── assets/               # Assets da aplicação
```

## 🚀 Instalação e Configuração

### 1. Pré-requisitos
- PHP 8.0 ou superior
- MySQL 5.7 ou MariaDB 10.4+
- Apache/Nginx
- Extensões PHP: PDO, PDO_MySQL, JSON

### 2. Configuração do Banco de Dados

1. **Importe o banco de dados:**
   ```bash
   mysql -u root -p < database/gruposorrisos.sql
   ```

2. **Configure as credenciais** em `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gruposorrisos');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### 3. Inicialização do Sistema

Execute o script de inicialização para criar dados básicos:

```bash
php scripts/init_system.php
```

Para verificar o status do sistema:
```bash
php scripts/init_system.php status
```

## 📊 Funcionalidades Implementadas

### ✅ Modelos Criados

1. **BaseModel** - Classe base com operações CRUD
2. **Material** - Gestão completa de materiais
3. **Movimentacao** - Controle de movimentações de estoque
4. **Filial** - Gestão de filiais
5. **Categoria** - Categorização de materiais
6. **Fornecedor** - Gestão de fornecedores
7. **UnidadeMedida** - Unidades de medida
8. **TipoMovimentacao** - Tipos de entrada/saída

### 🔧 Funcionalidades por Modelo

#### Material
- ✅ CRUD completo
- ✅ Busca com filtros avançados
- ✅ Controle de estoque
- ✅ Validação de códigos únicos
- ✅ Relacionamentos com outras entidades
- ✅ Alertas de estoque baixo/zerado

#### Movimentacao
- ✅ Registro de movimentações
- ✅ Atualização automática de estoque
- ✅ Validação de estoque disponível
- ✅ Controle de transações
- ✅ Relatórios por período
- ✅ Filtros por tipo, material, filial

#### Filial
- ✅ Gestão de filiais
- ✅ Validação de códigos únicos
- ✅ Estatísticas por filial
- ✅ Controle de status ativo/inativo

#### Categoria
- ✅ Categorias hierárquicas
- ✅ Contagem de materiais
- ✅ Validação antes de exclusão
- ✅ Estatísticas por categoria

#### Fornecedor
- ✅ Gestão completa de fornecedores
- ✅ Validação de CNPJ único
- ✅ Relacionamento com materiais
- ✅ Histórico de pedidos

#### UnidadeMedida
- ✅ Unidades padrão do sistema
- ✅ Validação de siglas únicas
- ✅ Relacionamento com materiais

#### TipoMovimentacao
- ✅ Tipos de entrada e saída
- ✅ Estatísticas de uso
- ✅ Validação antes de exclusão

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais
- `tbl_materiais` - Materiais do estoque
- `tbl_movimentacoes` - Movimentações de estoque
- `tbl_filiais` - Filiais da empresa
- `tbl_categorias` - Categorias de materiais
- `tbl_fornecedores` - Fornecedores
- `tbl_unidades_medida` - Unidades de medida
- `tbl_tipos_movimentacao` - Tipos de movimentação

### Tabelas de Suporte
- `tbl_alertas_estoque` - Alertas de estoque
- `tbl_logs_sistema` - Logs do sistema
- `tbl_usuarios` - Usuários do sistema
- `tbl_perfis` - Perfis de acesso

### Views
- `vw_estoque_atual` - Estoque atual consolidado
- `vw_movimentacoes_detalhadas` - Movimentações com detalhes
- `vw_alertas_nao_lidos` - Alertas não lidos

## 🔄 Fluxo de Trabalho

### 1. Cadastro de Materiais
1. Cadastrar categorias
2. Cadastrar fornecedores
3. Cadastrar materiais com:
   - Código único por filial
   - Categoria
   - Fornecedor
   - Unidade de medida
   - Estoque mínimo/máximo

### 2. Movimentações
1. Selecionar tipo de movimentação
2. Escolher material e filial
3. Informar quantidade
4. Sistema atualiza estoque automaticamente
5. Gera alertas se necessário

### 3. Controle de Estoque
- Monitoramento automático de estoque baixo
- Alertas de estoque zerado
- Relatórios de movimentação
- Inventário por filial

## 📈 Relatórios Disponíveis

### Estoque
- Estoque atual por filial
- Materiais com estoque baixo
- Materiais com estoque zerado
- Valor total do estoque

### Movimentações
- Movimentações por período
- Movimentações por tipo
- Movimentações por material
- Resumo de entradas/saídas

### Fornecedores
- Materiais por fornecedor
- Top fornecedores
- Histórico de pedidos

## 🔒 Segurança

### Validações Implementadas
- ✅ Validação de dados de entrada
- ✅ Controle de transações
- ✅ Verificação de integridade referencial
- ✅ Soft delete (exclusão lógica)
- ✅ Logs de auditoria

### Próximas Implementações
- 🔄 Autenticação de usuários
- 🔄 Controle de acesso por perfil
- 🔄 Criptografia de dados sensíveis
- 🔄 Rate limiting nas APIs

## 🚧 Próximos Passos

### Backend (Controllers e APIs)
- [ ] Criar controladores REST
- [ ] Implementar APIs JSON
- [ ] Sistema de autenticação
- [ ] Validação de entrada
- [ ] Tratamento de erros

### Frontend
- [ ] Interface web responsiva
- [ ] Dashboard com gráficos
- [ ] Formulários de cadastro
- [ ] Listagens com filtros
- [ ] Relatórios em PDF

### Funcionalidades Avançadas
- [ ] Pedidos de compra
- [ ] Controle de contas a pagar/receber
- [ ] Integração com fornecedores
- [ ] Notificações por email
- [ ] Backup automático

## 🛠️ Desenvolvimento

### Como Usar os Modelos

```php
// Carregar configurações
require_once 'config/autoload.php';
loadConfig();

// Usar um modelo
$material = new Material();

// Buscar todos os materiais
$materiais = $material->findAllWithRelations();

// Buscar com filtros
$filtros = ['id_filial' => 1, 'estoque_baixo' => true];
$resultado = $material->findWithFilters($filtros, 1, 10);

// Inserir novo material
$dados = [
    'codigo' => 'MAT001',
    'nome' => 'Papel A4',
    'id_categoria' => 1,
    'id_filial' => 1,
    'id_unidade' => 1,
    'estoque_minimo' => 10,
    'estoque_atual' => 0
];
$id = $material->insert($dados);
```

### Como Registrar Movimentações

```php
$movimentacao = new Movimentacao();

$dados = [
    'id_filial' => 1,
    'id_material' => 1,
    'id_tipo_movimentacao' => 1, // Tipo de entrada
    'id_usuario' => 1,
    'quantidade' => 50,
    'preco_unitario' => 25.00,
    'numero_documento' => 'NF001',
    'observacoes' => 'Entrada inicial'
];

$idMovimentacao = $movimentacao->registrarMovimentacao($dados);
```

## 📞 Suporte

Para dúvidas ou suporte técnico:
- Email: suporte@gruposorrisos.com.br
- Documentação: Este arquivo README
- Issues: Repositório do projeto

## 📝 Changelog

### v1.0.0 (Atual)
- ✅ Estrutura base do sistema
- ✅ Modelos completos
- ✅ Script de inicialização
- ✅ Documentação básica
- ✅ Validações e controles

---

**Sistema de Estoque - Grupo Sorrisos**  
Desenvolvido com ❤️ para otimizar a gestão de estoque 