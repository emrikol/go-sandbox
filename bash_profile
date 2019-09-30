alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp site list --path=/chroot/var/www --field=url | xargs -I URL wp --path=/chroot/var/www cron event run --due-now --url=URL"
git config --global user.email "derrick.tennant@automattic.com"
git config --global user.name "Derrick Tennant"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull

