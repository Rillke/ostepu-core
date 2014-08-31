<?php
/**
 * @file LTutor.php Contains the LTutor class
 *
 * @author Peter Koenig
 * @author Christian Elze
 * @author Martin Daute
 * @date 2013-2014
 */

require '../../Assistants/Slim/Slim.php';
include '../../Assistants/Request.php';
include_once '../../Assistants/CConfig.php';

\Slim\Slim::registerAutoloader();

/**
 * The LTutor class
 *
 * This class handles everything belongs to TutorAssignments
 */
class LTutor
{
    /**
     * @var Component $_conf the component data object
     */
    private $_conf=null;

    /**
     * @var string $_prefix the prefix, the class works with
     */
    private static $_prefix = "tutor";

    /**
     * the $_prefix getter
     *
     * @return the value of $_prefix
     */
    public static function getPrefix()
    {
        return LTutor::$_prefix;
    }

    /**
     * the $_prefix setter
     *
     * @param string $value the new value for $_prefix
     */
    public static function setPrefix($value)
    {
        LTutor::$_prefix = $value;
    }

    /**
     * @var string $lURL the URL of the logic-controller
     */
    private $lURL = ""; //aus config lesen
    
    private $_postTransaction = array();

    /**
     * REST actions
     *
     * This function contains the REST actions with the assignments to
     * the functions.
     *
     * @param Component $conf component data
     */
    public function __construct()
    {
        // runs the CConfig
        $com = new CConfig( LTutor::getPrefix( ) );

        // runs the LTutor
        if ( $com->used( ) ) return;
            $conf = $com->loadConfig( );
            
        /**
         *Initialise the Slim-Framework
         */
        $this->app = new \Slim\Slim();
        $this->app->response->headers->set('Content-Type', 'application/json');

        /**
         *Set the Logiccontroller-URL
         */
        $this->_conf = $conf;
        $this->query = array();
        $this->query = CConfig::getLink($conf->getLinks(),"controller");
        
        $this->_postTransaction = array( CConfig::getLink( 
                                                        $this->_conf->getLinks( ),
                                                        'postTransaction'
                                                        ) );

        // initialize lURL
        $this->lURL = $this->query->getAddress();

        //Set auto allocation by exercise
        $this->app->post('/'.$this->getPrefix().
            '/auto/exercise/course/:courseid/exercisesheet/:sheetid(/)',
                array($this, 'autoAllocateByExercise'));

        //Set auto allocation by group
        $this->app->post('/'.$this->getPrefix().
            '/auto/group/course/:courseid/exercisesheet/:sheetid(/)',
                array($this, 'autoAllocateByGroup'));

        //Get zip
        $this->app->get('/'.$this->getPrefix().'/user/:userid/exercisesheet/:sheetid(/)',
                array($this, 'getZip'));

        //uploadZip
        $this->app->post('/'.$this->getPrefix().'/user/:userid/exercisesheet/:sheetid(/)', array($this, 'uploadZip'));

        //run Slim
        $this->app->run();
    }

    /**
     * Function to auto allocate exercises to tutors
     *
     * This function takes two arguments and returns a status code.
     *
     * @param $courseid an integer identifies the course
     * @param $sheetid an integer identifies the exercisesheet
     */
    public function autoAllocateByExercise($courseid, $sheetid){
        $header = $this->app->request->headers->all();
        $body = json_decode($this->app->request->getBody(), true);
        $URL = $this->lURL.'/DB/marking';

        $error = false;

        $tutors = $body['tutors'];
        $submissions = array();
        foreach($body['unassigned'] as $submission){
            $exerciseId = $submission['exerciseId'];
            $submissions[$exerciseId][] = $submission;
        }

        //randomized allocation
        shuffle($tutors);
        shuffle($submissions);

        $i = 0;
        $numberOfTutors = count($tutors);
        $markings = array();
        foreach ($submissions as $submissionsByExercise){
            foreach($submissionsByExercise as $submission){
                $newMarking = array(
                    'submission' => $submission,
                    'status' => 0,
                    'tutorId' => $tutors[$i]['tutorId'],
                );
                //adds a submission to a tutor
                $markings[] = $newMarking;
            }
            if ($i < $numberOfTutors - 1){
                $i++;
            } else {
                $i = 0;
            }

        }

        //requests to database
        foreach($markings as $marking){
            $answer = Request::custom('POST', $URL, $header,
                    json_encode($marking));
            if ($answer['status'] >= 300){
                $error = true;
                $errorstatus = $answer['status'];
            }
        }
        // response
        if ($error == false){
            $this->app->response->setStatus(201);
            $this->app->response->setBody("");
        } else {
            $this->app->response->setStatus($errorstatus);
            $this->app->response->setBody("Warning: At least one exercise was not being allocated!");
        }

      //  $URL = $this->lURL.'/getSite/tutorassign/user/3/course/'
      //                  .$courseid.'/exercisesheet/'.$sheetid;
      //  $answer = Request::custom('GET', $URL, $header, "");
      //  $this->app->response->setBody($answer['content']);
    }

