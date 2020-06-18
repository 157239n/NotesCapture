<?php

define("MAINTENANCE", false); // whether we're in maintenance mode. If true, then displays nothing upon a request

define("GITHUB_PAGE", "https://github.com/157239n/NotesCapture");
define("LOG_FILE", "/var/log/apache2/error.log");
define("APP_LOCATION", __DIR__);
define("GOOGLE_CLIENT_ID", getenv("GOOGLE_CLIENT_ID"));

// just a random string. This serves as the beginning of every url of the application, so as to not collide with any other websites
define("CHARACTERISTIC_HASH", "4b6fb40fb27456b652bfeb82266ba23451e5fd28fdbdf5758f1fd5c1229e2fe0");

// domain/path related
define("DOMAIN", getenv("DOMAIN"));
define("CHARACTERISTIC_DOMAIN", DOMAIN . "/" . CHARACTERISTIC_HASH);
define("DOMAIN_CONTROLLER", CHARACTERISTIC_DOMAIN . "/ctrls");
define("DOMAIN_RESOURCES", CHARACTERISTIC_DOMAIN . "/resources");
define("SITE", "/9/8/7/6/5/4/3/2/1/0/9/8/7/6/5/4/3/2/1/site"); // using numbers like these so that we can trace back 3rd party's "../../abc.txt" urls
define("PADDING_NUMBER", 20);
define("REMOTE_EXPIRES_DURATION", 10); // used in setRemote controller

// inner workings related
define("USER_NAME_LENGTH_LIMIT", 1000);
define("USER_HANDLE_LENGTH_LIMIT", 100);
