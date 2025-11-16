#!/bin/sh

set -e

# Check if incomming command contains flags.
if [ "${1#-}" != "$1" ]; then
    set -- /usr/bin/supervisord "$@"
fi

# Replace environment variables if `ENV_SUBSTITUTION_ENABLE=true`
#if [[ -n "$ENV_SUBSTITUTION_ENABLE" ]] && [[ "$ENV_SUBSTITUTION_ENABLE" = "true" ]]; then
#    /init/envsubst.sh
#fi

/init/envsubst.sh
/init/cron.sh

exec "$@"
