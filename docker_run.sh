#!/bin/bash
docker build --tag rc-board:1.0 .
docker run -p 8080:80 -it rc-board:1.0 /bin/bash