    /**
     * Function to auto allocate groups to tutors
     *
     * It takes two argument and returns a Status-Code.
     *
     * @param $courseid an integer identifies the course
     * @param $sheetid an integer identifies the exercisesheet
     */
    public function autoAllocateByGroup($courseid, $sheetid){

        $header = $this->app->request->headers->all();
        $body = json_decode($this->app->request->getBody(), true);
        $URL = $this->lURL.'/DB/marking';

        $error = false;

        $tutors = $body['tutors'];
        $submissions = array();
        foreach($body['unassigned'] as $submission){
            $leaderId = $submission['leaderId'];
            $submissions[$leaderId][] = $submission;
        }

        //randomized allocation
        shuffle($tutors);

        $i = 0;
        $numberOfTutors = count($tutors);
        $markings = array();
        foreach ($submissions as $submissionsByGroup){
            foreach($submissionsByGroup as $submission){
                $newMarking = array(
                    'submission' => $submission,
                    'status' => 0,
                    'tutorId' => $tutors[$i]['tutorId']
                );
                //adds a submission to a tutor
                $markings[] = $newMarking;
            }
            if ($i < $numberOfTutors - 1){
                $i++;
            } else {
                $i = 0;
            }

        }

        //requests to database
        foreach($markings as $marking){
            $answer = Request::custom('POST', $URL, $header,
                    json_encode($marking));
            if ($answer['status'] >= 300){
                $error = true;
                $errorstatus = $answer['status'];
            }
        }
        // response
        if ($error == false){
            $this->app->response->setStatus(201);
            $this->app->response->setBody("");
        } else {
            $this->app->response->setStatus($errorstatus);
            $this->app->response->setBody("Warning: At least one group was not being allocated!");
        }

       // $URL = $this->lURL.'/getsite/tutorassignment/course/'
       //             .$courseid.'/exercisesheet/'.$sheetid;
       // $answer = Request::custom('GET', $URL, $header, "");
       //
       // $this->app->response->setBody($answer['content']);
    }

