<?php
include 'include/HTMLWrapper.php';
include_once 'include/Template.php';
?>

<?php
    if (isset($_POST['action'])) {
        /**
         * @todo Set a cookie for the user, to mark him as logged in.
         * @todo Add parameter to redirect URL, so the uer's data will be loaded
         */
        Logger::Log($_POST, LogLevel::INFO);
        header("Location: Index.php");
    } elseif (isset($_GET['action'])) {
        if ($_GET['action'] == "logout") {
            /**
             * @todo Remove the cookie that indicates, that the user is logged in.
             */
            Logger::Log("Should log-out user now", LogLevel::INFO);
        }
    } else {
        Logger::Log("No Login Data", LogLevel::INFO);
    }
?>

<?php

$notifications = array();

// construct a new header
$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind(array("backTitle" => "Veranstaltung wechseln",
               "name" => "Übungsplattform",
               "hideLogoutLink" => "true",
               "notificationElements" => $notifications));

// construct a login element
$userLogin = Template::WithTemplateFile('include/Login/Login.template.html');

// wrap all the elements in some HTML and show them on the page
$w = new HTMLWrapper($h, $userLogin);
$w->set_config_file('include/configs/config_default.json');
$w->show();
?>

