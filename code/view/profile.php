<?php

use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\Singleton\HtmlTemplate;

if (!$authenticator->authenticated()) Header::redirectToHome();
$user = $userFactory->currentUser();
?>
<!DOCTYPE html>
<html lang="en_US">
<head>
    <title>Account</title>
    <?php HtmlTemplate::header(); ?>
    <style>
        select option {
            height: 200px;
        }
    </style>
</head>
<body>
<?php HtmlTemplate::topNavigation(function() { ?>
    <a class="w3-bar-item w3-button" href="<?php echo CHARACTERISTIC_DOMAIN; ?>/dashboard">Dashboard</a>
<?php }); ?>
<br><br>
<h2>Account</h2>
<label for="user_handle">User name</label><input id="user_handle" class="w3-input" type="text"
                                                 value="<?php echo $user->getHandle(); ?>" disabled>
<br>
<label for="name">Name</label><input id="name" class="w3-input" type="text"
                                     value="<?php echo $user->getName(); ?>">
<br>
<label for="timezone">Timezone</label><select id="timezone" class="w3-select" name="option"
                                                       style="padding: 10px;">
    <?php foreach ($timezone->getTimezones() as $timezoneString) { ?>
        <option value="<?php echo $timezoneString; ?>"><?php echo $timezone->getDescription($timezoneString); ?></option>
    <?php } ?>
</select>
<br><br>
<button class="w3-btn w3-teal" onclick="update()">Update</button>
<br><br>
</body>
<?php HtmlTemplate::scripts(); ?>
<script>
    const gui = {timezone: $("#timezone"), name: $("#name")};
    gui.timezone.val("<?php echo $user->getTimezone(); ?>");

    function update() {
        $.ajax({
            url: "<?php echo DOMAIN_CONTROLLER; ?>/updateUser",
            type: "POST",
            data: {
                name: gui.name.val(),
                timezone: gui.timezone.val()
            },
            success: () => window.location = "<?php echo CHARACTERISTIC_DOMAIN . "/profile"; ?>"
        });
    }
</script>
</html>
