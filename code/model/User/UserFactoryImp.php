<?php

namespace Kelvinho\Notes\User;

use Kelvinho\Notes\Category\CategoryFactory;
use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Timezone\Timezone;
use mysqli;

/**
 * Class UserFactoryImp
 *
 * @package Kelvinho\Notes\User
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class UserFactoryImp implements UserFactory {
    private mysqli $mysqli;
    private Session $session;
    private Timezone $timezone;
    private CategoryFactory $categoryFactory;
    /** @var User[] $cache */
    private array $cache = array();

    public function __construct(mysqli $mysqli, Session $session, Timezone $timezone, CategoryFactory $categoryFactory) {
        $this->mysqli = $mysqli;
        $this->session = $session;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
    }

    public function currentUser(): User {
        return $this->get($this->session->getCheck("user_handle"));
    }

    public function new(string $user_handle, string $password, string $name, string $timezone, string $pictureUrl = CHARACTERISTIC_DOMAIN . "/resources/images/default_profile_image.png"): User {
        $password_salt = substr(hash("sha256", rand()), 0, 5);
        $password_hash = hash("sha256", $password_salt . $password);

        $rootCategory = $this->categoryFactory->new(null, "", $user_handle);
        $this->categoryFactory->new($rootCategory, "Shared with me", $user_handle);
        $categoryId = $rootCategory->getCategoryId();

        if (!$this->mysqli->query("insert into users (user_handle, password_hash, password_salt, name, timezone, category_id, picture_url) values ('" . $this->mysqli->escape_string($user_handle) . "', '$password_hash', '$password_salt', '" . $this->mysqli->escape_string($name) . "', '$timezone', $categoryId, '" . $this->mysqli->escape_string($pictureUrl) . "')")) Logs::error($this->mysqli->error);

        return $this->get($user_handle);
    }

    public function newFederated(string $type, string $user_handle, string $name, string $timezone, string $pictureUrl = CHARACTERISTIC_DOMAIN . "/resources/images/default_profile_image.png"): User {
        $rootCategory = $this->categoryFactory->new(null, "", $user_handle);
        $this->categoryFactory->new($rootCategory, "Shared with me", $user_handle);
        $categoryId = $rootCategory->getCategoryId();

        if (!$this->mysqli->query("insert into users (user_handle, name, timezone, category_id, federated, picture_url) values ('" . $this->mysqli->escape_string($user_handle) . "', '" . $this->mysqli->escape_string($name) . "', '$timezone', $categoryId, '$type', '" . $this->mysqli->escape_string($pictureUrl) . "')")) Logs::error($this->mysqli->error);

        return $this->get($user_handle);
    }

    public function get(string $user_handle): User {
        if (isset($this->cache[$user_handle])) return $this->cache[$user_handle];
        if (!$answer = $this->mysqli->query("select user_handle, name, timezone, federated, picture_url from users where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) throw new UserNotFound();
        if (!$row = $answer->fetch_assoc()) throw new UserNotFound();
        $this->cache[$user_handle] = new User($this->mysqli, $this->timezone, $this->categoryFactory, $user_handle, $row["name"], $row["timezone"], $row["federated"], $row["picture_url"]);
        return $this->cache[$user_handle];
    }

    public function exists(string $user_handle): bool {
        if (!$answer = $this->mysqli->query("select user_handle from users where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) return false;
        if (!$row = $answer->fetch_assoc()) return false;
        return true;
    }

    public function startingWith(string $start): array {
        if (!$answer = $this->mysqli->query("select user_handle, name, timezone, federated, picture_url from users where name like '" . $this->mysqli->escape_string($start) . "%' limit 20")) throw new UserNotFound();
        /** @var User[] $users */ $users = [];
        while ($row = $answer->fetch_assoc())
            $users[] = new User($this->mysqli, $this->timezone, $this->categoryFactory, $row["user_handle"], $row["name"], $row["timezone"], $row["federated"], $row["picture_url"]);
        return $users;
    }
}
