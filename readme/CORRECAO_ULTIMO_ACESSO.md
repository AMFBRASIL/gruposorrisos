# Correção do Campo "Último Acesso" na Tela de Usuários

## 🔍 Problema Identificado

O campo "Último Acesso" não estava aparecendo na tela de usuários (`usuarios.php`) por dois motivos principais:

1. **Login não atualizava o campo**: O arquivo `backend/api/auth.php` não estava usando o model `Usuario` que contém o método responsável por atualizar o campo `ultimo_acesso` no banco de dados.

2. **Queries SQL incompletas**: As queries SQL no model `Usuario.php` estavam usando `u.*` que poderia causar conflitos com campos de outras tabelas nos JOINs, especialmente com `f.id_filial`.

## ✅ Correções Implementadas

### 1. Atualização do Model Usuario.php

**Arquivo**: `models/Usuario.php`

**O que foi feito**:
- Substituído `SELECT u.*` por `SELECT` com lista explícita de campos
- Adicionado explicitamente o campo `u.ultimo_acesso` em todas as queries
- Alterado `f.id_filial` para `f.id_filial as filial_id` para evitar conflitos
- Removido o filtro hardcoded `WHERE u.ativo = 1` no método `findWithPagination` para permitir filtros flexíveis

**Métodos atualizados**:
- `autenticar()`
- `findByEmail()`
- `findAllWithRelations()`
- `findByIdWithRelations()`
- `findWithPagination()`

**Exemplo da correção**:
```php
// ANTES
$sql = "SELECT u.*, p.nome_perfil, f.nome_filial, f.id_filial FROM...";

// DEPOIS
$sql = "SELECT u.id_usuario,
               u.nome_completo,
               u.email,
               u.cpf,
               u.telefone,
               u.senha,
               u.id_perfil,
               u.id_filial,
               u.ativo,
               u.ultimo_acesso,  -- Campo adicionado explicitamente
               u.data_criacao,
               u.data_atualizacao,
               p.nome_perfil,
               f.nome_filial,
               f.id_filial as filial_id FROM...";
```

### 2. Atualização do Auth.php

**Arquivo**: `backend/api/auth.php`

**O que foi feito**:
- Adicionado `require_once '../../models/Usuario.php'`
- Substituída a query direta por chamada ao método `$usuarioModel->autenticar()`
- O método `autenticar()` já atualiza automaticamente o campo `ultimo_acesso` através do método privado `atualizarUltimoAcesso()`

**Código anterior**:
```php
// Fazia query direta sem atualizar ultimo_acesso
$stmt = $pdo->prepare("SELECT u.*, p.nome_perfil, f.nome_filial FROM...");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['senha'])) {
    // Login bem-sucedido
}
```

**Código corrigido**:
```php
// Usa o model que atualiza automaticamente o ultimo_acesso
$user = $usuarioModel->autenticar($email, $password);

if ($user) {
    // Login bem-sucedido
    // O campo ultimo_acesso foi atualizado automaticamente
}
```

### 3. Estilo CSS para Tabela de Usuários

**Arquivo**: `assets/css/usuarios.css`

**O que foi feito**:
- Adicionados estilos específicos para a classe `.table-usuarios`
- Melhorada a visualização da tabela com hover effects
- Garantido espaçamento adequado para o campo "Último Acesso"

```css
.table-usuarios th, .table-usuarios td { vertical-align: middle; }
.table-usuarios th {
    color: #222;
    font-weight: 600;
    font-size: 1.05rem;
    background: #f7f9fb;
    border-bottom: 2px solid #e5e7eb;
    padding: 1rem;
}
.table-usuarios td { font-size: 1.05rem; padding: 1rem; }
.table-usuarios tbody tr { border-bottom: 1px solid #f1f3f5; }
.table-usuarios tbody tr:hover { background: #f8f9fa; }
```

## 📁 Arquivos Criados

### 1. Script de Migração SQL
**Arquivo**: `database/add_ultimo_acesso.sql`

Script SQL para adicionar o campo `ultimo_acesso` caso não exista no banco de dados.

### 2. Script PHP de Atualização da Estrutura
**Arquivo**: `atualizar_campo_ultimo_acesso.php`

Interface web para executar a migração e verificar se o campo existe.

### 3. Script PHP para Atualizar Registros Existentes
**Arquivo**: `scripts/atualizar_ultimo_acesso_usuarios.php`

Script que atualiza os usuários existentes que não têm `ultimo_acesso` registrado, definindo a data/hora atual.

## 🚀 Como Aplicar as Correções

### Passo 1: Verificar a Estrutura do Banco
```bash
# Acesse via navegador:
http://localhost/sistemas/_estoquegrupoSorrisos/atualizar_campo_ultimo_acesso.php
```

