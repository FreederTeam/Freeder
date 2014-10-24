#!/bin/bash

##
# Copyright (c) 2014 Freeder
# Released under a MIT License.
# See the file LICENSE at the root of this repo for copying permission.

##
# Unit test
# inc/cron.php - register_crontask
# inc/cron.php - unregister_crontask

# Make sure the crontab exists
touch /tmp/void
crontab -l 2> /dev/null || crontab /tmp/void

# On demande l'enregistrement d'une crontask
php -r "require_once('../inc/cron.php'); register_crontask('@reboot cd / && echo Je suis ton père.');"
