# Sistema de Controle de Acesso e Permissões
## Grupo Sorrisos - Sistema de Gestão de Estoque

## 📋 Visão Geral

Este sistema implementa um controle de acesso robusto baseado em perfis de usuário e permissões granulares para cada página do sistema. Tudo é gerenciado através de controllers que acessam o banco de dados.

## 🏗️ Arquitetura do Sistema

### Controllers Criadas

1. **`ControllerPermissoes.php`** - Gerencia permissões e acessos
2. **`ControllerAcesso.php`** - Controla acesso às páginas e interface

### Tabelas do Banco de Dados

- **`tbl_perfis`** - Perfis de usuário (Administrador, Gerente, Operador, Visualizador)
- **`tbl_paginas`** - Páginas do sistema com categorias e ícones
- **`tbl_perfil_paginas`** - Permissões de cada perfil para cada página
- **`tbl_paginas_acesso`** - Log de acessos às páginas

## 🔐 Sistema de Permissões

### Níveis de Permissão

1. **Visualizar** - Acesso básico à página
2. **Inserir** - Pode criar novos registros
3. **Editar** - Pode modificar registros existentes
4. **Excluir** - Pode remover registros

### Perfis Padrão

- **Administrador**: Acesso total a todas as funcionalidades
- **Gerente**: Pode visualizar, inserir e editar, mas não excluir
- **Operador**: Pode visualizar e inserir, mas não editar nem excluir
- **Visualizador**: Apenas visualização

## 🚀 Como Implementar

### 1. Incluir a Controller

```php
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Inicializar controller
$controllerAcesso = new ControllerAcesso();
```

### 2. Verificar Acesso à Página

```php
// Verificar se pode acessar a página atual
if (!$controllerAcesso->verificarAcessoPagina()) {
    $controllerAcesso->redirecionarSemPermissao('Acesso negado');
}

// Registrar acesso (opcional)
$controllerAcesso->registrarAcessoPagina();
```

### 3. Verificar Permissões Específicas

```php
// Verificar permissões para ações
$podeInserir = $controllerAcesso->podeExecutarAcao('inserir');
$podeEditar = $controllerAcesso->podeExecutarAcao('editar');
$podeExcluir = $controllerAcesso->podeExecutarAcao('excluir');
```

### 4. Controlar Interface

```php
// Mostrar botões baseado nas permissões
if ($controllerAcesso->deveMostrar('botao_novo')) {
    echo '<button class="btn btn-primary">Novo</button>';
}

if ($controllerAcesso->deveMostrar('botao_editar')) {
    echo '<button class="btn btn-warning">Editar</button>';
}

if ($controllerAcesso->deveMostrar('botao_excluir')) {
    echo '<button class="btn btn-danger">Excluir</button>';
}
```

### 5. Obter Botões Permitidos

```php
// Obter todos os botões que o usuário pode usar
$botoesPermitidos = $controllerAcesso->obterBotoesPermitidos();

foreach ($botoesPermitidos as $botao) {
    // Renderizar botão
}
```

## 📊 Estrutura do Menu

O menu é renderizado dinamicamente baseado nas permissões do usuário:

```php
// Obter menu baseado nas permissões
$menuUsuario = $controllerAcesso->obterMenuUsuario();

// Renderizar menu
foreach ($menuUsuario as $categoria => $dados) {
    echo '<h6>' . $dados['nome'] . '</h6>';
    
    foreach ($dados['paginas'] as $pagina) {
        echo '<a href="' . $pagina['url_pagina'] . '">';
        echo '<i class="' . $pagina['icone'] . '"></i>';
        echo $pagina['nome_pagina'];
        echo '</a>';
    }
}
```

## 🗄️ Configuração do Banco de Dados

### 1. Executar Scripts de Configuração

```bash
# 1. Configurar páginas do sistema
php configurar_paginas.php

# 2. Configurar permissões padrão
php configurar_permissoes.php
```

### 2. Estrutura das Tabelas

#### tbl_paginas
```sql
CREATE TABLE tbl_paginas (
    id_pagina int(11) NOT NULL AUTO_INCREMENT,
    nome_pagina varchar(255) NOT NULL,
    url_pagina varchar(255) NOT NULL,
    descricao text,
    categoria varchar(100) DEFAULT NULL,
    icone varchar(100) DEFAULT NULL,
    cor varchar(50) DEFAULT 'primary',
    ordem int(11) DEFAULT 0,
    ativo tinyint(1) DEFAULT 1,
    data_criacao timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pagina)
);
```

#### tbl_perfil_paginas
```sql
CREATE TABLE tbl_perfil_paginas (
    id_perfil_pagina int(11) NOT NULL AUTO_INCREMENT,
    id_perfil int(11) NOT NULL,
    id_pagina int(11) NOT NULL,
    permissao_visualizar tinyint(1) DEFAULT 1,
    permissao_inserir tinyint(1) DEFAULT 0,
    permissao_editar tinyint(1) DEFAULT 0,
    permissao_excluir tinyint(1) DEFAULT 0,
    ativo tinyint(1) DEFAULT 1,
    data_criacao timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_perfil_pagina),
    UNIQUE KEY uk_perfil_pagina (id_perfil, id_pagina)
);
```

## 🔧 Funcionalidades da Controller

### ControllerPermissoes

- `verificarPermissao()` - Verifica permissão específica
- `obterPermissoesPerfil()` - Obtém todas as permissões de um perfil
- `salvarPermissoesPerfil()` - Salva permissões de um perfil
- `obterTodasPaginas()` - Lista todas as páginas do sistema
- `registrarAcesso()` - Registra acesso a uma página

### ControllerAcesso

- `verificarAcessoPagina()` - Verifica acesso à página atual
- `verificarEAutorizar()` - Verifica e autoriza ações
- `obterMenuUsuario()` - Obtém menu baseado nas permissões
- `podeExecutarAcao()` - Verifica se pode executar ação específica
- `deveMostrar()` - Verifica se deve mostrar elemento da interface

## 📱 Exemplo de Implementação

Veja o arquivo `exemplo_uso_controller.php` para um exemplo completo de implementação.

## 🚨 Segurança

- Todas as verificações são feitas no servidor
- Sessões são validadas a cada acesso
- Logs de acesso são registrados
- Redirecionamentos automáticos para usuários sem permissão

## 🔄 Atualizações

Para adicionar novas páginas:

1. Inserir na tabela `tbl_paginas`
2. Configurar permissões na tabela `tbl_perfil_paginas`
3. Executar script de configuração

## 📞 Suporte

Para dúvidas ou problemas:
- Verificar logs do sistema
- Consultar estrutura das tabelas
- Testar com usuários de diferentes perfis

## 📝 Notas Importantes

- Sempre verificar permissões antes de executar ações
- Usar a controller para controlar interface, não apenas backend
- Manter logs de acesso para auditoria
- Testar permissões com diferentes perfis de usuário 