# RingCentral "Live" Dashboards

Implements pseudo live dashboards to give personnel more insights into their call groups (queues) in near realtime.

Observe the two scripts in `src/cron/` and calculate the rate limits based on your deployment.  If you have many call queues, you may need to make modifications to comply with RingCentral API Rate Limits.

## Depenendencies

Dependencies are packaged in container for easy deployment.

* PHP 7.3 
* Apache2.4
* redis-server
* RingCentral SDK

# TODO

* Show member availabilities

# Deploy

* Modify `src/config/config.php` to include the deployed callback URL and the API keys for your deployment.
* `chmod +x docker_run.sh && ./docker_run.sh`