Este script irá:
- Verificar se o campo `ultimo_acesso` existe
- Adicionar o campo caso não exista
- Criar índice para otimizar consultas

### Passo 2: Atualizar Registros Existentes
```bash
# Acesse via navegador:
http://localhost/sistemas/_estoquegrupoSorrisos/scripts/atualizar_ultimo_acesso_usuarios.php
```

Este script irá:
- Atualizar todos os usuários sem `ultimo_acesso` com a data/hora atual
- Mostrar estatísticas dos registros atualizados
- Listar os últimos 10 usuários atualizados

### Passo 3: Testar o Login
1. Faça logout do sistema
2. Faça login novamente
3. O campo `ultimo_acesso` será atualizado automaticamente no banco

### Passo 4: Verificar na Tela de Usuários
1. Acesse: `http://localhost/sistemas/_estoquegrupoSorrisos/usuarios.php`
2. A coluna "Último Acesso" agora deve exibir as datas
3. Formato: `DD/MM/AAAA HH:MM`

## 🔧 Funcionamento Técnico

### Fluxo de Atualização do Último Acesso

```
1. Usuário faz login
   ↓
2. backend/api/auth.php recebe credenciais
   ↓
3. Chama $usuarioModel->autenticar($email, $senha)
   ↓
4. Method autenticar() verifica senha
   ↓
5. Se válida, chama atualizarUltimoAcesso($id_usuario)
   ↓
6. Executa: UPDATE tbl_usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id_usuario = ?
   ↓
7. Retorna dados do usuário (sem senha)
   ↓
8. Sessão é criada e usuário é redirecionado
```

### Exibição na Tela de Usuários

```javascript
// JavaScript em usuarios.php (linhas 414-417)
const ultimoAcesso = usuario.ultimo_acesso ? 
    new Date(usuario.ultimo_acesso).toLocaleDateString('pt-BR') + ' ' + 
    new Date(usuario.ultimo_acesso).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}) : 
    '-';
```

Se `ultimo_acesso` for NULL, exibe `-`

## 📊 Estrutura do Campo no Banco

```sql
CREATE TABLE `tbl_usuarios` (
    ...
    `ultimo_acesso` timestamp NULL,
    ...
);

-- Índice para otimizar consultas de estatísticas
CREATE INDEX idx_ultimo_acesso ON tbl_usuarios(ultimo_acesso);
```

## ✨ Melhorias Adicionais

### Estatísticas Atualizadas
O método `getEstatisticas()` no model Usuario já utiliza o campo `ultimo_acesso`:
- Total de usuários
- Usuários ativos
- Usuários ativos nos últimos 7 dias
- Usuários ativos nos últimos 30 dias

### Logs de Login
O sistema agora também registra o ID do usuário nos logs de tentativa de login, facilitando auditoria.

## 🎯 Resultado Final

Após aplicar todas as correções:

✅ Campo "Último Acesso" aparece na tela de usuários  
✅ Data é atualizada automaticamente a cada login  
✅ Formato de data em português (DD/MM/AAAA HH:MM)  
✅ Estatísticas de usuários ativos funcionando corretamente  
✅ Filtros de usuários funcionando (inclusive usuários inativos)  
✅ Performance otimizada com índice no banco  

## 📝 Notas Importantes

1. **Backup**: Sempre faça backup do banco antes de executar os scripts
2. **Permissões**: Certifique-se de que o usuário do banco tem permissões ALTER TABLE
3. **Cache**: Limpe o cache do navegador após as atualizações
4. **Teste**: Teste em ambiente de desenvolvimento antes de aplicar em produção

## 🐛 Troubleshooting

### Campo ainda não aparece?
- Limpe o cache do navegador (Ctrl + Shift + Del)
- Verifique se executou os scripts de migração
- Confirme que fez logout e login novamente
- Verifique o console do navegador (F12) para erros JavaScript

### Data não atualiza no login?
- Verifique se o arquivo `backend/api/auth.php` foi atualizado corretamente
- Confirme que o model `Usuario.php` está sendo importado
- Verifique logs de erro do PHP

### Erro de permissão no banco?
- Execute: `GRANT ALTER ON database_name.* TO 'user'@'localhost';`
- Ou execute os scripts com um usuário que tenha permissões adequadas

## 👨‍💻 Autor

Sistema de Estoque - Grupo Sorrisos  
Data da correção: 28/10/2024

---

**Arquivos modificados**:
- ✏️ `models/Usuario.php`
- ✏️ `backend/api/auth.php`
- ✏️ `assets/css/usuarios.css`

**Arquivos criados**:
- ➕ `database/add_ultimo_acesso.sql`
- ➕ `atualizar_campo_ultimo_acesso.php`
- ➕ `scripts/atualizar_ultimo_acesso_usuarios.php`
- ➕ `readme/CORRECAO_ULTIMO_ACESSO.md`

