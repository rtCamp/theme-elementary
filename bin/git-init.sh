#!/bin/bash

# check if git is initialized already
if [ -d .git ]; then
	echo "Git is already initialized"
	exit 0
fi

git init
git add --all .
git commit -m "Initialize project using rtCamp/theme-elementary"
