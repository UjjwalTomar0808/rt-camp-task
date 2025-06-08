#!/bin/bash

CRON_CMD="0 * * * * php $(pwd)/cron.php"

# Add the CRON job if it's not already there
(crontab -l 2>/dev/null | grep -v "$CRON_CMD"; echo "$CRON_CMD") | sort -u | crontab -

echo "✅ CRON job set up to run every hour."
