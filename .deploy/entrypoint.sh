#!/bin/sh

echo "🎬 entrypoint.sh: [$(whoami)] [PHP $(php -r 'echo phpversion();')]"

composer dump-autoload --no-interaction --no-dev --optimize

echo "🎬 artisan commands"

# link storage
php artisan storage:link

# php artisan config:clear

# php artisan queue:restart

# php artisan queue:listen --queue=SendEmailJob

# php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider" --tag="config"

# php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"

# 💡 Group into a custom command e.g. php artisan app:on-deploy

php artisan migrate --no-interaction --force

echo "🎬 start supervisord"

supervisord -c $LARAVEL_PATH/.deploy/config/supervisor.conf
