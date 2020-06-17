<?php

use Kelvinho\Notes\Singleton\Header;

$router->get("favicon.ico", function () {
    Header::notFound();
});

$router->get(CHARACTERISTIC_HASH . "/resources/empty", function () {
    Header::ok();
});

$router->get(CHARACTERISTIC_HASH . "/resources/css/*", function () use ($requestData) {
    readfile(__DIR__ . "/../resources/css/" . $requestData->getExplodedPath()[3]);
    \header("Content-Type: text/css");
    @readfile(__DIR__ . "/../resources/css/" . $requestData->getExplodedPath()[3]);
});

$router->get(CHARACTERISTIC_HASH . "/resources/js/*", function () use ($requestData, $userFactory) {
    \header("Content-Type: text/javascript");
    @include(__DIR__ . "/../resources/js/" . $requestData->getExplodedPath()[3]);
});

$router->get(CHARACTERISTIC_HASH . "/resources/images/*", function () use ($requestData) {
    $path = __DIR__ . "/../resources/images/" . $requestData->getExplodedPath()[3];
    \header("Content-Type: " . mime_content_type($path));
    readfile($path);
});

$router->getPost(CHARACTERISTIC_HASH . "/ctrls/*", function () use ($requestData, $authenticator, $session, $timezone, $userFactory, $categoryFactory, $websiteFactory, $highlightFactory, $commentFactory, $permissionFactory) {
    include(__DIR__ . "/../controller/" . $requestData->getExplodedPath()[2] . ".php");
    Header::ok();
});

$router->get("setup", function() {
});

$router->get("clear", function () use ($mysqli) {
    /** @noinspection SqlWithoutWhere */
    $mysqli->query("delete from categories");
    /** @noinspection SqlWithoutWhere */
    $mysqli->query("delete from highlights");
    /** @noinspection SqlWithoutWhere */
    $mysqli->query("delete from users");
    /** @noinspection SqlWithoutWhere */
    $mysqli->query("delete from websites");
});
