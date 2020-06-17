<?php

use Kelvinho\Notes\Auth\Authenticator;
use Kelvinho\Notes\Auth\AuthenticatorImp;
use Kelvinho\Notes\Category\CategoryFactory;
use Kelvinho\Notes\Comment\CommentFactory;
use Kelvinho\Notes\Core\Autoload;
use Kelvinho\Notes\Highlight\HighlightFactory;
use Kelvinho\Notes\Network\FilterList\BlacklistFactory;
use Kelvinho\Notes\Network\FilterList\WhitelistFactory;
use Kelvinho\Notes\Network\RequestData;
use Kelvinho\Notes\Network\Router;
use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Permission\PermissionFactory;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Timezone\Timezone;
use Kelvinho\Notes\User\UserFactory;
use Kelvinho\Notes\User\UserFactoryImp;
use Kelvinho\Notes\Website\WebsiteFactory;

require_once(__DIR__ . "/vendor/autoload.php");

// loads constants and dumb functions
require_once(__DIR__ . "/consts.php");
if (MAINTENANCE) exit();
require_once(__DIR__ . "/basics.php");

// load and register autoloader
require_once(__DIR__ . "/model/Core/Autoload.php");
$autoload = new Autoload(__DIR__ . "/model");
$autoload->register();

//\Kelvinho\Notes\Singleton\Header::forbidden();

// create every other injectable singleton objects
session_start();
$session = new Session();

$mysqli = new mysqli(getenv("MYSQL_HOST"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"), getenv("MYSQL_DATABASE"));
if ($mysqli->connect_errno) Logs::error("Mysql failed. Info: $mysqli->connect_error");

$requestData = new RequestData();
$whitelistFactory = new WhitelistFactory();
$blacklistFactory = new BlacklistFactory();

$timezone = new Timezone();

$categoryFactory = new CategoryFactory($mysqli, $session);
$commentFactory = new CommentFactory($mysqli);
$highlightFactory = new HighlightFactory($mysqli, $session, $commentFactory);
$websiteFactory = new WebsiteFactory($mysqli, $highlightFactory, $session);
$categoryFactory->addContext($websiteFactory);

/** @var UserFactory $userFactory */
$userFactory = new UserFactoryImp($mysqli, $session, $timezone, $categoryFactory);

$permissionFactory = new PermissionFactory($mysqli, $websiteFactory, $userFactory);

/** @var Authenticator $authenticator */
$authenticator = new AuthenticatorImp($session, $mysqli, $permissionFactory, $userFactory);

// adding missing dependencies
$websiteFactory->addContext($permissionFactory);

// create a router, add routes and run
$router = new Router($requestData, $session);
foreach (glob(__DIR__ . "/routes/*.php") as $file) require_once($file);
$router->run();

$mysqli->close();
