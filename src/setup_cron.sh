#!/bin/bash
CRON_JOB="0 * * * * php $(pwd)/cron.php"
(crontab -l 2>/dev/null | grep -v "$CRON_JOB"; echo "$CRON_JOB") | crontab -
echo "CRON job set up to run every hour."
