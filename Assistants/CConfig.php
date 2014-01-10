<?php
/**
* @file CConfig.php contains the CConfig class
*/ 
include_once( 'DBRequest.php' );


/**
* (description)
 *
 * @author Till Uhlig
*/
class CConfig
{
    private $_app;
    private $CONF_FILE = "CConfig.json";
    private $_prefix = "";
    private $_used = false;
    
    public function getPrefix()
    {
        return $this->_prefix;
    }
    
    public function setPrefix($value)
    {
        $this->_prefix = $value;
    }
    
    /**
     * the CConfig constructor
     *
     * @param $prefix the prefix, the component works with
     */ 
    public function __construct($prefix)
    {
        $this->setPrefix($prefix);
        $this->_app = new \Slim\Slim();

        $this->_app->response->headers->set('Content-Type' ,
                                            'application/json');
        
        // POST Config
        $this->_app->post('/component' , array($this , 'postConfig'));
        
        // GET Config
        $this->_app->get('/component' , array($this , 'getConfig'));
        
        if ($this->_app->request->getResourceUri() == "/component"){
        
            // run Slim
            $this->_used = true;
            $this->_app->run();
        } else {
            $conf = $this->loadConfig();
            $links = $conf->getLinks();
            $changed = false;
            foreach ($links as &$link){
                if ($link->getPrefix()===null){
                    $result = Request::get($link->getAddress() . '/component',array(),"");
                    
                    if ($result['status'] == 200){
                        $changed = true;
                        $obj = Component::decodeComponent($result['content']);
                        $link->setPrefix($obj->getPrefix());
                    }        
                }
            }

            if ($changed){
                $conf->setLinks($links);
                $this->saveConfig(Component::encodeComponent($conf));
            }
        }
    }
    
    /**
     * (description)
     */
    public function used()
    {
        return $this->_used;   
    }
    
    /**
     * POST Config
     */
    public function postConfig()
    {
        $this->_app->response->setStatus(451);
        $body = $this->_app->request->getBody();
        $Component = Component::decodeComponent($body);
        $Component->setPrefix($this->getPrefix());
        $this->saveConfig(json_encode($Component));
        $this->_app->response->setStatus(201);
    }
    
    /**
     * GET Config
     *
     * @return 
     */
    public function getConfig()
    {
        if (file_exists($this->CONF_FILE)){
            $com = Component::decodeComponent(file_get_contents($this->CONF_FILE));
            $com->setPrefix($this->getPrefix());
            $this->_app->response->setBody(Component::encodeComponent($com));
            $this->_app->response->setStatus(200);
        } else{
            $this->_app->response->setStatus(409);
            $com = new Component();
            $com->setPrefix($this->getPrefix());
            $this->_app->response->setBody(Component::encodeComponent($com));   
        }
    }
    
    /**
     * (description)
     *
     * @param $content (description)
     */
    public function saveConfig($content)
    {
        $file = fopen($this->CONF_FILE,"w");        
        fwrite($file, $content);
        fclose($file);
    }
    
    /**
     * (description)
     *
     * @return 
     */
    public function loadConfig()
    { 
        if (file_exists($this->CONF_FILE)){
            $com = Component::decodeComponent(file_get_contents($this->CONF_FILE));
            $com->setPrefix($this->getPrefix());
            return $com;
        } else{
            $com = new Component();
            $com->setPrefix($this->getPrefix());
            return $com;        
        }
    }
    
    /**
     * (description)
     *
     * @param $linkList (description)
     * @param $name (description)
     *
     * @return 
     */
    public static function getLink($linkList,$name)
    {
        foreach ($linkList as $link){
            if ($link->getName() == $name)
                return $link;
        }

        return null;
    }
    
    /**
     * (description)
     *
     * @param $linkList (description)
     * @param $name (description)
     *
     * @return 
     */
    public static function getLinks($linkList,$name)
    {
        $result = array();
        foreach ($linkList as $link){
            if ($link->getName() == $name)
                array_push($result, $link);
        }

        return $result;
    }
    
    /**
     * (description)
     *
     * @param $links (description)
     * @param $linkName (description)
     *
     * @return 
     */
    public static function deleteFromArray($links, $linkName)
    {
        $result = array();
        foreach($links as $link)
        {
            if($link->getName() != $linkName)
            {
                array_push($result,$link); 
            }
        }
        return $result;
    }
    
}

?>