#!/usr/bin/env php
<?php
declare(strict_types=1);

set_time_limit(0);

require 'vendor/autoload.php';

define('URL', 'https://sfbay.craigslist.org/search/cto?searchNearby=2&nearbyArea=63&nearbyArea=187&nearbyArea=43&nearbyArea=373&nearbyArea=709&nearbyArea=189&nearbyArea=454&nearbyArea=285&nearbyArea=96&nearbyArea=102&nearbyArea=188&nearbyArea=92&nearbyArea=12&nearbyArea=191&nearbyArea=62&nearbyArea=710&nearbyArea=708&nearbyArea=97&nearbyArea=707&nearbyArea=208&nearbyArea=346&nearbyArea=456&min_auto_year=2000');

define('SELECTOR', '.content ul.rows li p a');


Crawler::init()
    ->url(URL)
    ->selector(SELECTOR, 0)
    ->watcherChangeContent()
    ->interval(15)
    ->enableLog()
    ->run();
