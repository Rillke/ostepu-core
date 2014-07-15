<?php 


/**
 * @file LFile.php contains the LFile class
 *
 * @author Till Uhlig 
 * @date 2014
 */ 

require_once ( '../../Assistants/Slim/Slim.php' );
include_once ( '../../Assistants/CConfig.php' );
include_once ( '../../Assistants/Request.php' );
include_once ( '../../Assistants/Structures.php' );
include_once ( '../../Assistants/Logger.php' );

include_once ( './LFileHandler.php' );

\Slim\Slim::registerAutoloader( );

/**
 * The class for storing and hashing files.
 */
class LFile
{

    /**
     * @var string $_baseDir the root directory of this component.
     */
    private static $_baseDir = 'file';

    /**
     * the $_baseDir getter
     *
     * @return the value of $_baseDir
     */
    public static function getBaseDir( )
    {
        return LFile::$_baseDir;
    }

    /**
     * the $_baseDir setter
     *
     * @param string $value the new value for $_baseDir
     */
    public static function setBaseDir( $value )
    {
        LFile::$_baseDir = $value;
    }

    /**
     * @var Slim $_app the slim object
     */
    private $_app;

    /**
     * @var Component $_conf the component data object
     */
    private $_conf;

    /**
     * @var Link[] $_fs link to component which work with files, e.g. FSFile
     */
    private $_fs = array();
    
        /**
     * @var Link[] $_db link to component which work with files, e.g. DBFile
     */
    private $_db = array();

    /**
     * REST actions
     *
     * This function contains the REST actions with the assignments to
     * the functions.
     */
    public function __construct()
    {
        // runs the CConfig
        $com = new CConfig( LFile::$_baseDir );

        // runs the LFile
        if ( $com->used( ) ) return;
        $conf = $com->loadConfig( );
            
        $this->_conf = $conf;
        $this->_fs = CConfig::getLinks($this->_conf->getLinks( ),'file');
        $this->_db = CConfig::getLinks($this->_conf->getLinks( ),'fileDb');

        $this->_app = new \Slim\Slim( array( 'debug' => true ) );
        $this->_app->response->setStatus( 404 );

        $this->_app->response->headers->set( 
                                            'Content-Type',
                                            'application/json'
                                            );
                          
        // POST PathFile
        $this->_app->post( 
                          '/' . LFile::$_baseDir . '/:path+(/)',
                          array( 
                                $this,
                                'postPathFile'
                                )
                          );
                          
        // POST File
        $this->_app->post( 
                          '/' . LFile::$_baseDir . '(/)',
                          array( 
                                $this,
                                'postFile'
                                )
                          );
                            
        // DELETE File
        $this->_app->delete( 
                            '/' . LFile::$_baseDir. '(/)',
                            array( 
                                  $this,
                                  'deleteFile'
                                  )
                            );

        // run Slim
        $this->_app->run( );
    }
   
    /**
     * Adds a file.
     *
     * Called when this component receives an HTTP DELETE request to
     * /file/$path(/)
     *
     * @param String the path, where the file should be stored
     */
    public function postPathFile( $path)
    {
        Logger::Log( 
                'starts POST postFile',
                LogLevel::DEBUG
                );
                    
        $body = $this->_app->request->getBody( );
        $fileObjects = File::decodeFile( $body );
        
        $temp="";
        foreach ($path as $part){
        $temp .= $part.'/';
        }
        $path = $temp;

        // always been an array
        $arr = true;
        if ( !is_array( $fileObjects ) ){
            $fileObjects = array( $fileObjects );
            $arr = false;
        }

        $res = array( );

        foreach ( $fileObjects as $fileObject ){

            $result = LFileHandler::add($this->_db,$this->_fs,$path, array(),$fileObject);
            if ( $result !== null){
                $res[] = $result; 
            } else {
                $fileObject->addMessage("Die Datei konnte nicht gespeichert werden.");
                $res[] = $fileObject;
                
                Logger::Log( 
                    'POST postPathFile failed',
                    LogLevel::ERROR
                    );
                
                $this->_app->response->setStatus( 409 );
                $this->_app->response->setBody( File::encodeFile( $res ) );
                $this->_app->stop( );
            }
        }

        if ( !$arr && 
             count( $res ) == 1 )
            $res = $res[0];

        $this->_app->response->setStatus( 201 );
        $this->_app->response->setBody( File::encodeFile( $res ) );
    }
   
    /**
     * Adds a file.
     *
     * Called when this component receives an HTTP POST request to
     * /file(/)
     */
    public function postFile( )
    {
        $this->postPathFile(array(''));
    }

    /**
     * Deletes a file.
     *
     * Called when this component receives an HTTP DELETE request to
     * /file(/)
     */
    public function deleteFile( )
    {
        Logger::Log( 
                'starts Delete deleteFile',
                LogLevel::DEBUG
                );
                
        $body = $this->_app->request->getBody( );
        $fileObjects = File::decodeFile( $body );

        // always been an array
        $arr = true;
        if ( !is_array( $fileObjects ) ){
            $fileObjects = array( $fileObjects );
            $arr = false;
        }

        $res = array( );
        foreach ( $fileObjects as $fileObject ){
            if ($fileObject!==null && $fileObject !== array()){
                $result = LFileHandler::delete($this->_db, $this->_fs, array(), $fileObject);    
            } else
                $result = new File();
                
            if ( $result !== null){
                $res[] = $result; 
            } else {
                $uploadSubmission->getMessages()[] = ("Die Datei konnte nicht gelöscht werden.");
                $res[] = $fileObject;
                
                $this->_app->response->setStatus( 409 );
                $this->_app->response->setBody( File::encodeFile( $res ) );
                $this->_app->stop( );
            }
        }

        if ( !$arr && 
             count( $res ) == 1 )
            $res = $res[0];

        $this->_app->response->setStatus( 201 );
        $this->_app->response->setBody( File::encodeFile( $res ) );                        
    }
}

 
?>
