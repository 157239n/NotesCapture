<?php

use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;

if ($authenticator->authenticated()) Header::redirectToHome(); ?>
<html lang="en_US">
<head>
    <title>Log in</title>
    <?php HtmlTemplate::header(); ?>
</head>
<body>
<h2>Log in</h2>
<label for="login_user_handle">User name</label><input id="login_user_handle" class="w3-input" type="text">
<br>
<label for="login_password">Password</label><input id="login_password" class="w3-input" type="password">
<div style="color: red;"><?php echo $requestData->get("loginMessage", ""); ?></div>
<br>
<button class="w3-btn w3-light-blue" onclick="login()">Login</button>
<h2>Register</h2>
<label for="register_user_handle">User name</label><input id="register_user_handle" class="w3-input" type="text">
<br>
<label for="register_password">Password</label><input id="register_password" class="w3-input" type="password">
<br>
<label for="register_name">Name</label><input id="register_name" class="w3-input" type="text">
<br>
<label for="register_timezone">Timezone</label><select id="register_timezone" class="w3-select" name="option"
                                                       style="padding: 10px;">
    <?php foreach ($timezone->getTimezones() as $timezoneString) { ?>
        <option value="<?php echo $timezoneString; ?>"><?php echo $timezone->getDescription($timezoneString); ?></option>
    <?php } ?>
</select>
<div id="register_message" style="color: red;"><?php echo $requestData->get("registerMessage", ""); ?></div>
<br>
<button class="w3-btn w3-light-green" onclick="register()">Register</button>
<h2>Log in/register with 3<sup>rd</sup> party providers</h2>
<div class="g-signin2" data-width="240" data-height="50" data-longtitle="true" data-theme="light"
     data-onsuccess="onGoogleSuccess" data-onfailure="onGoogleFailure"></div>
<h2>What is this?</h2>
<p>This application's purpose is to annotate sections of any websites that you come across. You can comment on the
    side
    of the things you find interesting, to be viewed later on, kinda like google docs's comment functionality. It
    should
    work on almost all websites, even ones that include complex Latex symbols. In the future, the ideal will be to
    make
    the annotations sharable to whomever you like.</p>
<p>This application is under the MIT license, and is freely available over <a href="<?php echo GITHUB_PAGE; ?>"
                                                                              style="color: blue; cursor: pointer;">github</a>,
    if the technical among you want to host this on your own website or want to check the integrity and security of
    this. I have put my best efforts into securing the application, but there can still be vulnerabilities.</p>
<div id="toast" class="w3-round-xxlarge"></div>
</body>
<?php HtmlTemplate::scripts(); ?>
<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<script>
    function onGoogleSuccess(googleUser) {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER; ?>/federatedSignin",
            type: "POST",
            data: {
                type: "google",
                timezone: gui.register_timezone.val(),
                token: googleUser.getAuthResponse().id_token
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/login"; ?>",
            error: () => toast.display("Something went wrong! Can't signin")
        });
    }

    function onGoogleFailure() {
        toast.display("Can't log in to Google. Please try again");
    }
</script>
<script type="application/javascript">
    const gui = {
        login_user_handle: $("#login_user_handle"),
        login_password: $("#login_password"),
        register_user_handle: $("#register_user_handle"),
        register_password: $("#register_password"),
        register_name: $("#register_name"),
        register_message: $("#register_message"),
        register_timezone: $("#register_timezone"),
    };

    //gui.register_timezone.val(0);

    function login() {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER; ?>/login",
            type: "POST",
            data: {
                user_handle: gui.login_user_handle.val().trim(),
                password: gui.login_password.val().trim()
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/login"; ?>",
            error: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/login"; ?>?loginMessage=User%20doesn't%20exist%20or%20password%20is%20wrong"
        });
    }

    function register() {
        const register_name = gui.register_name.val().trim();
        const register_user_handle = gui.register_user_handle.val().trim();
        const register_password = gui.register_password.val().trim();
        if (register_name.length === 0) {
            gui.register_message.html("Name can't be empty");
            return;
        }
        if (register_user_handle.length === 0) {
            gui.register_message.html("User name can't be empty");
            return;
        }
        if (register_user_handle.match("[^A-Za-z0-9_]")) {
            gui.register_message.html("User name can only be letters, numbers and \"_\".");
            return;
        }
        if (register_user_handle.length > <?php echo USER_NAME_LENGTH_LIMIT; ?>) {
            gui.register_message.html("User handle exceeds max length of 20");
            return;
        }
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER; ?>/register",
            type: "POST",
            data: {
                user_handle: register_user_handle,
                password: register_password,
                name: register_name,
                timezone: gui.register_timezone.val()
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/login"; ?>?registerMessage=Register%20successful.%20Please%20log%20in%20now",
            error: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/login"; ?>?registerMessage=Username%20already%20taken"
        })
    }

    const loginFunction = (event) => event.which === 13 ? login() : 0;
    gui.login_user_handle.keydown(loginFunction);
    gui.login_password.keydown(loginFunction);

    const registerFunction = (event) => event.which === 13 ? register() : 0;
    gui.register_user_handle.keydown(registerFunction);
    gui.register_password.keydown(registerFunction);
    gui.register_name.keydown(registerFunction);

    gui.register_timezone.val(Intl.DateTimeFormat().resolvedOptions().timeZone)
</script>
</html>
