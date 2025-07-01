
set -e

echo "Deploying application ..."

# Enter maintenance mode
#(php artisan down)
    # Update codebase
    git pull origin dev
    # Exit maintenance mode
# php artisan up
# php artisan migrate --force
# php artisan passport:client --personal --name="ib" --no-interaction
# php artisan passport:keys --force
chmod -R 777 storage
# npm run build


# php artisan optimize:clear
# php artisan cache:clear
# echo "Application deployed!"
