<?php

/**
 * Tries to logs the user in, or register a new user, given:
 * - type: The federated party's name. At the time of writing, only "google" is available
 * - timezone: The timezone string, like America/New_York
 *
 * If registering, must:
 * - Have the user handle's length less than USER_HANDLE_LENGTH_LIMIT
 * - Have the user's name's length less than USER_NAME_LENGTH_LIMIT
 * - Have the user's handle be alphanumeric only
 * - Have a unique user handle
 */

use Kelvinho\Notes\Auth\UnknownThirdParty;
use Kelvinho\Notes\Singleton\Header;

$type = $requestData->postCheck("type");
switch ($type) {
    case "google":
        $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
        $payload = $client->verifyIdToken($requestData->postCheck("token"));
        if ($payload) {
            $user_handle = $payload['sub'];
            if ($userFactory->exists($user_handle)) { // signing in
                $session->set("user_handle", $user_handle);
            } else { // registering
                $timezoneString = $requestData->postCheck("timezone");
                $name = $payload["name"];

                if (!$timezone->hasTimezone($timezoneString)) Header::badRequest();
                if (strlen($user_handle) > USER_HANDLE_LENGTH_LIMIT) Header::badRequest();
                if (strlen($name) > USER_NAME_LENGTH_LIMIT) Header::badRequest();
                if (preg_match('/[^A-Za-z0-9_]/', $user_handle)) Header::badRequest();
                if ($userFactory->exists($user_handle)) Header::badRequest();

                $userFactory->newFederated(1, $user_handle, $name, $timezoneString, $payload["picture"]);
                $session->set("user_handle", $user_handle);
            }
        } else Header::forbidden();
        break;
    default:
        throw new UnknownThirdParty();
}

$authenticator->authenticated() ? Header::ok() : Header::forbidden();