    /**
     * Function to get a zip with csv
     *
     * It takes two arguments and returns a zip with folders named a
     * exercise-ID and contains PDF's named the marking-ID. Informations
     * for each marking is written in a CSV-file in the root of the zip.
     *
     * @param $userid an integer identifies the user (tutor)
     * @param $sheetid an integer identifies the exercisesheet
     */
    public function getZip($userid, $sheetid){
        $header = $this->app->request->headers->all();
        $body = json_decode($this->app->request->getBody());

        $URL = $this->lURL.'/DB/marking/exercisesheet/'.$sheetid.'/tutor/'.$userid;
        //request to database to get the markings
        $answer = Request::custom('GET', $URL, $header,"");
        $markings = json_decode($answer['content'], true);

        $URL = $this->lURL.'/DB/exercise/exercisesheet/'.$sheetid;
        //request to database to get the exercise sheets
        $answer = Request::custom('GET', $URL, $header,"");
        $exercises = json_decode($answer['content'], true);

        $count = 0;
        //an array to descripe the subtasks
        $alphabet = range('a', 'z');
        $secondRow = array();
        $sortedMarkings = array();
        $rows = array();
        $exerciseIdWithExistingMarkings = array();
        $namesOfExercises = array();

        //exercises with informations of marking and submissions
        //sorted by exercise ID and checked of existence
        foreach( $markings as $marking){
            $submission = $marking['submission'];
            $id = $submission['exerciseId'];
            $sortedMarkings[$id][] = $marking;
            if(!in_array($id, $exerciseIdWithExistingMarkings)){
                $exerciseIdWithExistingMarkings[] = $id;
            }
        }

        //formating, create the layout of the CSV-file for the tutor
        //first two rows of an exercise are the heads of the table
        foreach ($exercises as $exercise){
            $firstRow = array();
            $secondRow = array();
            $row = array();

            if ($exercise != $exercise['link']){
                $count++;
                $firstRow[] = 'Aufgabe '.$count;
                $int = $exercise['id'];
                $namesOfExercises[$int] = 'Aufgabe '.$count;
                $subtask = 0;
            }else{
                $firstRow[] = 'Aufgabe '.$count.$alphabet[$subtask];
                $int = $exercise['id'];
                $namesOfExercises[$int] = 'Aufgabe '.$count.$alphabet[$subtask];
                $subtask++;
            }
            $firstRow[] = $exercise['id'];
            $secondRow[] = 'ID';
            $secondRow[] = 'Points';
            $secondRow[] = 'MaxPoints';
            $secondRow[] = 'Outstanding?';
            $secondRow[] = 'Status';
            $secondRow[] = 'TutorComment';
            $secondRow[] = 'StudentComment';

            //formating, write known informations of the markings in the CSV-file
            //after the second row to each exercise
            if(in_array($exercise['id'], $exerciseIdWithExistingMarkings)){
                $rows[] = $firstRow;
                $rows[] = $secondRow;
                foreach($sortedMarkings[$exercise['id']] as $marking){
                    $row = array();
                    //MarkingId
                    if (!isset($marking['id'])) continue;
                    $row[] = $marking['id'];
                    
                    //Points{
                    $row[] = (isset($marking['points']) ? $marking['points'] : '0');

                    //MaxPoints
                    $row[] = (isset($marking['maxPoints']) ? $exercise['maxPoints'] : '0');
                    
                    //Outstanding
                    $row[] = (isset($marking['outstanding']) ? $marking['outstanding'] : '');

                    //Status
                    $row[] = (isset($marking['status']) ? $marking['status'] : '0');

                    //TutorComment
                    $row[] = (isset($marking['tutorComment']) ? $marking['tutorComment'] : '');

                    //StudentComment
                    if (isset($marking['submission'])){
                        $submission = $marking['submission'];
                        $row[] = (isset($submission['comment']) ? $submission['comment'] : '');
                    }

                    $rows[] = $row;
                }
                //an empty row after an exercise
                $rows[] = array();
            }

        }

        //request to database to get the user name of the tutor for the
        //name of the CSV-file
        $URL = $this->lURL.'/DB/user/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, "");
        $user = json_decode($answer['content'], true);
        
        // create transaction ticket
        $transaction = Transaction::createTransaction(
                                                      null,
                                                      (time() + (30 * 24 * 60 * 60)),
                                                      'TutorCSV_'.$userid.'_'.$sheetid,
                                                      json_encode($rows)
                                                      );
        $result = Request::routeRequest(
                                        'POST',
                                        '/transaction/exercisesheet/'.$sheetid,
                                        array(),
                                        Transaction::encodeTransaction($transaction),
                                        $this->_postTransaction,
                                        'transaction'
                                        );

        // checks the correctness of the query
        if ( isset($result['status']) && isset($result['content']) && $result['status'] == 201){
            $transaction = Transaction::decodeTransaction($result['content']);
            $rows[0][] = $transaction->getTransactionId(); 
             
            $this->deleteDir("./csv");
            mkdir("./csv");        

            //this is the true writing of the CSV-file named [tutorname]_[sheetid].csv
            $CSV = fopen('./csv/'.$user['lastName'].'_'.$sheetid.'.csv', 'w');

            foreach($rows as $row){
                fputcsv($CSV, $row, ';');
            }

            fclose($CSV);
            
            
            //Create Zip
            $filesToZip = array();
            //Push all SubmissionFiles to an array in order of exercises
            foreach( $exercises as $exercise){
                $exerciseId = $exercise['id'];
                if(in_array($exercise['id'], $exerciseIdWithExistingMarkings)){
                    foreach($sortedMarkings[$exerciseId] as $marking){
                        $URL = $this->lURL.'/DB/submission/submission/'.
                                                $marking['submission']['id'];
                                                
                        //request to database to get the submission file
                        $answer = Request::custom('GET', $URL, $header,"");
                        $submission = json_decode($answer['content'], true);

                        $newfile = $submission['file'];

                        $newfile['displayName'] =
                            $namesOfExercises[$exerciseId].'/'.$marking['id'].'.pdf';

                        $filesToZip[] = $newfile;
                    }
                }
            }


            //push the .csv-file to the array
            $path = './csv/'.$user['lastName'].'_'.$sheetid.'.csv';
            $csvFile = array(
                        'displayName' => $user['lastName'].'_'.$sheetid.'.csv',
                        'body' => base64_encode(file_get_contents($path))
                    );
            $filesToZip[] = $csvFile;

            $URL = $this->lURL.'/FS/zip';
            //request to filesystem to create the Zip-File
            $answer = Request::custom('POST', $URL, $header,json_encode($filesToZip));
            $zipFile = json_decode($answer['content'], true);
            $URL = $this->lURL.'/FS/'.$zipFile['address'].'/'.$userid.'_'.$sheetid.'.zip';
            //request to filesystem to get the created Zip-File
            $answer = Request::custom('GET', $URL, $header,"");

            if (isset($answer['headers']['Content-Type']))
                $this->app->response->headers->set('Content-Type', $answer['headers']['Content-Type']);
            
            if (isset($answer['headers']['Content-Disposition']))
                $this->app->response->headers->set('Content-Disposition', $answer['headers']['Content-Disposition']);
            $this->app->response->setBody($answer['content']);
        } else {
            $this->app->response->setStatus(409);
        }
        
    }

