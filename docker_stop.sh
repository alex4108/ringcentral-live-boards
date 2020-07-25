#!/bin/bash

docker stop $(docker ps | grep rc-board | awk '{print $1}')