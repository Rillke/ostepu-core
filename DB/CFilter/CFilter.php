<?php 


/**
 * @file CFilter.php contains the CFilter class
 *
 * @author Till Uhlig
 * @date 2013-2014
 */

require_once ( '../../Assistants/Slim/Slim.php' );
include_once ( '../../Assistants/Structures.php' );
include_once ( '../../Assistants/CConfig.php' );
include_once ( '../../Assistants/Request.php' );
include_once ( '../../Assistants/Controller2.php' );
include_once ( '../../Assistants/Logger.php' );

\Slim\Slim::registerAutoloader( );

/**
 * A class, to forwards requests into the heap of database components
 *
 * @author Till Uhlig
 */
class CFilter extends Controller2
{

    /**
     * @var string $_prefix the prefixes, the class works with (comma separated)
     */
    protected static $_prefix = '';

    /**
     * the $_prefix getter
     *
     * @return the value of $_prefix
     */
    public static function getPrefix( )
    {
        return CFilter::$_prefix;
    }

    /**
     * the $_prefix setter
     *
     * @param string $value the new value for $_prefix
     */
    public static function setPrefix( $value )
    {
        CFilter::$_prefix = $value;
    }
} 
?>

