<?php
/**
 * @file (filename)
 * (description)
 */ 
include 'Request/CreateRequest.php';
include 'Request/MultiRequest.php';

/**
 * (description)
 */
class Request
{
    /**
     * (description)
     *
     * @param $target (description)
     * @param $method (description)
     * @param $content (description)
     */
    public static function custom($method, $target, $header,  $content)
    {
        $ch = Request_CreateRequest::createCustom($method,$target,$header,$content);
        $content = curl_exec($ch);
          
        $result = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['headers'] = array();
        $head = explode("\r\n",substr($content, 0, $header_size));
        foreach ($head as $k){
            $value = split(": ",$k);
            $result['headers'][$value[0]] = $k;
        }

        $result['content'] = substr($content, $header_size);
        $result['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 
        return $result; 
    }
       
     
    /**
     * (description)
     *
     * @param $target (description)
     * @param $method (description)
     * @param $content (description)
     */
    public static function post($target, $header,  $content)
    {
        return Request::custom("POST", $target , $header, $content); 
    }
    
    
    /**
     * (description)
     *
     * @param $target (description)
     * @param $method (description)
     * @param $content (description)
     */
    public static function get($target, $header,  $content)
    {
        return Request::custom("GET", $target, $header, $content); 
    }
    
    
    /**
     * (description)
     *
     * @param $target (description)
     * @param $method (description)
     * @param $content (description)
     */
    public static function delete($target, $header,  $content)
    {
        return Request::custom("DELETE", $target, $header, $content); 
    } 
    
    /**
     * (description)
     *
     * @param $target (description)
     * @param $method (description)
     * @param $content (description)
     */
    public static function put($target, $header,  $content)
    {
        return Request::custom("PUT", $target, $header, $content); 
    } 
    
    /**
     * (description)
     *
     * @param $method (description)
     * @param $resourceUri (description)
     * @param $header (description)
     * @param $content (description)
     * @param $linkedComponents (description)
     * @param $prefix (description)
     * @param $linkName (description)
     */
    public static function routeRequest($method , $resourceUri , $header ,  $content , $linkedComponents , $prefix, $linkName=NULL)
    {
        // get possible links
        
        $else = array();
        foreach ($linkedComponents as $links){
            if ($linkName!=NULL && $linkName!=$links->getName())
                continue;
                
            $possible = explode(',',$links->getPrefix());
            if (in_array($prefix,$possible)){
                $ch = Request::custom($method,
                                      $links->getAddress().$resourceUri,
                                      $header,
                                      $content);
                if ($ch['status']>=200 && $ch['status']<=299){
                    // finished
                    return $ch;
                }
                                     
            } elseif(in_array("",$possible)){
                array_push($else, $links);
            } 
        }
        
        foreach ($else as $links)
        {
            $ch = Request::custom($method,
                                  $links->getAddress().$resourceUri,
                                  $header,
                                  $content);
                                  
            if ($ch['status']>=200 && $ch['status']<=299){
                // finished
                return $ch;
            }
        }
        
        // no positive response or no operative link
        $ch = array();
        $ch['status'] = 404;
        return $ch;
    }

}   
?>