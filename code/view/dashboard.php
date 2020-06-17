<?php

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Permission\Permission;
use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;
use Kelvinho\Notes\Website\Website;
use function Kelvinho\Notes\map;

if (!$authenticator->authenticated()) Header::redirect("login");

/**
 * @param Category $category
 * @param Website[] $sharedWebsites
 */
function displayCategories(Category $category, array $sharedWebsites): void {
    $categoryId = $category->getCategoryId();
    $shared = ($category->getCategoryId() === $category->getRootCategory()->getCategoryId() + 1);
    $websites = $shared ? $sharedWebsites : $category->getWebsites();
    $childCategories = $category->getChildren(); ?>
    <li>
        <div id="category<?php echo $categoryId; ?>"
             class="category w3-light-grey w3-hover-grey w3-card"
             onclick="toggleWebsitesVisibility(<?php echo $categoryId; ?>)"
             style="clear: both;position: relative;padding-left: 15px;">
            <span style="position: absolute;top: 50%;transform: translateY(-50%);"><?php echo $category->getName(); ?>
                <span style="color: #616161;">, sites: <?php echo count($websites); ?></span></span>
            <?php if (!$category->isRoot() && !$shared) { ?>
                <button class="w3-btn w3-teal w3-round-xxlarge" style="float:right;margin-left: 10px"
                        onclick="deleteCategory(<?php echo $categoryId; ?>)"
                        onmouseenter="tooltip.activate('Delete')"
                        onmouseleave="tooltip.deactivate()"><span class="material-icons">delete</span>
                </button>
            <?php }
            if (!$shared) { ?>
                <button class="w3-btn w3-light-green w3-round-xxlarge" style="float:right;margin-left: 10px"
                        onclick="openAddWebsiteOverlay(<?php echo $categoryId; ?>)"
                        onmouseenter="tooltip.activate('Add website')"
                        onmouseleave="tooltip.deactivate()"><span class="material-icons">add</span>
                </button>
                <button class="w3-btn w3-khaki w3-round-xxlarge" style="float:right;height: 38px"
                        onclick="openAddCategoryOverlay(<?php echo $categoryId; ?>)"
                        onmouseenter="tooltip.activate('Add category')"
                        onmouseleave="tooltip.deactivate()"><span class="material-icons">create_new_folder</span>
                </button>
            <?php } ?>
        </div><?php
        if (count($websites) + count($childCategories) > 0) { ?>
            <ul>
                <?php foreach ($websites as $website) {
                    $websiteId = $website->getWebsiteId(); ?>
                    <li class="websitesUnder<?php echo $categoryId; ?>" style="position: relative;display: none">
                        <div id="website<?php echo $websiteId; ?>" class="website w3-white w3-hover-grey w3-card"
                             style="clear: both;position: relative;padding-left: 15px;"
                             onclick="openWebsite(<?php echo $websiteId; ?>)">
                            <span style="position: absolute;top: 50%;transform: translateY(-50%);"><?php echo $website->getTitle(); ?></span>
                            <button class="w3-btn w3-teal w3-round-xxlarge" style="float:right;margin-left: 10px"
                                    onclick="deleteWebsite(<?php echo $website->getWebsiteId(); ?>)"
                                    onmouseenter="tooltip.activate('<?php echo ($shared ? "Unlink" : "Delete"); ?>')"
                                    onmouseleave="tooltip.deactivate()"><span class="material-icons">delete</span>
                            </button>
                            <?php if (!$shared) { ?>
                                <button class="w3-btn w3-light-green w3-round-xxlarge"
                                        style="float:right;margin-left: 10px"
                                        onclick="openShareWebsiteOverlay(<?php echo $website->getWebsiteId(); ?>)"
                                        onmouseenter="tooltip.activate('Share')"
                                        onmouseleave="tooltip.deactivate()"><span class="material-icons">share</span>
                                </button>
                            <?php } ?>
                            <button class="w3-btn w3-khaki w3-round-xxlarge" style="float:right;"
                                    onclick="openRawWebsite('<?php echo $website->getUrl(); ?>')"
                                    onmouseenter="tooltip.activate('Open original')"
                                    onmouseleave="tooltip.deactivate()"><span class="material-icons">link</span>
                            </button>
                        </div>
                    </li>
                <?php }
                foreach ($childCategories as $childCategory) displayCategories($childCategory, $sharedWebsites); ?>
            </ul>
        <?php } ?>
    </li>
<?php }

$user = $userFactory->currentUser();
$user_handle = $user->getHandle(); ?>
<html lang="en_US">
<head>
    <title>Dashboard</title>
    <?php HtmlTemplate::header(); ?>
    <style><?php include(APP_LOCATION . "/resources/css/dashboard.css"); ?></style>
</head>
<body>
<div id="addWebsiteOverlay" class="overlay">
    <div>
        <label for="txtAddWebsite" style="">New website</label>
        <input id="txtAddWebsite" class="w3-input w3-round-xxlarge" type="text">
    </div>
    <i class="material-icons" style="padding: 40px 50px;position: absolute;right: 0;font-size: 2em;cursor:pointer;"
       onclick="closeWebsiteOverlay()">close</i>