    // @todo use LFile to upload markings
    // @todo use parallel requests
    public function uploadZip($userid, $sheetid){
        // error array of strings
        $errors = array();

        $header = $this->app->request->headers->all();
        $body = json_decode($this->app->request->getBody(), true); //1 file-Object

        $URL = $this->lURL.'/DB/user/'.$userid;
        //request to database to get the tutor
        $answer = Request::custom('GET', $URL, $header,"");
        $user = json_decode($answer['content'], true);

        $filename = $user['userName'].'.zip';
        file_put_contents($filename, base64_decode($body['body']));
        $zip = new ZipArchive();
        $zip->open($filename);
        $zip->extractTo('./'.$userid.'/');
        $zip->close();
        $this->deleteDir($filename);
        // check if csv file exists
        if (file_exists('./'.$userid.'/'.$user['lastName'].'_'.$sheetid.'.csv')){
            $csv = fopen('./'.$userid.'/'.$user['lastName'].'_'.$sheetid.'.csv', "r");
            while (($row = fgetcsv($csv)) !== false){
                $row = explode(";", $row[0]);
                if($row[0] != ""){
                    if($row[0][0] == "A"){
                        $exerciseName = $row[0];
                    } elseif(!($row[0] == "ID")){
                        // check if file with this markingid exists
                        if (file_exists('./'.$userid.'/'.$exerciseName.'/'.$row[0].'.pdf')) {
                            $fileBody = file_get_contents('./'.$userid.'/'.$exerciseName.'/'.$row[0].'.pdf');
                            $file = array(
                                    'displayName' => $exerciseName.'_'.$row[0].'.pdf',
                                    'body' => base64_encode($fileBody),
                                    );

                            $URL = $this->lURL.'/FS/file';
                            //request to filesystem to save the marking file
                            $answer = Request::custom('POST', $URL, $header,json_encode($file));
                            $file = json_decode($answer['content'], true);
                            //request to database file table to save the marking file
                            $URL = $this->lURL.'/DB/file/hash/'.$file['hash'];
                            $answer = Request::custom('GET', $URL, $header, "");
                            $markingFile = json_decode($answer['content'], true);
                            if ($answer == "[]"){       //if file does not exists, POST-Request to add it
                                $URL = $this->lURL.'/DB/file';
                                $answer = Request::custom('POST', $URL, $header, json_encode($file));
                                $markingFile = json_decode($answer['content'], true);
                            }
                            // create new marking object
                            $marking = array(
                                    'id' => $row[0],
                                    'points' => $row[1],
                                    'outstanding' => $row[3],
                                    'tutorId' => $userid,
                                    'tutorComment' => $row[5],
                                    'file' => $markingFile,
                                    'status' => $row[4],
                                    );

                            $URL = $this->lURL.'/DB/marking/'.$marking['id'];
                            //request to database to edit the marking
                            $answer = Request::custom('PUT', $URL, $header,json_encode($marking));
                            if ($answer['status'] >= 300) {
                                $errors[] = 'error in csv file in table '.$exerciseName.' row with ID '.$row[0];
                            }

                        } else { //if file with this markingid not exists
                            $errors[] = 'File does not exist: '.$exerciseName.'/'.$row[0].'.pdf';
                        }

                    }
                }
            }
            fclose($csv);
        } else { // if csv file does not exist
            $errors[] = '.csv file does not exist in uploaded zip-Archiv';
        }
        $this->deleteDir('./'.$userid);

        $this->app->response->setBody(json_encode($errors));
        if (!($errors == array())){
            $this->app->response->setStatus(409);
        }

    }

    /**
    * Delete hole directory inclusiv files and dirs
    *
    * @param string $path
    * @return boolean
    */
    public function deleteDir($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $this->deleteDir(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        }

        // Datei entfernen
        else if (is_file($path) === true) {
            return unlink($path);
        }
        return false;
    }

}

?>