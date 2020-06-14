<?php

use Kelvinho\Notes\Singleton\Header;

$router->get("", function () use ($session) {
    // try to pass the 3rd party website through again even in this scenario
    if ($session->has("remote")) {
        if ($session->getCheck("remoteExpires") < time()) { // remote has already expired, meaning we should redirect to our stuff
            Header::redirect("dashboard");
        } else {
            //Header::redirectBare("http://" . parse_url($session->getCheck("remote"))["host"]);
            return true;
        }
    } else Header::redirect("dashboard");
    return false;
});

$router->get("test", function () use ($mysqli, $requestData, $session, $userFactory, $categoryFactory, $websiteFactory) {
    include(__DIR__ . "/../test.php");
});

$router->get(CHARACTERISTIC_HASH, fn() => Header::redirect("dashboard"));

$router->get(CHARACTERISTIC_HASH . "/logout", function () {
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

$router->get(CHARACTERISTIC_HASH . "/dashboard", function () use ($authenticator, $session, $userFactory) {
    include(__DIR__ . "/../view/dashboard.php");
});

$router->get(CHARACTERISTIC_HASH . "/faq", function () use ($authenticator, $session, $userFactory) {
    include(__DIR__ . "/../view/faq.php");
});

$router->get(CHARACTERISTIC_HASH . SITE, function () use ($session, $websiteFactory) {
    include(__DIR__ . "/../view/site.php");
});
