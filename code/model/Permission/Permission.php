<?php

namespace Kelvinho\Notes\Permission;

use Kelvinho\Notes\User\User;
use Kelvinho\Notes\User\UserFactory;
use Kelvinho\Notes\Website\Website;
use Kelvinho\Notes\Website\WebsiteFactory;
use mysqli;

class Permission {
    public const READ_AND_WRITE = 0;//, READ_ONLY = 1, NO_ACCESS = 2;

    private mysqli $mysqli;
    private WebsiteFactory $websiteFactory;
    private UserFactory $userFactory;
    private int $permissionId;
    private int $websiteId;
    private string $user_handle;
    private int $access;
    private ?Website $website = null;
    private ?User $user = null;

    /**
     * Highlight constructor.
     * @param mysqli $mysqli
     * @param WebsiteFactory $websiteFactory
     * @param UserFactory $userFactory
     * @param int $permissionId
     * @param int $websiteId
     * @param string $user_handle
     * @param int $access
     */
    public function __construct(mysqli $mysqli, WebsiteFactory $websiteFactory, UserFactory $userFactory, int $permissionId, int $websiteId, string $user_handle, int $access) {
        $this->mysqli = $mysqli;
        $this->websiteFactory = $websiteFactory;
        $this->userFactory = $userFactory;
        $this->permissionId = $permissionId;
        $this->websiteId = $websiteId;
        $this->user_handle = $user_handle;
        $this->access = $access;
    }

    public function getWebsite(): Website {
        if ($this->website === null) $this->website = $this->websiteFactory->get($this->websiteId);
        return $this->website;
    }

    public function getUser(): User {
        if ($this->user === null) $this->user = $this->userFactory->get($this->user_handle);
        return $this->user;
    }

    public function getAccess(): int {
        return $this->access;
    }

    public function delete(): void {
        $this->mysqli->query("delete from permissions where permission_id = $this->permissionId");
    }
}
