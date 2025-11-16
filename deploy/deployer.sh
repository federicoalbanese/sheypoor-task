##!/bin/sh

git reset --hard

git clean -df

git checkout main || exit 1

git pull origin main || exit 1

make prod_build || exit 1

make composer_install_prod || exit 1

make reset_worker || exit 1

make clear_opcache || exit 1

echo "Deployment Done!"
