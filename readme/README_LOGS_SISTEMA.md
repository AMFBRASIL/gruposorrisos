# Sistema de Logs - Grupo Sorrisos

## 📋 Visão Geral

Sistema completo para visualização, filtragem e análise de logs de atividades do sistema. Permite rastrear todas as ações dos usuários, incluindo logins, criação, edição e exclusão de registros.

## 🎯 Funcionalidades

### ✨ Visualização de Logs
- **Lista completa** de todos os logs do sistema
- **Paginação** automática (50 registros por página)
- **Atualização em tempo real** das estatísticas
- **Interface moderna** seguindo o padrão visual do sistema

### 🔍 Filtros Avançados

#### Filtros Disponíveis:
1. **Busca Geral**: Pesquisa por ação, tabela, usuário, email ou IP
2. **Usuário**: Filtra logs de um usuário específico
3. **Filial**: Filtra logs de uma filial específica
4. **Ação**: Filtra por tipo de ação (LOGIN, CREATE, UPDATE, DELETE, etc.)
5. **Tabela**: Filtra por tabela afetada
6. **Período**: Filtra por intervalo de datas (início e fim)
7. **IP**: Filtra por endereço IP específico

### 📊 Estatísticas

Cards informativos exibindo:
- **Total de Logs**: Quantidade total de registros
- **Últimas 24h**: Atividades nas últimas 24 horas
- **Últimos 7 dias**: Atividades na última semana
- **Últimos 30 dias**: Atividades no último mês

### 🔎 Detalhes do Log

Modal com informações completas:
- ID do log
- Data e hora
- Usuário e email
- Filial
- Ação realizada
- Tabela afetada
- ID do registro
- Endereço IP
- User Agent (navegador)
- **Dados Anteriores** (JSON formatado)
- **Dados Novos** (JSON formatado)

### 📤 Exportação

- **Exportar CSV**: Exporta todos os logs filtrados para arquivo CSV
- **Formato**: UTF-8 com BOM, separador ponto e vírgula
- **Nome do arquivo**: `logs_sistema_AAAA-MM-DD_HHMMSS.csv`

## 📁 Arquivos Criados

### 1. Model
**Arquivo**: `models/LogSistema.php`

Responsável pela comunicação com o banco de dados:
- `findAllWithRelations()` - Busca logs com JOIN de usuários e filiais
- `findWithPagination()` - Busca com paginação
- `findByUsuario()` - Filtra por usuário
- `findByFilial()` - Filtra por filial
- `findByAcao()` - Filtra por ação
- `findByTabela()` - Filtra por tabela
- `findByPeriodo()` - Filtra por período
- `findByIp()` - Filtra por IP
- `getEstatisticas()` - Retorna estatísticas
- `getAcoesDistintas()` - Lista ações únicas
- `getTabelasDistintas()` - Lista tabelas únicas
- `registrarLog()` - Registra novo log
- `limparLogsAntigos()` - Manutenção (remove logs antigos)

### 2. Controller
**Arquivo**: `backend/controllers/ControllerLogs.php`

Gerencia a lógica de negócio dos logs (seguindo a preferência do usuário):
- `listar()` - Lista logs com todos os filtros
- `buscarPorId()` - Busca log específico
- `obterEstatisticas()` - Retorna estatísticas
- `obterAcoes()` - Lista ações para filtro
- `obterTabelas()` - Lista tabelas para filtro
- `obterUsuarios()` - Lista usuários para filtro
- `obterFiliais()` - Lista filiais para filtro
- `limparLogsAntigos()` - Remove logs com mais de X dias
- `exportarCSV()` - Gera arquivo CSV
- `contarPorAcao()` - Estatísticas por ação
- `contarPorUsuario()` - Estatísticas por usuário

### 3. API Backend
**Arquivo**: `backend/api/logs_sistema.php`

