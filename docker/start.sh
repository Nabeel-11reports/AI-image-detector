#!/bin/bash
set -euo pipefail

db_name="${DB_NAME:-image_detector}"
db_user="${DB_USER:-detector_app}"
db_password="${DB_PASSWORD:-change-me-for-class}"
if [[ ! "$db_name" =~ ^[A-Za-z0-9_]+$ ]] || [[ ! "$db_user" =~ ^[A-Za-z0-9_]+$ ]]; then
  echo "DB_NAME and DB_USER may contain only letters, numbers, and underscores" >&2
  exit 1
fi

mkdir -p /run/mysqld
chown mysql:mysql /run/mysqld

if [ ! -d /var/lib/mysql/mysql ]; then
  mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null
fi

/usr/bin/mysqld_safe --datadir=/var/lib/mysql >/var/log/mysqld-bootstrap.log 2>&1 &
mysql_pid=$!

for attempt in $(seq 1 30); do
  if mariadb-admin ping --silent >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

if ! mariadb-admin ping --silent >/dev/null 2>&1; then
  echo "MariaDB did not become ready" >&2
  exit 1
fi

mariadb -uroot <<SQL
CREATE DATABASE IF NOT EXISTS ${db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${db_user}'@'localhost' IDENTIFIED BY '${db_password}';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON ${db_name}.* TO '${db_user}'@'localhost';
FLUSH PRIVILEGES;
SQL

mariadb-admin -uroot shutdown
wait "$mysql_pid" 2>/dev/null || true
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