</div>
<div id="addCategoryOverlay" class="overlay">
    <div>
        <label for="txtAddCategory" style="">New category</label>
        <input id="txtAddCategory" class="w3-input w3-round-xxlarge" type="text">
    </div>
    <i class="material-icons" style="padding: 40px 50px;position: absolute;right: 0;font-size: 2em;cursor:pointer;"
       onclick="closeCategoryOverlay()">close</i>
</div>
<div id="shareWebsiteOverlay" class="overlay">
    <div style="width: 80vw;height: 80vh">
        <div style="width: 100%;">People you have shared with:</div>
        <div id="currentGuests" style="width: 100%;min-height: 30%;max-height:40%;overflow-y: auto"
             class="w3-round-large w3-white"></div>
        <div style="width: 100%;margin-top: 10px">Look up people to share with:</div>
        <!--suppress HtmlFormInputWithoutLabel -->
        <textarea id="nameLookup" rows="1" style="resize: none" class="w3-input w3-border w3-round"
                  oninput="lookupNewGuests()"></textarea>
        <div id="lookupResult" style="width: 100%;max-height: 40%;overflow-y: auto"></div>
    </div>
    <i class="material-icons" style="padding: 40px 50px;position: absolute;right: 0;font-size: 2em;cursor:pointer;"
       onclick="closeShareWebsiteOverlay()">close</i>
</div>
<?php HtmlTemplate::topNavigation(function () { ?>
    <a class="w3-bar-item w3-button" href="<?php echo CHARACTERISTIC_DOMAIN; ?>/dashboard">Dashboard</a>
<?php }); ?>
<br><br>
<h2>Categories</h2>
<ul id="categories">
    <?php displayCategories($user->getRootCategory(), map($permissionFactory->getFromUser($user), fn(Permission $permission) => $permission->getWebsite())); ?>
