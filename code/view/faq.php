<?php

use Kelvinho\Notes\Singleton\HtmlTemplate;

?>
<html lang="en_US">
<head>
    <title>Frequently asked questions</title>
    <?php HtmlTemplate::header(); ?>
</head>
<body>
<?php if ($authenticator->authenticated()) {
    HtmlTemplate::topNavigation(function () { ?>
        <a class="w3-bar-item w3-button" href="<?php echo CHARACTERISTIC_DOMAIN; ?>/dashboard">Dashboard</a>
    <?php });
} ?>
<br><br>
<h2>Frequently asked questions</h2>
<h3>How many websites can it work on?</h3>
<p>The inner workings of the application is quite tricky, and modern technology has lots of edge cases and we can't
    really get it to work on 100% of sites. It is important to note that it is virtually impossible to make it available
    on every website out there, as the task is on par with redesigning the web. All sites that require you to log in is
    out of the question as we will actually put you in danger if we allow you to sign in. Old websites almost always
    work well. 3-5 year old websites usually work, but sites that use very recent and very complex technologies may not
    work. Just to give you a feeling, some websites that look good enough to annotate is:</p>
<ul>
    <li>Wikipedia</li>
    <li>Google searches</li>
    <li>Reddit posts</li>
    <li>Stack Overflow</li>
    <li>GitHub</li>
    <li>Khan Academy</li>
    <li>Steam</li>
    <li>PyTorch</li>
    <li>BBC</li>
</ul>
<p>Websites that looks okay, but functionality is reduced:</p>
<ul>
    <li>Kaggle</li>
    <li>LinkedIn</li>
    <li>YouTube</li>
</ul>
<p>Websites that can't load:</p>
<ul>
    <li>Medium</li>
    <li>Tesla</li>
    <li>Ars Technica</li>
    <li>CNN</li>
</ul>
<h3>What are cross origin restrictions?</h3>
<p>These are restrictions by IT security teams around the world aiming to prevent embedding of a website in another
    website. The purpose of doing this is to prevent a website displaying a legitimate website, because that website can
    place invisible input fields right above the legitimate website, and trick customers to get their passwords and
    whatnot. This makes my job a lot harder, so I only managed to recreate a majority of sites, and not all of them.</p>
<h3>Why can't I annotate some pieces of text?</h3>
<p>How the application works in short is that it will segment the selected text, save them, and then look for those when
    you open the website later on. This means that for it to work well, the selected text should be long (7 or more
    words, although the minimum is 5 characters) and there shouldn't have lots of breaks in the middle, like links,
    colored texts, and so on. It's not a huge deal if the application can't look for a piece of text as it is quite
    robust, as it can still work when the underlying content changes, but if it can't look for a large percentage (30%
    or more) of the selected text, it gives up and notifies you so.</p>
<h3>What are "unknowns"?</h3>
<p>These are the sections of text that the system can't pinpoint the exact location of. The annotation will still be
    available in the unknowns tab though, so check them out.</p>
<h3>Can I annotate pdf files?</h3>
<p>No you can't. We plan to add that in the future, but no release date has been announced yet.</p>
<h3>What happens when I delete a website or a category?</h3>
<p>It's gone, never to be seen again. There is currently no undo button, and the information is deleted from our
    servers as well.</p>
<h3>What happens when the internet connection dropped? Will my notes be saved?</h3>
<p>No they will not, but for every failed attempt at reaching our servers, we will tell you right away so you can fix
    it.</p>
<h3>Will this work on phones and tablets?</h3>
<p>No it will not. This is just the fact that phones and tablets don't provide enough space to work with in the
    beginning, so doing normal work on them just doesn't make sense. The cutoff screen width right now is 900 pixels</p>
<h3>Can I annotate code?</h3>
<p>For code that is formatted with fancy colors, no. For code that is just a lump of text, yes.</p>
<h3>Can I annotate equations?</h3>
<p>You can't annotate equations directly as they are quite tricky to deal with. All mathematical symbols are eliminated
    from the internal search engine. However, you can annotate the text surrounding it which may include the equations
    as well.</p>
<h3>What are other weird quirks that I, an average user, needs to know about?</h3>
<p>You can annotate many sites at once, but because of the underlying mechanism, you shouldn't refresh it, or else it
    will opens the most recent site. You can't also click a link while the annotation window is open. You can actually
    click one, but you just can't annotate anymore. This is due to cross origin restrictions mentioned above. So the way
    to annotate a different site is to add the new website entirely.</p>
<h3>What are other weird quirks that I, an advanced user, needs to know about?</h3>
<p>Each time you open a new site to annotate, the bare domain "<?php echo DOMAIN; ?>" actually shows the 3rd party site
    that you are viewing. However, this only happens until <?php echo REMOTE_EXPIRES_DURATION; ?> seconds after you have
    selected a site to annotate and will redirect to the dashboard after that. This is so that regular users can access
    the dashboard without having to remember a convoluted url.</p>
<?php HtmlTemplate::scripts(); ?>
</html>
