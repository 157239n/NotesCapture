#!/bin/bash

chown -R www-data:www-data /var/log/apache2

/startup/runPhpFpm.sh
cat /etc/environment | /startup/setupFpmEnv.sh

tail -f /dev/null
