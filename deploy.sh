#!/bin/bash
# Legacy manual deploy script.
# Deploys are now handled automatically by GitHub Actions
# (.github/workflows/deploy.yml) on every push to main.
# This script is kept only as a manual fallback for local/VPS use.
cd /var/www/Scout || exit
git fetch origin main
git reset --hard origin/main
pm2 restart all
