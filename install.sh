#!/bin/bash
main() {
	# Include config if exists
	go_sandbox_build_config && source ~/dev-scripts/.go-sandbox.conf

	# Clone or download GH repo
	git clone git@github.com:emrikol/go-sandbox.git ~/dev-scripts/go-sandbox 2> /dev/null || git -C ~/dev-scripts/go-sandbox pull

	# Install bash_profile shim
	go_sandbox_install_bash_profile

	source ~/dev-scripts/.bash_profile
}

function go_sandbox_install_bash_profile() {
	cat << EOF > ~/dev-scripts/.bash_profile
# Include shared profile, should be all we need.
if [ -f ~/dev-scripts/go-sandbox/bash_profile ]; then
	. ~/dev-scripts/go-sandbox/bash_profile
fi
EOF
}

function go_sandbox_build_config() (
	# Helpers - https://unix.stackexchange.com/a/433816
	sed_escape() {
		sed -e 's/[]\/$*.^[]/\\&/g'
	}

	cfg_write() {
		cfg_delete ~/dev-scripts/.go-sandbox.conf "$1";
		echo "export $1=\"$2\"" >> ~/dev-scripts/.go-sandbox.conf
	}

	cfg_read() {
		test -f ~/dev-scripts/.go-sandbox.conf && grep "^$(echo "$1" | sed_escape)=" ~/dev-scripts/.go-sandbox.conf | sed "s/^$(echo "$1" | sed_escape)=//" | tail -1
	}

	cfg_delete() {
		test -f ~/dev-scripts/.go-sandbox.conf && sed -i "/^$(echo $1 | sed_escape).*$/d" ~/dev-scripts/.go-sandbox.conf
	}

	cfg_haskey() {
		test -f ~/dev-scripts/.go-sandbox.conf && grep "^$(echo "$1" | sed_escape)=" ~/dev-scripts/.go-sandbox.conf > /dev/null
	}

	if [ -z "$GIT_CONFIG_USER" ]; then
		read -r -e -p "GitHub user.name: " GIT_CONFIG_USER
		cfg_write GIT_CONFIG_USER "$GIT_CONFIG_USER"
		export GIT_CONFIG_USER
	fi

	if [ -z "$GIT_CONFIG_EMAIL" ]; then
		read -r -e -p "GitHub user.email: " GIT_CONFIG_EMAIL
		cfg_write GIT_CONFIG_EMAIL "$GIT_CONFIG_EMAIL"
		export GIT_CONFIG_EMAIL
	fi

)

test -f ~/dev-scripts/.go-sandbox.conf && source ~/dev-scripts/.go-sandbox.conf
main "$@"
test -f ~/dev-scripts/.go-sandbox.conf && source ~/dev-scripts/.go-sandbox.conf
exit
echo "Finished!  Please log out and log back in!"
sleep 10000
logout
