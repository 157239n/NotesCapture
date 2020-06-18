<?php

namespace Kelvinho\Notes\Permission;

use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\User\User;
use Kelvinho\Notes\Website\Website;
use mysqli;

/**
 * Class PermissionFactory.
 *
 * @package Kelvinho\Notes\Permission
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class PermissionFactory {
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }

    public function get(int $permissionId): Permission {
        if (!$answer = $this->mysqli->query("select website_id, user_handle, access from permissions where permission_id = $permissionId")) throw new PermissionNotFound();
        if (!$row = $answer->fetch_assoc()) throw new PermissionNotFound();
        return new Permission($this->mysqli, $permissionId, $row["website_id"], $row["user_handle"], $row["access"]);
    }

    /**
     * Get all permissions of a website.
     *
     * @param Website $website
     * @return Permission[]
     */
    public function getFromWebsite(Website $website): array {
        $websiteId = $website->getWebsiteId();
        if (!$answer = $this->mysqli->query("select permission_id, user_handle, access from permissions where website_id = $websiteId")) throw new PermissionNotFound();
        /** @var Permission[] $permissions */
        $permissions = [];
        while ($row = $answer->fetch_assoc())
            $permissions[] = new Permission($this->mysqli, $row["permission_id"], $websiteId, $row["user_handle"], (int)$row["access"]);
        return $permissions;
    }

    /**
     * Get all permissions of a user.
     *
     * @param User $user
     * @return Permission[]
     */
    public function getFromUser(User $user): array {
        $user_handle = $user->getHandle();
        if (!$answer = $this->mysqli->query("select permission_id, website_id, access from permissions where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) throw new PermissionNotFound();
        /** @var Permission[] $permissions */
        $permissions = [];
        while ($row = $answer->fetch_assoc()) $permissions[] = new Permission($this->mysqli, $row["permission_id"], $row["website_id"], $user_handle, $row["access"]);
        return $permissions;
    }

    public function getFromWebsiteAndUser(Website $website, User $user): Permission {
        $websiteId = $website->getWebsiteId();
        $user_handle = $user->getHandle();
        if (!$answer = $this->mysqli->query("select permission_id, access from permissions where website_id = $websiteId and user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) throw new PermissionNotFound();
        if (!$row = $answer->fetch_assoc()) throw new PermissionNotFound();
        return new Permission($this->mysqli, $row["permission_id"], $websiteId, $user_handle, $row["access"]);
    }

    /**
     * @param Website $website
     * @param User $user
     * @param int $access
     * @return Permission
     */
    public function new(Website $website, User $user, int $access): Permission {
        $websiteId = $website->getWebsiteId();
        $user_handle = $user->getHandle();
        if (!$this->mysqli->query("insert into permissions (website_id, user_handle, access) values ($websiteId, '" . $this->mysqli->escape_string($user_handle) . "', $access)")) Logs::error($this->mysqli->error);
        return $this->get($this->mysqli->insert_id);
    }
}