</ul>
<div id="toast" class="w3-round-xxlarge"></div>
<div id="tooltip" class="w3-round-large w3-white w3-card"></div>
</body>
<?php HtmlTemplate::scripts(); ?>
<?php include(APP_LOCATION . "/resources/js/dashboard.php"); ?>
<script type="application/javascript">
    const gui = {
        "addWebsiteOverlay": $("#addWebsiteOverlay"),
        "txtAddWebsite": $("#txtAddWebsite"),
        "addCategoryOverlay": $("#addCategoryOverlay"),
        "txtAddCategory": $("#txtAddCategory"),
        "shareWebsiteOverlay": $("#shareWebsiteOverlay"),
        "lookupResult": $("#lookupResult"),
        "currentGuests": $("#currentGuests")
    };

    class User {
        constructor(user_handle, userName, avatarUrl) {
            this.user_handle = user_handle;
            this.userName = atob(userName);
            this.avatarUrl = atob(avatarUrl);
        }

        /**
         * Renders the user as a little card
         *
         * @param {string} btnLabel Label of the button
         * @param {string} btnCallback Callback of the button, string, and is global agnostic
         * @returns {string}
         */
        render(btnLabel, btnCallback) {
            return `
                <div class="w3-card-2 w3-white w3-hover-light-grey"
                     style="padding: 8px; position: relative;margin-top: 1px;">
                    <img src="${this.avatarUrl}" alt="Avatar" class="w3-left w3-circle"
                         width="45px" height="45px" style="margin-right: 8px">
                    <div style="position: absolute;top: 50%;left: 61px;transform: translateY(-50%);">${this.userName}&nbsp;&nbsp;<span style="color: #616161">@${this.user_handle}</span></div>
                    <button class="w3-btn w3-light-green w3-round-xxlarge" onclick="${btnCallback}"
                            style="float:right;margin-left: 10px;height: 45px;">${btnLabel}
                    </button>
                    <div style="clear: both"></div>
                </div>`;
        }
    }

    /**
     * @param {string} response
     * @return {User[]}
     */
    function usersFromResponse(response) {
        const splits = response.split("\n");
        /** @type {User[]} user */ const users = [];
        for (let i = 0; i < splits.length / 3 - 0.7; i++) users.push(new User(splits[i * 3 + 1], splits[i * 3 + 2], splits[i * 3 + 3]));
        return users;
    }

    function lookupNewGuests() {
        const starts = $("#nameLookup").val();
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/lookupName"; ?>",
            type: "POST",
            data: {
                starts: btoa(starts),
                exclude: currentGuests.map(user => btoa(user.user_handle)).join("\n")
            },
            success: (response) => gui.lookupResult.html(usersFromResponse(response).map(user => user.render("Invite", `inviteUser('${user.user_handle}')`)).join("")),
            error: () => toast.display("Can't connect to server to look up users. Please check your internet connection.")
        });
    }

    function lookupCurrentGuests() {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/invitedUsers"; ?>",
            type: "POST",
            async: false,
            data: {
                websiteId: shareWebsiteId
            },
            success: (response) => {
                currentGuests = usersFromResponse(response);
                gui.currentGuests.html(currentGuests.map(user => user.render("Revoke", `revokeUser('${user.user_handle}')`)).join(""));
            },
            error: () => toast.display("Can't connect to server to look up users. Please check your internet connection.")
        });
    }

    let justWebsite = false;

    function openWebsite(websiteId) {
        if (justWebsite) {
            justWebsite = false;
            return;
        }
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/setRemote"; ?>",
            type: "POST",
            data: {
                websiteId: websiteId
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . SITE; ?>",
            error: () => toast.display("Can't connect to server to open. Please check your internet connection.")
        });
    }

    function openRawWebsite(url) {
        justWebsite = true;
        window.open(url, '_blank');
    }

    function deleteWebsite(websiteId) {
        justWebsite = true;
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/deleteWebsite"; ?>",
            type: "POST",
            data: {
                website_id: websiteId
            },
            success: () => window.location.reload(),
            error: () => toast.display("Can't connect to server to delete. Please check your internet connection.")
        });
    }

    let shareWebsiteId = -1;
    /** @type {User[]} currentGuests */ let currentGuests = [];

    function openShareWebsiteOverlay(websiteId) {
        justWebsite = true;
        shareWebsiteId = websiteId;
        gui.shareWebsiteOverlay.addClass("active");
        lookupCurrentGuests();
        lookupNewGuests();
    }

    function closeShareWebsiteOverlay() {
        gui.shareWebsiteOverlay.removeClass("active");
    }

    function inviteUser(user_handle) {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/inviteUser"; ?>",
            type: "POST",
            data: {
                user_handle: user_handle,
                websiteId: shareWebsiteId
            },
            success: () => {
                lookupCurrentGuests();
                lookupNewGuests();
            },
            error: () => toast.display("Can't connect to server to invite. Please check your internet connection.")
        });
    }

    function revokeUser(user_handle) {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/revokeUser"; ?>",
            type: "POST",
            data: {
                user_handle: user_handle,
                websiteId: shareWebsiteId
            },
            success: () => {
                lookupCurrentGuests();
                lookupNewGuests();
            },
            error: () => toast.display("Can't connect to server to invite. Please check your internet connection.")
        });
    }

    let justCategory = false; // like justWebsite, but this is for the category tabs
    let addWebsiteCategoryId = -1;
    let addCategoryCategoryId = -1;

    function toggleWebsitesVisibility(categoryId) {
        if (justCategory) {
            justCategory = false;
            return;
        }
        let elements = $(".websitesUnder" + categoryId);
        if (elements.css("display") !== "none") elements.css("display", "none");
        else elements.css("display", "block");
    }

    function openAddWebsiteOverlay(categoryId) {
        justCategory = true;
        addWebsiteCategoryId = categoryId;
        gui.addWebsiteOverlay.addClass("active");
        document.getElementById("txtAddWebsite").focus();
    }

    function openAddCategoryOverlay(categoryId) {
        justCategory = true;
        addCategoryCategoryId = categoryId;
        gui.addCategoryOverlay.addClass("active");
        document.getElementById("txtAddCategory").focus();
    }

    function deleteCategory(categoryId) {
        justCategory = true;
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/deleteCategory"; ?>",
            type: "POST",
            data: {
                category_id: categoryId
            },
            success: () => window.location.reload(),
            error: () => toast.display("Can't connect to server to delete. Please check your internet connection.")
        });
    }

    gui.txtAddWebsite.on('keypress', (e) => {
        if (e.which === 13) {
            if (addWebsiteCategoryId === -1) {
                toast.display("A website has not been filled in. Please refresh the page.");
            } else {
                $.ajax({
                    url: "<?php echo DOMAIN_CONTROLLER . "/addWebsite"; ?>",
                    type: "POST",
                    data: {
                        category_id: addWebsiteCategoryId,
                        url: gui.txtAddWebsite.val()
                    },
                    success: (response) => openWebsite(response),
                    error: () => toast.display("Can't connect to server to add website. Please check your internet connection.")
                });
            }
        }
    });

    gui.txtAddCategory.on('keypress', (e) => {
        if (e.which === 13) {
            if (addCategoryCategoryId === -1) {
                toast.display("A category has not been filled in. Please refresh the page.");
            } else {
                $.ajax({
                    url: "<?php echo DOMAIN_CONTROLLER . "/addCategory"; ?>",
                    type: "POST",
                    data: {
                        category_id: addCategoryCategoryId,
                        name: gui.txtAddCategory.val()
                    },
                    success: () => window.location.reload(),
                    error: () => toast.display("Can't connect to server to add category. Please check your internet connection.")
                });
            }
        }
    });

    function closeWebsiteOverlay() {
        gui.addWebsiteOverlay.removeClass('active');
    }

    function closeCategoryOverlay() {
        gui.addCategoryOverlay.removeClass('active');
    }

    if (location.protocol !== "https:") {
        location.protocol = "https:";
    }
</script>
</html>
