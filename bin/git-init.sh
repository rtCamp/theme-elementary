#!/bin/bash

if [ -d .git ]; then
	exit 0
fi

{
	git init
	git add --all .
	git commit -m "Initialize project using rtCamp/theme-elementary"
} &> /dev/null
