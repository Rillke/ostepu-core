<?php
include 'include/Header/Header.php';
include 'include/HTMLWrapper.php';
include 'include/Group/InvitationsGroupSheet.php';
include 'include/Group/InviteGroupSheet.php';
include 'include/Group/ManageGroupSheet.php';

// construct a new Header
$h = new Header("Datenstrukturen",
                "",
                "Florian Lücke",
                "211221492", 
                "75%");

$h->setBackURL("index.php")
->setBackTitle("zur Veranstaltung");

$invitations = array(array( 
                     "user" => array("userID"=>"rvjbr",
                                     "email"=>"id.erat@mauris.co.uk",
                                     "firstName"=>"Colton",
                                     "lastName"=>"Gordon",
                                     "title"=>"Dr."), 
                     "leader" => array("userID"=>"tfead",
                                       "email"=>"libero@antebladitviverra.net",
                                       "firstName"=>"Yuli",
                                       "lastName"=>"Burris",
                                       "title"=>"Dr."), 
                     "sheetID" => ""));
// construct a content element for managing groups
$manageGroup = new ManageGroupSheet();

// construct a content element for creating groups
$createGroup = new InviteGroupSheet();

// construct a content element for joining groups
$invitations = new InvitationsGroupSheet($invitations);

// wrap all the elements in some HTML and show them on the page
$w = new HTMLWrapper($h, $manageGroup, $createGroup, $invitations);
$w->show();
?>

