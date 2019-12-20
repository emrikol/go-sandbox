#!/bin/bash
git clone git@github.com:emrikol/go-sandbox.git ~/dev-scripts/go-sandbox
cat << EOF > ~/dev-scripts/.bash_profile
# Include shared profile, should be all we need.
if [ -f ~/dev-scripts/go-sandbox/bash_profile ]; then
	. ~/dev-scripts/go-sandbox/bash_profile
fi
EOF
