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
crontab -l > /dev/null 2> /dev/null || crontab /tmp/void
rm -f /tmp/void

# Define (un)?register_crontask
register_crontask () {
    if [ -z "$2" ]
    then
	php -r "require_once('../inc/cron.php'); var_dump (register_crontask('$1'));"
    else
	php -r "require_once('../inc/cron.php'); var_dump (register_crontask('$1', '$2'));"
    fi
}
unregister_crontask () {
    php -r "require_once('../inc/cron.php'); var_dump (unregister_crontask('$1'));"
}

crontask1='@reboot  echo Luke, I am your father'
crontask2='0 */2 * * *  echo Am I dead ? Am I alive ?'
crontask3='I am gonna destroy your crontab hehehe…'

crontab -l > tmp/state1

register_crontask "$crontask1" 'FREEDER UNIT TEST'
crontab -l > tmp/state2
diff -u tmp/state1 tmp/state2 | grep -v '@@\|---\|+++'

register_crontask "$crontask2" 'FREEDER UNIT TEST'
crontab -l > tmp/state3
diff -u tmp/state2 tmp/state3 | grep -v '@@\|---\|+++'

register_crontask "$crontask2" 'FREEDER UNIT TEST, AGAIN'
crontab -l > tmp/state4
diff -u tmp/state3 tmp/state4 | grep -v '@@\|---\|+++'

register_crontask "$crontask3" 'FREEDER UNIT TEST'
crontab -l > tmp/state5
diff -u tmp/state4 tmp/state5 | grep -v '@@\|---\|+++'

unregister_crontask "$crontask1"
crontab -l > tmp/state6
diff -u tmp/state5 tmp/state6 | grep -v '@@\|---\|+++'

unregister_crontask 'FREEDER UNIT TEST, AGAIN'
crontab -l > tmp/state7
diff -u tmp/state6 tmp/state7 | grep -v '@@\|---\|+++'

unregister_crontask 'FREEDER UNIT TEST, AGAIN'
crontab -l > tmp/state8
diff -u tmp/state7 tmp/state8 | grep -v '@@\|---\|+++'

unregister_crontask 'FREEDER UNIT TEST'
crontab -l > tmp/state9
diff -u tmp/state8 tmp/state9 | grep -v '@@\|---\|+++'
