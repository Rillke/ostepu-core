<?php
/**
 * @file Admin.php
 * Constructs the page that is displayed to an admin.
 *
 * @author Felix Schmidt
 * @author Florian Lücke
 * @author Ralf Busch
 */

include_once dirname(__FILE__).'/include/Boilerplate.php';
include_once dirname(__FILE__).'/../Assistants/Structures.php';
include_once dirname(__FILE__).'/../Assistants/LArraySorter.php';

global $globalUserData;
Authentication::checkRights(PRIVILEGE_LEVEL::ADMIN, $cid, $uid, $globalUserData);

$langTemplate='Admin_Controller';Language::loadLanguageFile('de', $langTemplate, 'json', dirname(__FILE__).'/');

$sheetNotifications = array();

unset($_SESSION['selectedUser']);

if (isset($_POST['action'])) {     
    if ($_POST['action'] == "ExerciseSheetLecturer" && isset($_POST['deleteSheetWarning'])) {
        $sheetNotifications[$_POST['deleteSheetWarning']][] = MakeNotification("warning", Language::Get('main','askDeleteSubmission', $langTemplate));
    } elseif ($_POST['action'] == "ExerciseSheetLecturer" && isset($_POST['deleteSheet'])) {
        $URL = $logicURI . "/exercisesheet/exercisesheet/{$_POST['deleteSheet']}";
        $result = http_delete($URL, true, $message);
        
        if ($message == 201){
            $sheetNotifications[$_POST['deleteSheet']][] = MakeNotification('success', Language::Get('main','successDeleteSubmission', $langTemplate));
        } else 
            $sheetNotifications[$_POST['deleteSheet']][] = MakeNotification('error', Language::Get('main','errorDeleteSubmission', $langTemplate));
    }
}

// load GetSite data for Admin.php
$URL = $getSiteURI . "/admin/user/{$uid}/course/{$cid}";
$admin_data = http_get($URL, true);
$admin_data = json_decode($admin_data, true);
$admin_data['filesystemURI'] = $filesystemURI;
$admin_data['cid'] = $cid;

$user_course_data = $admin_data['user'];

$menu = MakeNavigationElement($user_course_data,
                              PRIVILEGE_LEVEL::ADMIN);

// construct a new header
$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind($user_course_data);
$h->bind(array("name" => $user_course_data['courses'][0]['course']['name'],
               "backTitle" => Language::Get('main','changeCourse', $langTemplate),
               "backURL" => "index.php",
               "notificationElements" => $notifications,
               "navigationElement" => $menu));


$t = Template::WithTemplateFile('include/ExerciseSheet/ExerciseSheetLecturer.template.html');
$t->bind($admin_data);
if (isset($sheetNotifications))
    $t->bind(array("SheetNotificationElements" => $sheetNotifications));
    
$w = new HTMLWrapper($h, $t);
$w->defineForm(basename(__FILE__)."?cid=".$cid, false, $t);
$w->set_config_file('include/configs/config_admin_lecturer.json');
$w->show();
