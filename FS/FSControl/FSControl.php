<?php
/**
 * @file FSControl.php contains the FSControl class
 * 
 * @author Till Uhlig
 * @author Felix Schmidt
 */ 
 
require_once('Include/Slim/Slim.php');
include_once('Include/Structures.php');
include_once('Include/CConfig.php');
include_once('Include/Request.php');
include_once('Include/Controller.php');

/**
 * The controller of the filesystem.
 */
class FSControl extends Controller
{
    /**
     * @var string $_prefix the prefixes the class works with (comma-separated)
     */
    protected static $_prefix = "";

    /**
     * the $_prefix getter
     *
     * @return the value of $_prefix
     */ 
    public static function getPrefix()
    {
        return FSControl::$_prefix;
    }

    /**
     * the $_prefix setter
     *
     * @param string $value the new value for $_prefix
     */ 
    public static function setPrefix($value)
    {
        FSControl::$_prefix = $value;
    }
}

\Slim\Slim::registerAutoloader();

// runs the CConfig
$com = new CConfig(FSControl::getPrefix()); 

// runs the FSControl
if (!$com->used())
    new FSControl($com->loadConfig());
?>