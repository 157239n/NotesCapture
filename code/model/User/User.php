<?php

namespace Kelvinho\Notes\User;

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Category\CategoryFactory;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Timezone\Timezone;
use mysqli;

/**
 * Class User.
 *
 * Represents a user. The representation of this will be stored in table users only. No data is stored on disk.
 *
 * @package Kelvinho\Notes\User
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class User {
    private string $user_handle;
    private string $name;
    private string $timezone;
    private string $federated;
    private string $pictureUrl;
    private Timezone $timezoneObject;
    private mysqli $mysqli;
    private CategoryFactory $categoryFactory;
    private ?Category $rootCategory = null;

    /**
     * User constructor.
     * @param mysqli $mysqli
     * @param Timezone $timezoneObject
     * @param CategoryFactory $categoryFactory
     * @param string $user_handle
     * @param string $name
     * @param string $timezone
     * @param string $federated
     * @param string $pictureUrl
     * @internal
     */
    public function __construct(mysqli $mysqli, Timezone $timezoneObject, CategoryFactory $categoryFactory, string $user_handle, string $name, string $timezone, string $federated, string $pictureUrl) {
        $this->mysqli = $mysqli;
        $this->timezoneObject = $timezoneObject;
        $this->categoryFactory = $categoryFactory;
        $this->user_handle = $user_handle;
        $this->name = $name;
        $this->timezone = $timezone;
        $this->federated = $federated;
        $this->pictureUrl = $pictureUrl;
    }

    public function getRootCategory(): Category {
        if ($this->rootCategory == null) $this->rootCategory = $this->categoryFactory->getRoot();
        return $this->rootCategory;
    }

    public function getPictureUrl(): string {
        return $this->pictureUrl;
    }

    public function getTimezone(): string {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): void {
        $this->timezone = $timezone;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getHandle(): string {
        return $this->user_handle;
    }

    public function saveState(): void {
        if (!$this->mysqli->query("update users set name = '" . $this->mysqli->escape_string($this->name) . "', timezone = '$this->timezone' where user_handle = '" . $this->mysqli->escape_string($this->user_handle) . "'")) Logs::mysql($this->mysqli);
    }
}
