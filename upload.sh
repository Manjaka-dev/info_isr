#!/usr/bin/env bash
set -euo pipefail

DEFAULT_TARGET="/Application/XAMPP/xamppflie/htdocs/optim/info_isr"
ALT_TARGET="/Applications/XAMPP/xamppfiles/htdocs/optim/info_isr"
TARGET_DIR="$DEFAULT_TARGET"
DRY_RUN=false
ASSUME_YES=false
UPLOADS_BACKUP=""

usage() {
  cat <<'EOF'
Usage: ./upload.sh [options]

Options:
  -t, --target <path>  Dossier cible sur le serveur (par defaut: /Application/XAMPP/xamppflie/htdocs/optim)
  -n, --dry-run        Affiche les actions sans les executer
  -y, --yes            Ne demande pas de confirmation
  -h, --help           Affiche cette aide
EOF
}

log() {
  printf '[deploy] %s\n' "$1"
}

run_cmd() {
  if $DRY_RUN; then
    printf '[dry-run] '
    printf '%q ' "$@"
    printf '\n'
    return 0
  fi
  "$@"
}

confirm() {
  if $ASSUME_YES || $DRY_RUN; then
    return 0
  fi

  read -r -p "Le dossier cible existe deja. Supprimer et remplacer ? (y/N): " answer
  [[ "$answer" =~ ^[Yy]$ ]]
}

while (($# > 0)); do
  case "$1" in
    -t|--target)
      shift
      if (($# == 0)); then
        echo "Erreur: option --target sans valeur." >&2
        exit 1
      fi
      TARGET_DIR="$1"
      ;;
    -n|--dry-run)
      DRY_RUN=true
      ;;
    -y|--yes)
      ASSUME_YES=true
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Option inconnue: $1" >&2
      usage
      exit 1
      ;;
  esac
  shift
done

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ "$TARGET_DIR" == "$DEFAULT_TARGET" && ! -e "$DEFAULT_TARGET" && -e "$ALT_TARGET" ]]; then
  log "Chemin detecte: utilisation de $ALT_TARGET"
  TARGET_DIR="$ALT_TARGET"
fi

if [[ -z "$TARGET_DIR" || "$TARGET_DIR" == "/" || "$TARGET_DIR" == "/Applications" || "$TARGET_DIR" == "/Application" ]]; then
  echo "Erreur: chemin cible dangereux ou invalide: '$TARGET_DIR'" >&2
  exit 1
fi

if [[ "$SOURCE_DIR" == "$TARGET_DIR" ]]; then
  echo "Erreur: la source et la cible sont identiques." >&2
  exit 1
fi

PARENT_DIR="$(dirname "$TARGET_DIR")"
if ! $DRY_RUN && [[ ! -w "$PARENT_DIR" ]] && [[ $EUID -ne 0 ]]; then
  echo "Erreur: pas de permission d'ecriture dans '$PARENT_DIR'." >&2
  echo "Relance avec sudo: sudo ./upload.sh" >&2
  exit 1
fi

if [[ -d "$TARGET_DIR" ]]; then
  log "Ancienne version detectee: $TARGET_DIR"
  if ! confirm; then
    log "Operation annulee."
    exit 0
  fi
  if [[ -d "$TARGET_DIR/uploads" ]]; then
    UPLOADS_BACKUP="/tmp/info_isr_uploads_backup_$$"
    run_cmd mv "$TARGET_DIR/uploads" "$UPLOADS_BACKUP"
  fi
  run_cmd rm -rf "$TARGET_DIR"
else
  log "Aucune version existante detectee."
fi

run_cmd mkdir -p "$TARGET_DIR"
run_cmd cp -R "$SOURCE_DIR"/. "$TARGET_DIR"/

if [[ -n "$UPLOADS_BACKUP" ]]; then
  run_cmd rm -rf "$TARGET_DIR/uploads"
  run_cmd mv "$UPLOADS_BACKUP" "$TARGET_DIR/uploads"
fi

run_cmd mkdir -p "$TARGET_DIR/uploads"
run_cmd chmod 775 "$TARGET_DIR/uploads"

if ! $DRY_RUN && [[ $EUID -eq 0 ]] && id -u daemon >/dev/null 2>&1; then
  run_cmd chown -R daemon:daemon "$TARGET_DIR/uploads"
fi

if ! $DRY_RUN && [[ ! -f "$TARGET_DIR/index.php" ]]; then
  echo "Erreur: verification post-deploiement echouee (index.php introuvable)." >&2
  exit 1
fi

log "Deploiement termine vers: $TARGET_DIR"
