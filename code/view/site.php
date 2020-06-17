<?php

use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;

if (!$session->has("remote")) Header::redirect("dashboard");
if (!$session->has("websiteId")) Header::redirect("dashboard");
$url = $session->getCheck("remote");
$parsed_url = parse_url($url);
$modifiedUrl = str_replace($parsed_url["scheme"] . "://" . $parsed_url["host"], DOMAIN, $url);
$websiteId = $session->getCheck("websiteId");
$website = $websiteFactory->get($websiteId);
if (!$authenticator->websiteAuthenticated($website)) Header::redirect("dashboard");
$highlights = $website->getHighlights();
?>
<!DOCTYPE html>
<html lang="en_US">
<head>
    <title>NotesCapture</title>
    <?php HtmlTemplate::header(); ?>
    <style><?php include(APP_LOCATION . "/resources/css/site.css"); ?></style>
</head>
<body>
<iframe id="page" sandbox="allow-same-origin allow-scripts" src="<?php echo $modifiedUrl; ?>"
        style="display: none"></iframe>
<div id="panel">
    <?php HtmlTemplate::topNavigation(function () use ($websiteId) { ?>
        <a class="w3-bar-item w3-button w3-border-right" onclick="highlights.capture(<?php echo $websiteId; ?>)">New</a>
        <a class="w3-bar-item w3-button" id="unknownBtn" onclick="highlights.toggleDisplayMode()">
            <span id="unknownToolbar" style="display: none">Unknowns: <span id="unknownAmount">0</span></span>
            <span id="knownToolbar">Knowns: <span id="knownAmount">0</span></span>
        </a>
    <?php }, function () { ?>
        <a class="w3-bar-item w3-button w3-right w3-border-right"
           href="<?php echo CHARACTERISTIC_DOMAIN . "/dashboard"; ?>">Dashboard</a>
    <?php }); ?>
    <input type="button" value="New highlight" onclick="highlights.capture(<?php echo $websiteId; ?>)">
    <div id="unknowns"></div>
</div>
<div id="toast" class="w3-round-xxlarge"></div>
<div id="boundingBoxes"></div>
</body>
<?php HtmlTemplate::scripts(); ?>
<?php include(APP_LOCATION . "/resources/js/site.php"); ?>
<!--suppress JSValidateJSDoc, JSDeprecatedSymbols, PointlessBooleanExpressionJS -->
<script type="text/javascript">
    const gui = {
        "body": $("body")[0],
        "panel": $("#panel"),
        "page": $("#page"),
        "unknowns": $("#unknowns"),
        "unknownBtn": $("#unknownBtn"),
        "unknownToolbar": $("#unknownToolbar"),
        "unknownAmount": $("#unknownAmount"),
        "knownToolbar": $("#knownToolbar"),
        "knownAmount": $("#knownAmount"),
        "pageContentWindow": document.getElementById("page").contentWindow,
        "boundingBoxes": $("#boundingBoxes")
    };
    const displayMetrics = false; // displays metrics of the algorithm

    /** @type {number} justPanel */ let justPanel = 0;

    // if the generic panel is clicked, make all of the comment dropdowns disappear
    gui.panel.on("click", () => {
        if (justPanel > 0) {
            justPanel--;
            return;
        }
        $(".commentDropdown").css("display", "none");
        highlights.outOfFocus();
    });

    if (window.innerWidth < 900) {
        toast.display("This application can't work on phones and tablets. Too little space!");
        gui.page.css("display", "none");
        gui.panel.css("display", "none");
        throw new Error(); // prevent execution of the rest
    }

    /** @type {Highlights} highlights */
    const highlights = new Highlights();

    setTimeout(() => {
        document.getElementById("page").contentWindow.onscroll = () => highlights.display();
        const getTitle = () => {
            let title = document.getElementById("page").contentDocument.title;
            if (title === "") setTimeout(getTitle, 1000);
            else document.title = "NotesCapture - " + document.getElementById("page").contentDocument.title;
        }
        getTitle();
        /** @type {KComment} comment */ let comment;
        let comments;
        <?php foreach ($highlights as $highlight) {
        $comment = $commentFactory->getRoot($highlight->getHighlightId());
        ?>
        toast.persistTillNextDisplay("Loading highlights...");
        comments = [];
        comment = null;
        <?php
        while (true) { $commentUser = $userFactory->get($comment->getUserHandle()); $currentUser = $userFactory->currentUser() ?>
        comment = new KComment(<?php echo $comment->getCommentId(); ?>, "<?php echo base64_encode($commentUser->getName()); ?>", "<?php echo base64_encode($commentUser->getPictureUrl()); ?>", "<?php echo $timezone->display($currentUser->getTimezone(), $comment->getUnixTime()); ?>", comment, "<?php echo base64_encode($comment->getContent()); ?>", <?php echo ($currentUser === $commentUser) ? "true" : "false"; ?>);
        comments.push(comment);
        <?php
        if ($comment->getChildComment() !== null) {
            $comment = $comment->getChildComment();
        } else break;} ?>
        highlights.addFromServer(<?php echo $highlight->getHighlightId(); ?>, <?php echo $highlight->getWebsiteId(); ?>, "<?php echo $highlight->getRawStrings(); ?>", coherentRootComment(comments));
        <?php } ?>
    }, 200);

    document.getElementById("panel").onwheel = (event) => {
        if (highlights.normalDisplayMode) gui.pageContentWindow.scrollBy(event.deltaX, event.deltaY);
    }

    // detects that the site is refreshed and if so, sets the remote again, so that the root route points to the specified domain
    if (performance.navigation.type !== 0) {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER . "/setRemote"; ?>",
            type: "POST",
            data: {
                websiteId: <?php echo $websiteId; ?>
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . SITE; ?>"
        });
    } else gui.page.css("display", "block");

    // reruns this every now and then, to make sure the bounding boxes change as the page changes slightly
    setInterval(() => highlights.display(), 3000);
</script>
</html>
