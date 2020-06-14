<?php

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;

if (!$authenticator->authenticated()) Header::redirect("login");

function displayCategories(Category $category): void {
    $categoryId = $category->getCategoryId();
    $websites = $category->getWebsites();
    $childCategories = $category->getChildren(); ?>
    <li>
        <div id="category<?php echo $categoryId; ?>"
             class="category w3-light-grey w3-hover-grey w3-card"
             onclick="toggleWebsitesVisibility(<?php echo $categoryId; ?>)"
             style="clear: both;position: relative;padding-left: 15px;">
            <span style="position: absolute;top: 50%;transform: translateY(-50%);"><?php echo $category->getName(); ?>
                <span style="color: #616161;">, sites: <?php echo count($websites); ?></span></span>
            <?php if (!$category->isRoot()) { ?>
                <button class="w3-btn w3-teal" style="float:right;margin-left: 10px"
                        onclick="deleteCategory(<?php echo $categoryId; ?>)">Delete
                </button>
            <?php } ?>
            <button class="w3-btn w3-light-green" style="float:right;margin-left: 10px"
                    onclick="openAddWebsiteOverlay(<?php echo $categoryId; ?>)">Add website
            </button>
            <button class="w3-btn w3-khaki" style="float:right;"
                    onclick="openAddCategoryOverlay(<?php echo $categoryId; ?>)">Add category
            </button>
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
                            <button class="w3-btn w3-teal" style="float:right;margin-left: 10px"
                                    onclick="deleteWebsite(<?php echo $website->getWebsiteId(); ?>)">Delete
                            </button>
                            <button class="w3-btn w3-blue-grey" style="float:right;"
                                    onclick="openRawWebsite('<?php echo $website->getUrl(); ?>')">View raw
                            </button>
                        </div>
                    </li>
                <?php }
                foreach ($childCategories as $childCategory) displayCategories($childCategory); ?>
            </ul>
        <?php } ?>
    </li>
<?php }

$user_handle = $session->get("user_handle");
$user = $userFactory->get($user_handle);
?>
<html lang="en_US">
<head>
    <title>Dashboard</title>
    <?php HtmlTemplate::header(); ?>
    <!--suppress CssUnusedSymbol -->
    <style>
        .codes {
            color: midnightblue;
        }

        #categories ul, #categories {
            list-style-type: none;
            margin: 0;
        }

        #categories {
            padding: 0;
        }

        #categories li {
            margin: 0;
        }

        #categories li div {
            padding: 8px;
            margin: 1px 0 0;
            width: 100%;
            cursor: pointer;
            clear: both;
            height: 54px;
        }

        .overlay {
            width: 100vw;
            height: 100vh;
            top: 0;
            left: 0;
            position: fixed;
            /*display: none;*/
            background-color: #f0e68ccc;
            opacity: 0;
            z-index: 20000;
            pointer-events: none;
            transition: opacity 1s;
        }

        .overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .overlay > div {
            position: absolute;
            width: 50vw;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
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
<?php HtmlTemplate::topNavigation(function () { ?>
    <a class="w3-bar-item w3-button" href="<?php echo CHARACTERISTIC_DOMAIN; ?>/dashboard">Dashboard</a>
<?php }); ?>
<br><br>
<h2>Categories</h2>
<ul id="categories">
    <?php displayCategories($user->getRootCategory()); ?>
</ul>
</body>
<?php HtmlTemplate::scripts(); ?>
<script type="application/javascript">
    const gui = {
        "addWebsiteOverlay": $("#addWebsiteOverlay"), "txtAddWebsite": $("#txtAddWebsite"),
        "addCategoryOverlay": $("#addCategoryOverlay"), "txtAddCategory": $("#txtAddCategory")
    };

    let justOpenedRaw = false;

    function openWebsite(websiteId) {
        if (justOpenedRaw) {
            justOpenedRaw = false;
            return;
        }
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/setRemote"; ?>",
            type: "POST",
            data: {
                websiteId: websiteId
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . SITE; ?>"
        });
    }

    function openRawWebsite(url) {
        justOpenedRaw = true;
        window.open(url, '_blank');
    }

    function deleteWebsite(websiteId) {
        justOpenedRaw = true;
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/deleteWebsite"; ?>",
            type: "POST",
            data: {
                website_id: websiteId
            },
            success: () => window.location.reload()
        });
    }

    let justCategory = false; // like justOpenedRaw, but this is for the category tabs
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
            success: () => window.location.reload()
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
                    success: (response) => openWebsite(response)
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
                    success: () => window.location.reload()
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
