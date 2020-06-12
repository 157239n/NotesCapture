<?php

define("GITHUB_PAGE", "https://github.com/157239n/NotesCapture");

// logging and persistent states
define("LOG_FILE", "/var/log/apache2/error.log");
define("STRAY_VIRUS_LOG_FILE", "/var/log/apache2/strayViruses.log");
define("DATA_FILE", "/data"); // where all of the persistent state outside of the database lives

define("APP_LOCATION", __DIR__);

define("CHARACTERISTIC_HASH", "4b6fb40fb27456b652bfeb82266ba23451e5fd28fdbdf5758f1fd5c1229e2fe0");

// domain related
define("DOMAIN", getenv("DOMAIN"));
define("ALT_DOMAIN", getenv("ALT_DOMAIN")); // this is to avoid "virus. ..." displayed to the target user when social engineering
define("ALT_SECURE_DOMAIN", getenv("ALT_SECURE_DOMAIN")); // this is to avoid "virus. ..." displayed to the target user when social engineering
define("ALT_DOMAIN_SHORT", str_replace("http://", "", ALT_DOMAIN));
define("CHARACTERISTIC_DOMAIN", DOMAIN . "/" . CHARACTERISTIC_HASH);
define("DOMAIN_CONTROLLER", CHARACTERISTIC_DOMAIN . "/ctrls");
define("DOMAIN_RESOURCES", CHARACTERISTIC_DOMAIN . "/resources");

// inner workings related
define("NAME_LENGTH_LIMIT", 50);

define("MAINTENANCE", false);