Endpoints REST para consumo via AJAX:

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `?action=list` | GET | Lista logs com filtros e paginação |
| `?action=get&id={id}` | GET | Busca log específico |
| `?action=estatisticas` | GET | Retorna estatísticas |
| `?action=acoes` | GET | Lista ações distintas |
| `?action=tabelas` | GET | Lista tabelas distintas |
| `?action=usuarios` | GET | Lista usuários |
| `?action=filiais` | GET | Lista filiais |
| `?action=export` | GET | Exporta logs para CSV |
| `?action=limpar&dias={dias}` | GET | Remove logs antigos |
| `?action=count_por_acao` | GET | Conta logs por ação |
| `?action=count_por_usuario` | GET | Conta logs por usuário |

### 4. Interface
**Arquivo**: `logs-sistema.php`

Página web com interface completa:
- Design responsivo (Bootstrap 5)
- Integração com menu do sistema
- Busca em tempo real
- Filtros avançados colapsáveis
- Paginação dinâmica
- Modal de detalhes
- Botões de ação (exportar, imprimir, atualizar)

## 🎨 Elementos Visuais

### Badges de Ação
As ações são coloridas automaticamente:
- 🔵 **LOGIN** - Azul (login/autenticação)
- 🟢 **CREATE/INSERT** - Verde (criação)
- 🟡 **UPDATE/EDIT** - Amarelo (atualização)
- 🔴 **DELETE** - Vermelho (exclusão)
- 🟣 **EXPORT** - Roxo (exportação)
- ⚪ **OUTROS** - Cinza (outras ações)

### Cards de Estatísticas
- **Total de Logs** - Preto
- **Últimas 24h** - Azul
- **Últimos 7 dias** - Amarelo
- **Últimos 30 dias** - Verde

## 🗄️ Estrutura do Banco

### Tabela: `tbl_logs_sistema`

```sql
CREATE TABLE `tbl_logs_sistema` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `id_usuario` int(11) DEFAULT NULL,
    `id_filial` int(11) DEFAULT NULL,
    `acao` varchar(100) DEFAULT NULL,
    `tabela` varchar(100) DEFAULT NULL,
    `id_registro` int(11) DEFAULT NULL,
    `dados_anteriores` TEXT DEFAULT NULL,
    `dados_novos` TEXT DEFAULT NULL,
    `ip_usuario` varchar(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `data_log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_acao` (`acao`),
    INDEX `idx_tabela` (`tabela`),
    INDEX `idx_data` (`data_log`),
    FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`),
    FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 🚀 Como Usar

### 1. Acessar a Página
```
http://localhost/sistemas/_estoquegrupoSorrisos/logs-sistema.php
```

### 2. Visualizar Logs
- Os logs são carregados automaticamente ao abrir a página
- Lista mostra os 50 registros mais recentes
- Use a paginação para navegar entre as páginas

### 3. Filtrar Logs

#### Busca Rápida:
Digite no campo de busca qualquer termo (ação, tabela, usuário, IP)

#### Filtros Avançados:
1. Clique em "Filtros Avançados"
2. Selecione os critérios desejados
3. Os resultados são atualizados automaticamente

### 4. Ver Detalhes
1. Clique no ícone 👁️ (olho) na linha do log
2. Modal abre com todos os detalhes
3. Visualize dados anteriores e novos em formato JSON

### 5. Exportar
1. Aplique os filtros desejados
2. Clique em "Exportar CSV"
3. Arquivo será baixado automaticamente

## 🔧 Integração com Outros Módulos

Para registrar logs em outras partes do sistema, use o model `LogSistema`:

```php
require_once 'models/LogSistema.php';

$pdo = Conexao::getInstance()->getPdo();
$logModel = new LogSistema($pdo);

// Registrar log
$logModel->registrarLog([
    'id_usuario' => $_SESSION['usuario_id'],
    'id_filial' => $_SESSION['usuario_filial_id'],
    'acao' => 'CREATE_MATERIAL',
    'tabela' => 'tbl_materiais',
    'id_registro' => $novoId,
    'dados_novos' => [
        'descricao' => 'Material X',
        'codigo' => '123'
    ]
]);
```

