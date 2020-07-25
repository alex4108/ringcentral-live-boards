#!/bin/bash

git log -1 --date=short --pretty=format:%ct > ./src/commit_date
git rev-parse --short HEAD > ./src/commit_id
eb deploy
