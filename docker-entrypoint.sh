#!/bin/bash
set -e
# Render cung cấp PORT (mặc định 10000), Apache cần listen trên port này
PORT=${PORT:-10000}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground "$@"