### Exemplo de Update com Dados Anteriores:

```php
// Buscar dados antes da atualização
$dadosAnteriores = $materialModel->findById($id);

// Atualizar
$materialModel->update($id, $novosDados);

// Registrar log
$logModel->registrarLog([
    'id_usuario' => $_SESSION['usuario_id'],
    'id_filial' => $_SESSION['usuario_filial_id'],
    'acao' => 'UPDATE_MATERIAL',
    'tabela' => 'tbl_materiais',
    'id_registro' => $id,
    'dados_anteriores' => $dadosAnteriores,
    'dados_novos' => $novosDados
]);
```

## 🛡️ Segurança

- ✅ Requer autenticação (`requireLogin()`)
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Sanitização de inputs
- ✅ Headers CORS configurados
- ✅ Registro automático de IP e User Agent

## 🧹 Manutenção

### Limpar Logs Antigos

Para manter o banco organizado, você pode remover logs antigos:

```php
// Via API
GET backend/api/logs_sistema.php?action=limpar&dias=90

// Via Controller
$controller = new ControllerLogs();
$resultado = $controller->limparLogsAntigos(90); // Remove logs com mais de 90 dias
```

**Nota**: O sistema não permite excluir logs com menos de 30 dias.

## 📱 Responsividade

A interface é totalmente responsiva:
- **Desktop**: Visualização completa com todos os filtros
- **Tablet**: Layout adaptado com cards empilhados
- **Mobile**: Interface otimizada para telas pequenas

## 🎯 Casos de Uso

### 1. Auditoria
- Verificar quem acessou o sistema
- Rastrear modificações em registros importantes
- Identificar ações suspeitas

### 2. Troubleshooting
- Investigar erros reportados por usuários
- Ver sequência de ações que levaram a um problema
- Verificar dados antes e depois de uma operação

### 3. Relatórios
- Exportar logs para análise externa
- Gerar relatórios de atividades
- Estatísticas de uso do sistema

### 4. Compliance
- Manter histórico de alterações
- Documentar acessos e modificações
- Atender requisitos legais de rastreabilidade

## 📊 Exemplos de Queries

### Buscar todos os logs de login:
```
Filtro Ação: LOGIN_SUCCESS
```

### Buscar alterações em um usuário específico:
```
Filtro Tabela: tbl_usuarios
Filtro Ação: UPDATE
```

### Buscar atividades de hoje:
```
Filtro Data Início: 2024-10-28
Filtro Data Fim: 2024-10-28
```

### Buscar acessos de um IP específico:
```
Filtro IP: 192.168.1.100
```

## 🔍 Troubleshooting

### Logs não aparecem?
- Verifique se a tabela `tbl_logs_sistema` existe
- Confirme que outros módulos estão registrando logs
- Verifique permissões do banco de dados

### Filtros não funcionam?
- Limpe o cache do navegador
- Verifique o console (F12) para erros JavaScript
- Confirme que a API está respondendo

### Erro ao exportar?
- Verifique permissões de escrita
- Confirme que há logs para exportar
- Tente com menos filtros

## 👨‍💻 Estrutura de Código

### Padrão MVC
- **Model**: `LogSistema.php` - Acesso aos dados
- **Controller**: `ControllerLogs.php` - Lógica de negócio
- **View**: `logs-sistema.php` - Interface

### JavaScript
- Funções assíncronas (async/await)
- Fetch API para requisições
- Manipulação dinâmica do DOM
- SweetAlert2 para alertas

### CSS
- Bootstrap 5 como base
- Classes customizadas para badges
- Estilos inline para logs
- Design system consistente

## 📝 Notas Finais

- Os logs são ordenados do mais recente para o mais antigo
- Dados JSON são formatados automaticamente
- A busca é case-insensitive
- Paginação preserva os filtros aplicados
- Exportação respeita os filtros ativos

---

**Criado em**: 28/10/2024  
**Versão**: 1.0  
**Sistema**: Grupo Sorrisos - Gestão de Estoque Odontológico





