#!/bin/bash

# Include config if exists
test -f ~/dev-scripts/.go-sandbox.conf && source ~/dev-scripts/.go-sandbox.conf

yes | cp -rf ~/dev-scripts/go-sandbox/mu-plugins/* ~/software-stacks/mu-plugins/1/
yes | cp -rf ~/dev-scripts/go-sandbox/.nanorc ~/.nanorc

alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp core is-installed --network --path=/chroot/var/www && wp site list --path=/chroot/var/www --field=url | xargs -I URL wp --path=/chroot/var/www cron event run --due-now --url=URL || wp cron event run --due-now --path=/chroot/var/www"
git config --global user.email "$GIT_CONFIG_EMAIL"
git config --global user.name "$GIT_CONFIG_USER"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull

export PS1="\
\[$(tput sgr0)\]\[\033[38;5;15m\]\[\033[48;5;124m\][S]\[$(tput sgr0)\]\[\033[38;5;124m\]\[$(tput sgr0)\]\[\033[38;5;15m\] \
\[$(tput sgr0)\]\[\033[38;5;6m\]\u\[$(tput sgr0)\]@\[\033[38;5;2m\]\h:\[$(tput sgr0)\]\[\033[38;5;3m\]\w\[$(tput sgr0)\]\[\033[38;5;15m\]\\$ \[$(tput sgr0)\]"

# I have to do this so much, let's just do it on login!
sudo reset-permissions-vip-go.sh

