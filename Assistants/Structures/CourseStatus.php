<?php 
/**
* A pair of a course and a status for some user.
* The status reflects the rights the particular user has in that
* course
*/
class CourseStatus extends Object implements JsonSerializable
{
    /**
     * A course.
     *
     * type: Course
     */
    private $course;
    
    /**
     * (description)
     */
    public function getCourse()
    {
        return $this->course;
    }
    
    /**
     * (description)
     *
     * @param $param (description)
     */
    public function setCourse($value)
    {
        $this->course = $value;
    }

    
    
    
    /**
     * a string that defines which status the user has in that course.
     *
     * type: string
     */
    private $status;
    
    /**
     * (description)
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * (description)
     *
     * @param $param (description)
     */
    public function setStatus($value)
    {
        $this->status = $value;
    }

    
    
    
    /**
     * (description)
     */
    public static function getDbConvert()
    {
        return array(
           'C_course' => 'course',
           'C_name' => 'status',
        );
    }
    
    /**
     * (description)
     */
    // TODO: hier fehlt noch der primary key/keys
    public static function getDbPrimaryKey()
    {
        return 'C_id';
    }
    
    /**
     * (description)
     */
    public static function getDefinition(){
        return array(
            '0' => 'inactive',
            '1' => 'active',
        );
    }
   
    /**
     * (description)
     * 
     * @param $param (description)
     */
    public function __construct($data=array()) 
    {
        foreach ($data AS $key => $value) {
            if (isset($key)){
                $this->{$key} = $value;
            }
        }
    }
    
    /**
     * (description)
     *
     * @param $param (description)
     */
    public static function encodeCourseStatus($data)
    {
        return json_encode($data);
    }
    
    /**
     * (description)
     * 
     * @param $param (description)
     * @param $param (description)
     */
    public static function decodeCourseStatus($data, $decode=true)
    {
        if ($decode)
            $data = json_decode($data);
        if (is_array($data)){
            $result = array();
            foreach ($data AS $key => $value) {
                array_push($result, new CourseStatus($value));
            }
            return $result;   
        } else
            return new CourseStatus($data);
    }
    
    /**
     * (description)
     */
    public function jsonSerialize()
    {
        return array(
            'course' => $this->course,
            'status' => $this->status
        );
    }
}
?>