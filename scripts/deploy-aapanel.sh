#!/bin/bash
# aaPanel — cole INTEIRO em Git Manager → Script (alias: GitGrupoSorrisos)
# O painel inicia em /www/server/panel; o cd na 2ª linha é obrigatório.

set -e

SITE="/www/wwwroot/app.gruposorrisos.com.br"
export GIT_CONFIG_COUNT=1
export GIT_CONFIG_KEY_0="safe.directory"
export GIT_CONFIG_VALUE_0="$SITE"

echo "========== Deploy $(date) =========="
echo "PWD antes do cd: $(pwd)"

cd "$SITE" || { echo "ERRO: nao entrou em $SITE"; exit 1; }

echo "PWD no site: $(pwd)"

# Preservar config de produção (credenciais do VPS não vão no Git)
CONFIG_BAK="/root/config.php.producao.bak"
if [ -f config/config.php ]; then
    cp -f config/config.php "$CONFIG_BAK"
    echo "Backup config: $CONFIG_BAK"
fi

# Descartar alteração local só no Git para o pull não abortar
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    git restore config/config.php 2>/dev/null \
        || git checkout -- config/config.php 2>/dev/null \
        || true
fi

git pull origin main

if [ -f "$CONFIG_BAK" ]; then
    cp -f "$CONFIG_BAK" config/config.php
    echo "Config de producao restaurado."
fi

chown -R www:www "$SITE" 2>/dev/null || true

echo "Versao: $(git log -1 --oneline)"
echo "Application deployed!"
