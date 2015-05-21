APP_ENV=local
APP_DEBUG=true
APP_KEY={{ $key }}

DB_HOST={{ $host_name }}
DB_DATABASE={{ $db_name }}
DB_USERNAME={{ $db_user }}
DB_PASSWORD={{ $db_password }}

CACHE_DRIVER=file
SESSION_DRIVER=file