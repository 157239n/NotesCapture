<?php

namespace Kelvinho\Notes\Singleton;

/**
 * Class HtmlTemplate, provides sort of a shared structure. This is not really elegant, I will factor it later.
 *
 * @package Kelvinho\Notes\Singleton
 */
class HtmlTemplate {
    /**
     * The header (no <head> included. This contains:
     * - Css from 157239n.com
     * - Css from w3school.com
     * - Viewports
     * - .link classes have pointer cursor and their color blue
     */
    public static function header(): void { ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="utf-8">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="<?php echo DOMAIN_RESOURCES; ?>/css/styles.css">
        <!--suppress CssUnusedSymbol -->
        <style>
            .w3-table td {
                vertical-align: inherit;
            }

            h1 {
                color: rgb(69, 128, 100);
            }

            h2 {
                color: #616161;
            }
        </style>
    <?php }

    public static function topNavigation(callable $additionalCb1 = null, callable $additionalCb2 = null): void { ?>
        <div id="toolBar" class="w3-bar w3-light-grey w3-card">
            <?php if ($additionalCb1 != null) $additionalCb1(); ?>
            <div class="w3-bar-item w3-button w3-right w3-dropdown-hover w3-light-grey" style="height: 38px;"><i
                        class="material-icons">settings</i>
                <div class="w3-dropdown-content w3-bar-block w3-card-4" style="position: fixed;right: 0; top: 38px;">
                    <a href="<?php echo CHARACTERISTIC_DOMAIN; ?>/profile" class="w3-bar-item w3-button">Profile</a>
                    <a href="<?php echo CHARACTERISTIC_DOMAIN; ?>/faq" class="w3-bar-item w3-button">FAQ</a>
                    <a href="<?php echo CHARACTERISTIC_DOMAIN; ?>/logout" class="w3-bar-item w3-button">Sign out</a>
                </div>
            </div>
            <?php if ($additionalCb2 != null) $additionalCb2(); ?>
        </div>
    <?php }

    /**
     * The scripts tag. This contains:
     * - jquery minified cdn
     * - javascript from 157239n.com
     */
    public static function scripts(): void { ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <?php include(APP_LOCATION . "/resources/js/scripts.php"); ?>
    <?php }
}
