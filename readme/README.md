# Sistema de Controle de Estoque

Um sistema web completo para controle de estoque com funcionalidades avançadas de gestão de produtos, movimentações, alertas e relatórios.

## 🚀 Funcionalidades

### ✅ Cadastros Básicos
- **Produtos/Materiais**: Cadastro completo com SKU, código de barras, categoria, fornecedor, preços e estoque mínimo
- **Categorias**: Organização hierárquica de produtos
- **Fornecedores**: Gestão completa de fornecedores com dados fiscais
- **Usuários**: Sistema de usuários com perfis e permissões
- **Empresas/Filiais**: Suporte multi-empresa

### ✅ Controle de Estoque
- **Movimentações**: Entradas, saídas e ajustes de estoque
- **Alertas Automáticos**: Notificações para estoque baixo e zerado
- **Pedidos de Compra**: Gestão completa de pedidos
- **Controle Financeiro**: Contas a pagar e receber

### ✅ Relatórios e Analytics
- **Dashboard Interativo**: Indicadores em tempo real
- **Relatórios Gerenciais**: Exportação para PDF e Excel
- **Histórico de Movimentações**: Rastreamento completo
- **Logs de Auditoria**: Registro de todas as ações

### ✅ Segurança e API
- **Autenticação JWT**: Sistema seguro de login
- **API RESTful**: Endpoints protegidos
- **Controle de Permissões**: Perfis de acesso
- **Backup Automático**: Proteção de dados

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS**: Bootstrap 5
- **Autenticação**: JWT (JSON Web Tokens)
- **Gráficos**: Chart.js
- **Ícones**: Bootstrap Icons

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache/Nginx
- Extensões PHP: PDO, PDO_MySQL, JSON

## 🔧 Instalação

### 1. Clone o repositório
```bash
git clone [url-do-repositorio]
cd sistema-estoque
```

### 2. Configure o banco de dados
Edite o arquivo `config/database.php` com suas credenciais:
```php
private $host = 'localhost';
private $db_name = 'estoque_sistema';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 3. Execute a instalação
Acesse no navegador:
```
http://localhost/sistema-estoque/install.php
```

### 4. Acesse o sistema
```
http://localhost/sistema-estoque/login.php
```

**Credenciais padrão:**
- Email: `admin@sistema.com`
- Senha: `password`

## 📁 Estrutura do Projeto

```
sistema-estoque/
├── api/                    # APIs RESTful
│   ├── auth.php           # Autenticação
│   ├── produtos.php       # CRUD de produtos
│   └── ...
├── auth/                   # Sistema de autenticação
│   └── JWTAuth.php        # Classe JWT
├── config/                 # Configurações
│   └── database.php       # Conexão com banco
├── models/                 # Modelos de dados
│   ├── BaseModel.php      # Classe base
│   ├── Produto.php        # Modelo de produtos
│   └── ...
├── database/               # Scripts de banco
│   └── schema.sql         # Schema completo
├── assets/                 # Recursos estáticos
│   ├── css/               # Estilos
│   └── js/                # JavaScript
├── uploads/                # Uploads de arquivos
├── backups/                # Backups automáticos
├── logs/                   # Logs do sistema
├── index.php              # Dashboard principal
├── login.php              # Página de login
├── install.php            # Script de instalação
└── README.md              # Documentação
```

## 🔐 API Endpoints

### Autenticação
- `POST /api/auth.php?action=login` - Login
- `POST /api/auth.php?action=logout` - Logout
- `POST /api/auth.php?action=refresh` - Renovar token

### Produtos
- `GET /api/produtos.php` - Listar produtos
- `GET /api/produtos.php?path=estatisticas` - Estatísticas
- `GET /api/produtos.php?path=estoque-baixo` - Produtos com estoque baixo
- `POST /api/produtos.php` - Criar produto
- `PUT /api/produtos.php/{id}` - Atualizar produto
- `DELETE /api/produtos.php/{id}` - Deletar produto

### Movimentações
- `POST /api/produtos.php?path=movimentacao/{id}` - Registrar movimentação

## 🎯 Funcionalidades Principais

### Dashboard
- Visão geral do estoque
- Gráficos de movimentações
- Alertas de estoque baixo
- Indicadores financeiros

### Gestão de Produtos
- Cadastro completo de produtos
- Controle de estoque mínimo
- Histórico de movimentações
- Busca por SKU/código de barras

### Movimentações
- Entrada de produtos
- Saída de produtos
- Ajustes de estoque
- Documentação de referência

### Relatórios
- Relatório de estoque atual
- Histórico de movimentações
- Produtos com estoque baixo
- Exportação para PDF/Excel

## 🔒 Segurança

- **Autenticação JWT**: Tokens seguros com expiração
- **Controle de Permissões**: Perfis de acesso granular
- **Logs de Auditoria**: Registro de todas as ações
- **Validação de Dados**: Sanitização e validação
- **Proteção CSRF**: Tokens de segurança

## 📊 Perfis de Usuário

### Administrador
- Acesso total ao sistema
- Gestão de usuários
- Configurações do sistema

### Gerente
- Gestão de produtos
- Visualização de relatórios
- Controle de movimentações

### Operador
- Visualização de produtos
- Registro de movimentações
- Acesso limitado

## 🚨 Alertas e Notificações

- **Estoque Baixo**: Produtos abaixo do mínimo
- **Estoque Zerado**: Produtos sem estoque
- **Vencimento**: Produtos próximos do vencimento
- **Movimentações**: Notificações de entradas/saídas

## 📈 Relatórios Disponíveis

1. **Relatório de Estoque Atual**
   - Quantidade em estoque
   - Valor total
   - Produtos críticos

2. **Histórico de Movimentações**
   - Entradas e saídas
   - Período personalizado
   - Filtros por produto/categoria

3. **Relatório de Produtos Críticos**
   - Estoque baixo
   - Estoque zerado
   - Sugestões de compra

4. **Relatório Financeiro**
   - Valor total em estoque
   - Custo vs. preço de venda
   - Margem de lucro

## 🔧 Configurações

### Backup Automático
- Configurado em `config/database.php`
- Retenção de 30 dias
- Backup diário automático

### Upload de Arquivos
- Tipos permitidos: jpg, jpeg, png, pdf, xlsx, xls
- Tamanho máximo: 5MB
- Diretório: `uploads/`

### JWT
- Tempo de expiração: 1 hora
- Renovação automática
- Invalidação no logout

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
1. Verifique se o MySQL está rodando
2. Confirme as credenciais em `config/database.php`
3. Verifique se o banco `estoque_sistema` existe

### Erro de Permissões
1. Verifique se os diretórios têm permissão de escrita
2. Configure permissões 755 para diretórios
3. Configure permissões 644 para arquivos

### Token Expirado
1. Faça logout e login novamente
2. Verifique o horário do servidor
3. Ajuste o tempo de expiração se necessário

## 📞 Suporte

Para suporte técnico ou dúvidas:
- Email: suporte@sistema.com
- Documentação: [link-da-documentacao]
- Issues: [link-do-github]

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. Faça um fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📝 Changelog

### v1.0.0 (2024-01-15)
- ✅ Sistema base completo
- ✅ Autenticação JWT
- ✅ CRUD de produtos
- ✅ Controle de estoque
- ✅ Dashboard interativo
- ✅ Relatórios básicos
- ✅ Sistema multi-empresa

---

**Desenvolvido com ❤️ para facilitar o controle de estoque** 