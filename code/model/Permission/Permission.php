<?php

namespace Kelvinho\Notes\Permission;

use mysqli;

/**
 * Class Permission. A Website owner can share it with other Users and when they share, a new Permission is created,
 * stating that a User can have access to a Website. Currently there's only read and write access, and nothing further,
 * but that may change in the future.
 *
 * @package Kelvinho\Notes\Permission
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Permission {
    public const READ_AND_WRITE = 0;//, READ_ONLY = 1, NO_ACCESS = 2;

    private mysqli $mysqli;
    private int $permissionId;
    private int $websiteId;
    private string $user_handle;
    private int $access;

    public function __construct(mysqli $mysqli, int $permissionId, int $websiteId, string $user_handle, int $access) {
        $this->mysqli = $mysqli;
        $this->permissionId = $permissionId;
        $this->websiteId = $websiteId;
        $this->user_handle = $user_handle;
        $this->access = $access;
    }

    public function getWebsiteId(): int {
        return $this->websiteId;
    }

    public function getUserHandle(): string {
        return $this->user_handle;
    }

    public function getAccess(): int {
        return $this->access;
    }

    public function delete(): void {
        $this->mysqli->query("delete from permissions where permission_id = $this->permissionId");
    }
}
