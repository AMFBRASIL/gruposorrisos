#!/bin/bash
# Deploy automático — Grupo Sorrisos (aaPanel Git Manager)
# Cole em: Site → Git Manager → Script → Add script
# Alias sugerido: Deploy produção

set -e

SITE_PATH="/www/wwwroot/app.gruposorrisos.com.br"
BRANCH="main"
REMOTE="origin"
WEB_USER="www"
WEB_GROUP="www"

echo "========== Deploy START $(date '+%Y-%m-%d %H:%M:%S') =========="

if [ ! -d "$SITE_PATH/.git" ]; then
  echo "ERRO: repositório Git não encontrado em $SITE_PATH"
  exit 1
fi

cd "$SITE_PATH"

# Root no painel + arquivos www → evita "dubious ownership"
git config --global --add safe.directory "$SITE_PATH" 2>/dev/null || true
# Ignora mudança só de permissão (chmod) no servidor
git config core.fileMode false

# Backup do config de produção (se existir alteração local)
CONFIG_STASHED=0
if [ -n "$(git status --porcelain config/config.php 2>/dev/null)" ]; then
  echo "→ Preservando config/config.php do servidor..."
  cp -f config/config.php "config/config.php.bak.deploy" 2>/dev/null || true
  git stash push -m "deploy-config-$(date +%Y%m%d%H%M%S)" -- config/config.php || true
  CONFIG_STASHED=1
fi

echo "→ git fetch $REMOTE $BRANCH"
git fetch "$REMOTE" "$BRANCH"

echo "→ Atualizando código (fast-forward)..."
git merge --ff-only "$REMOTE/$BRANCH" || git pull "$REMOTE" "$BRANCH"

# Restaurar config de produção
if [ "$CONFIG_STASHED" = "1" ]; then
  echo "→ Restaurando config/config.php..."
  if ! git stash pop 2>/dev/null; then
    if [ -f config/config.php.bak.deploy ]; then
      cp -f config/config.php.bak.deploy config/config.php
      echo "   (restaurado do backup .bak.deploy)"
    fi
  fi
fi

echo "→ Permissões para PHP/Nginx ($WEB_USER)..."
chown -R "$WEB_USER:$WEB_GROUP" "$SITE_PATH"
find "$SITE_PATH" -type d -exec chmod 755 {} \;
find "$SITE_PATH" -type f -exec chmod 644 {} \;

for dir in uploads logs cache tmp; do
  if [ -d "$SITE_PATH/$dir" ]; then
    chown -R "$WEB_USER:$WEB_GROUP" "$SITE_PATH/$dir"
    find "$SITE_PATH/$dir" -type d -exec chmod 775 {} \;
    find "$SITE_PATH/$dir" -type f -exec chmod 644 {} \;
  fi
done

echo "→ Versão em produção: $(git log -1 --oneline)"
echo "========== Deploy OK $(date '+%Y-%m-%d %H:%M:%S') =========="
echo "Application deployed!"
