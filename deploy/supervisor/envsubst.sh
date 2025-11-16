#!/bin/sh

set -e

UID="$UID"

envsubst < "/init/tmpl/cron.tmpl" > "/var/spool/cron/crontabs/root"
envsubst < "/init/tmpl/horizon.tmpl" > "/etc/supervisor/conf.d/horizon.conf"
