<?php

use Kelvinho\Notes\Singleton\Header;

$router->get("", function () use ($session) {
    //Header::redirect("dashboard");
    // try to pass the website through again even in this scenario
    if ($session->has("remote")) {
        //Header::redirectBare("http://" . parse_url($session->getCheck("remote"))["host"]);
        return true;
    } else Header::redirect("dashboard");
    return false;
    /**/
});

$router->get("test", function () use ($mysqli, $requestData, $session, $userFactory, $categoryFactory, $websiteFactory) {
    include(__DIR__ . "/../test.php");
});

$router->get(CHARACTERISTIC_HASH, fn() => Header::redirect("dashboard"));

$router->get(CHARACTERISTIC_HASH . "/empty", function () {
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
