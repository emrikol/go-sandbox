#!/bin/bash

# Include config if exists
test -f ~/dev-scripts/.go-sandbox.conf && source ~/dev-scripts/.go-sandbox.conf

# Config git
git config --global user.email "$GIT_CONFIG_EMAIL"
git config --global user.name "$GIT_CONFIG_USER"
git -C /home/vipdev/dev-scripts/go-sandbox/ pull
git -C /home/vipdev/dev-scripts/go-sandbox/ submodule update --recursive --remote

# Move custom mu-plugins
yes | cp -af ~/dev-scripts/go-sandbox/mu-plugins/* ~/software-stacks/mu-plugins/1/

# Install rc files
yes | cp -af ~/dev-scripts/go-sandbox/.nanorc ~/.nanorc

# Create "Universal" vip-cli login
vipgo | ack sha 1&>2 2>/dev/null # Check for login

retVal=$?
if [ $retVal -eq 1 ]; then
	# Logged out, copy login info to home
	#echo "Logged out of VIP-CLI, copying login info"
	rm -rf ~/.vip-cli/
	cp -r ~/dev-scripts/.vip-cli/ ~/.vip-cli/
else
	# Copy login info to dev-scripts if logged in
	#echo "Logged in to VIP-CLI, storing login info"
	rm -rf ~/dev-scripts/.vip-cli/
	cp -r ~/.vip-cli/ ~/dev-scripts/.vip-cli/
fi

# Some simple aliases
alias logs="tail -F /tmp/php-errors -F /chroot/tmp/php-errors"
alias run-crons="wp core is-installed --network --path=/chroot/var/www 2> /dev/null && wp site list --path=/chroot/var/www --field=url 2> /dev/null | xargs -I URL bash -c 'echo Running cron for URL; wp --path=/chroot/var/www cron event run --due-now --url=URL 2> /dev/null' || echo Running cron for $(wp option get siteurl --path=/chroot/var/www 2> /dev/null); wp cron event run --due-now --path=/chroot/var/www 2> /dev/null"

# A better prompt, no $P$G here!
export PS1="\
\[$(tput sgr0)\]\[\033[38;5;15m\]\[\033[48;5;124m\][S]\[$(tput sgr0)\]\[\033[38;5;124m\]\[$(tput sgr0)\]\[\033[38;5;15m\] \
\[$(tput sgr0)\]\[\033[38;5;6m\]\u\[$(tput sgr0)\]@\[\033[38;5;2m\]\h:\[$(tput sgr0)\]\[\033[38;5;3m\]\w\[$(tput sgr0)\]\[\033[38;5;15m\]\\$ \[$(tput sgr0)\]"

# Path
export PATH=/home/vipdev/dev-scripts/go-sandbox/bin/:$PATH
export LD_LIBRARY_PATH=/home/vipdev/dev-scripts/go-sandbox/bin/:$LD_LIBRARY_PATH
export VIPGO_ID=$(php -r "include '/var/www/config/wp-config.php'; echo VIP_GO_APP_ID;" 2> /dev/null)

# Adds hostname badge to iTerm2
printf "\e]1337;SetBadgeFormat=%s\a" $(echo -n "$HOSTNAME" | base64)

# I have to do this so much, let's just do it on login!
sudo reset-permissions-vip-go.sh

