#!/bin/bash
cd /usr/share/nginx/html
php vendor/robmorgan/phinx/bin/phinx migrate
