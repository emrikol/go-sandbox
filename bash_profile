#!/bin/bash

# Include config if exists
test -f ~/dev-scripts/.go-sandbox.conf && source ~/dev-scripts/.go-sandbox.conf

# Move custom mu-plugins
yes | cp -af ~/dev-scripts/go-sandbox/mu-plugins/* ~/software-stacks/mu-plugins/1/

# Install rc files
yes | cp -af ~/dev-scripts/go-sandbox/.nanorc ~/.nanorc

# Create "Universal" vip-cli login
mkdir -p ~/dev-scripts/.vip-cli/
ln -s ~/dev-scripts/.vip-cli ~/.vip-cli

# Some simple aliases
alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp core is-installed --network --path=/chroot/var/www && wp site list --path=/chroot/var/www --field=url | xargs -I URL wp --path=/chroot/var/www cron event run --due-now --url=URL || wp cron event run --due-now --path=/chroot/var/www"

# Config git
git config --global user.email "$GIT_CONFIG_EMAIL"
git config --global user.name "$GIT_CONFIG_USER"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull

# A better prompt, no $P$G here!
export PS1="\
\[$(tput sgr0)\]\[\033[38;5;15m\]\[\033[48;5;124m\][S]\[$(tput sgr0)\]\[\033[38;5;124m\]\[$(tput sgr0)\]\[\033[38;5;15m\] \
\[$(tput sgr0)\]\[\033[38;5;6m\]\u\[$(tput sgr0)\]@\[\033[38;5;2m\]\h:\[$(tput sgr0)\]\[\033[38;5;3m\]\w\[$(tput sgr0)\]\[\033[38;5;15m\]\\$ \[$(tput sgr0)\]"

# Path
export PATH=/home/vipdev/dev-scripts/go-sandbox/bin/:$PATH
export LD_LIBRARY_PATH=/home/vipdev/dev-scripts/go-sandbox/bin/:$LD_LIBRARY_PATH

# I have to do this so much, let's just do it on login!
sudo reset-permissions-vip-go.sh

