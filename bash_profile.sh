#!/bin/bash
alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp site list --path=/chroot/var/www --field=url | xargs -I URL wp --path=/chroot/var/www cron event run --due-now --url=URL"
git config --global user.email "derrick.tennant@automattic.com"
git config --global user.name "Derrick Tennant"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull


tput csr 1 "$((LINES-1))"
export PS1="\
$(tput sc)$(tput home)\[\033[38;5;124m\]▐\[$(tput sgr0)\]\[\033[38;5;15m\]\[\033[48;5;124m\][SANDBOXED: \h]\[$(tput sgr0)\]\[\033[38;5;124m\]\[\033[48;5;-1m\]▌\[$(tput sgr0)\]\[\033[38;5;15m\]\
$(tput rc)\[$(tput sgr0)\]\[\033[38;5;6m\]\u\[$(tput sgr0)\]\[\033[38;5;3m\]:\[$(tput sgr0)\]\[\033[38;5;2m\]\w\[$(tput sgr0)\]\[\033[38;5;15m\]\\$ \[$(tput sgr0)\]"