#!/bin/bash
yes | cp -rf ~/dev-scripts/go-sandbox/mu-plugins/00-sandbox-helper.php ~/software-stacks/mu-plugins/1/

alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp site list --path=/chroot/var/www --field=url | xargs -I URL wp --path=/chroot/var/www cron event run --due-now --url=URL"
git config --global user.email "derrick.tennant@automattic.com"
git config --global user.name "Derrick Tennant"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull

export PS1="\
\[$(tput sgr0)\]\[\033[38;5;15m\]\[\033[48;5;124m\][S]\[$(tput sgr0)\]\[\033[38;5;124m\]\[$(tput sgr0)\]\[\033[38;5;15m\] \
\[$(tput sgr0)\]\[\033[38;5;6m\]\u\[$(tput sgr0)\]@\[\033[38;5;2m\]\h:\[$(tput sgr0)\]\[\033[38;5;3m\]\w\[$(tput sgr0)\]\[\033[38;5;15m\]\\$ \[$(tput sgr0)\]"