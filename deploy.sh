#!/bin/bash
cd /var/www/Scout || exit
git fetch origin main
git reset --hard origin/main
pm2 restart all
