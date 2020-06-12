<?php

namespace Kelvinho\Notes\User;

use Kelvinho\Notes\Category\CategoryFactory;
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
    private Timezone $timezone;
    private CategoryFactory $categoryFactory;

    public function __construct(mysqli $mysqli, Timezone $timezone, CategoryFactory $categoryFactory) {
        $this->mysqli = $mysqli;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
    }

    public function new(string $user_handle, string $password, string $name, string $timezone = "GMT"): User {
        $password_salt = substr(hash("sha256", rand()), 0, 5);
        $password_hash = hash("sha256", $password_salt . $password);

        $categoryId = $this->categoryFactory->new()->getCategoryId();

        if (!$this->mysqli->query("insert into users (user_handle, password_hash, password_salt, name, timezone, category_id) values ('$user_handle', '$password_hash', '$password_salt', '" . $this->mysqli->escape_string($name) . "', '$timezone', $categoryId)")) Logs::error($this->mysqli->error);

        return $this->get($user_handle);
    }

    public function get(string $user_handle): User {
        if (!$this->exists($user_handle)) throw new UserNotFound();
        return new User($this->mysqli, $this->timezone, $this->categoryFactory, $user_handle);
    }

    public function exists(string $user_handle): bool {
        if (!$answer = $this->mysqli->query("select user_handle from users where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) return false;
        if (!$row = $answer->fetch_assoc()) return false;
        return true;
    }

    public function getAll(): array {
        $user_handles = [];
        if (!$answer = $this->mysqli->query("select user_handle from users")) return [];
        while ($row = $answer->fetch_assoc()) $user_handles[] = $row["user_handle"];
        return $user_handles;
    }
}
