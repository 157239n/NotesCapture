<?php

use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;

//$url = "";
//$url = "https://stackoverflow.com/questions/3076414/ways-to-circumvent-the-same-origin-policy";
//$url = "https://ruder.io/optimizing-gradient-descent/index.html"; // this one can only work when I have already done the redirect thingy
//$url = "http://blackbox.nn.157239n.com/basics";
//$url = "https://157239n.com/page/pages/neural-1/";
//$url = "https://github.com/157239n/Virus";
//$url = "https://github.com/hieudan225/step";
//$url = "https://github.com/hieudan225";
//$url = "https://157239n.com";
//$url = "https://nn.157239n.com";
//$url = "https://phys.libretexts.org/Bookshelves/University_Physics/Book%3A_University_Physics_(OpenStax)/Map%3A_University_Physics_III_-_Optics_and_Modern_Physics_(OpenStax)/06%3A_Photons_and_Matter_Waves/6.03%3A_Photoelectric_Effect#:~:text=A%20430%2Dnm%20violet%20light,kinetic%20energy%20of%20ejected%20electrons.&text=The%20energy%20of%20the%20incident,we%20use%20f%CE%BB%3Dc.";
//$url = "https://www.google.com/search?sxsrf=ALeKk00x9mSGurMtvEiuVSvCZlxccOZw7g%3A1591592825260&ei=ecfdXuPAD5PM1QHMpqbYDQ&q=set+iframe+origin+of+base64&oq=set+iframe+origin+of+base64&gs_lcp=CgZwc3ktYWIQA1AAWABgk4kBaABwAHgAgAEAiAEAkgEAmAEAqgEHZ3dzLXdpeg&sclient=psy-ab&ved=0ahUKEwij4eenufHpAhUTZjUKHUyTCdsQ4dUDCAw&uact=5";

if (!$session->has("remote")) Header::redirect("dashboard");
if (!$session->has("websiteId")) Header::redirect("dashboard");
$url = $session->getCheck("remote");
$websiteId = $session->getCheck("websiteId");
$website = $websiteFactory->get($websiteId);
$highlights = $website->getHighlights();
?>
<!DOCTYPE html>
<html lang="en_US">
<head>
    <title>Try embedding</title>
    <?php HtmlTemplate::header(); ?>
    <!--suppress CssUnusedSymbol -->
    <style>
        #toolBar {
            width: 30vw;
            left: 70vw;
        }

        textarea {
            resize: none;
        }

        .contentBox button, .unknownBox button {
            margin-top: 8px;
        }

        body {
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            padding: 0;
        }
    </style>
</head>
<body>
<iframe sandbox="allow-same-origin allow-scripts" id="page" src="<?php echo CHARACTERISTIC_DOMAIN; ?>/empty"></iframe>
<div id="panel">
    <?php HtmlTemplate::topNavigation(function () use ($websiteId) { ?>
        <a class="w3-bar-item w3-button w3-border-right" onclick="highlights.capture(<?php echo $websiteId; ?>)">New</a>
        <a class="w3-bar-item w3-button" id="unknownBtn" onclick="highlights.toggleDisplayMode()">Unknowns: <span
                    id="unknownAmount">0</span></a>
    <?php }, function () { ?>
        <a class="w3-bar-item w3-button w3-right w3-border-right"
           href="<?php echo CHARACTERISTIC_DOMAIN . "/dashboard"; ?>">Dashboard</a>
    <?php }); ?>
    <input type="button" value="New highlight" onclick="highlights.capture(<?php echo $websiteId; ?>)">
    <div id="unknowns"></div>
    <div id="toast" class="w3-round-xxlarge"></div>
</div>
<div id="boundingBoxes"></div>
</body>
<?php HtmlTemplate::scripts(); ?>
<!--suppress JSValidateJSDoc -->
<script type="text/javascript">
    const gui = {
        "body": $("body")[0],
        "panel": $("#panel"),
        "page": $("#page"),
        "unknowns": $("#unknowns"),
        "unknownBtn": $("#unknownBtn"),
        "unknownAmount": $("#unknownAmount"),
        "toast": $("#toast"),
        "pageContentWindow": document.getElementById("page").contentWindow,
        "boundingBoxes": $("#boundingBoxes")
    };
    const displayMetrics = false; // displays metrics of the algorithm

    const highlights = new Highlights();

    fetch("<?php echo DOMAIN_CONTROLLER; ?>/getRss?rss=" + btoa(`<?php echo $url; ?>`)).then(response => response.text())
        .then(data => {
            //gui.page.attr("srcdoc", data);
            document.getElementById('page').contentWindow.document.write(data); // old way
        }).then(() => setTimeout(() => {
            document.getElementById("page").contentWindow.onscroll = () => highlights.display();
            document.title = "NotesCapture - " + document.getElementById("page").contentDocument.title;
            console.log("Done");
            <?php foreach ($highlights as $highlight) { ?>
            toast.persistTillNextDisplay("Loading highlights...");
            highlights.addFromServer(<?php echo $highlight->getHighlightId(); ?>, <?php echo $highlight->getWebsiteId(); ?>, "<?php echo $highlight->getRawStrings(); ?>", "<?php echo $highlight->getComment(); ?>")
            <?php } ?>
        }, 200)
    );/**/

    document.getElementById("panel").onwheel = (event) => {
        if (highlights.normalDisplayMode) gui.pageContentWindow.scrollBy(event.deltaX, event.deltaY);
    }
</script>
</html>
