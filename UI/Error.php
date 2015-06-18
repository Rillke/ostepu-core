<?php
/**
 * @file Error.php
 *
 * @author Ralf Busch
 */

include_once 'include/Boilerplate.php';
include_once dirname(__FILE__) . '/../Assistants/Language.php';

if (isset($_GET['msg'])) {
  $msg = cleanInput($_GET['msg']);
}

if (isset($msg) && $msg == "403") {
    header("HTTP/1.0 403 Access Forbidden");
    $notifications[] = MakeNotification("error", "403: Access Forbidden");
}elseif (isset($msg) && $msg == "404") {
    header("HTTP/1.0 404 Not Found");
    $notifications[] = MakeNotification("error", "404: Not Found!");
}elseif (isset($msg) && $msg == "409") {
    header("HTTP/1.0 404 Not Found");
    $notifications[] = MakeNotification("error", "409: Conflict!");
}else{
    header("HTTP/1.0 403 Not Found");
    $notifications[] = MakeNotification("error", "403: ".$msg);
}

$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind(array("name" => "Übungsplattform",
               "backTitle" => "Startseite",
               "backURL" => "index.php",
               "hideLogoutLink" => "true",
               "notificationElements" => $notifications));

// wrap all the elements in some HTML and show them on the page
$w = new HTMLWrapper($h);
$w->set_config_file('include/configs/config_default.json');
$w->show();
