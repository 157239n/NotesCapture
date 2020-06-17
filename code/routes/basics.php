<?php

use Kelvinho\Notes\Singleton\Header;

$router->get("", function () use ($session) {
    // try to pass the 3rd party website through again even in this scenario
    if ($session->has("remote")) {
        // remote has already expired, meaning we should redirect to our stuff
        if ($session->getCheck("remoteExpires") < time()) Header::redirect("dashboard");
        else return true;
    } else Header::redirect("dashboard");
    return false;
});

$router->get("test", function () use ($mysqli, $requestData, $session, $userFactory, $categoryFactory, $websiteFactory) {
    include(__DIR__ . "/../test.php");
});

$router->get(CHARACTERISTIC_HASH, fn() => Header::redirect("dashboard"));

$router->get(CHARACTERISTIC_HASH . "/logout", function () use ($userFactory, $authenticator) {
    session_unset();
    session_destroy();
    Header::redirect("login");
});

$router->get(CHARACTERISTIC_HASH . "/profile", function () use ($authenticator, $requestData, $timezone, $userFactory, $session) {
    include(__DIR__ . "/../view/profile.php");
});

$router->get(CHARACTERISTIC_HASH . "/login", function () use ($authenticator, $requestData, $timezone) {
    include(__DIR__ . "/../view/login.php");
});

$router->get(CHARACTERISTIC_HASH . "/dashboard", function () use ($authenticator, $session, $userFactory, $permissionFactory) {
    include(__DIR__ . "/../view/dashboard.php");
});

$router->get(CHARACTERISTIC_HASH . "/faq", function () use ($authenticator, $session, $userFactory) {
    include(__DIR__ . "/../view/faq.php");
});

$router->get(CHARACTERISTIC_HASH . SITE, function () use ($session, $websiteFactory, $commentFactory, $userFactory, $timezone, $authenticator) {
    include(__DIR__ . "/../view/site.php");
});
