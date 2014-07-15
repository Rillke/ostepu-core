<?php


/**
 * @file Pdf.php contains the Pdf class
 */

/**
 * the pdf structure
 *
 * @author Till Uhlig
 * @date 2014
 */
class Pdf extends Object implements JsonSerializable
{

    private $text = null;
    public function getText( )
    {
        return $this->text;
    }
    public function setText( $value = null )
    {
        $this->text = $value;
    }

    private $orientation = null;
    public function getOrientation( )
    {
        return $this->orientation;
    }
    public function setOrientation( $value = null )
    {
        $this->orientation = $value;
    }
    
    private $font = null;
    public function getFont( )
    {
        return $this->font;
    }
    public function setFont( $value = null )
    {
        $this->font = $value;
    }
    
    private $fontSize = null;
    public function getFontSize( )
    {
        return $this->fontSize;
    }
    public function setFontSize( $value = null )
    {
        $this->fontSize = $value;
    }
    
    private $textColor = null;
    public function getTextColor( )
    {
        return $this->textColor;
    }
    public function setTextColor( $value = null )
    {
        $this->textColor = $value;
    }
    
    private $subject = null;
    public function getSubject( )
    {
        return $this->subject;
    }
    public function setSubject( $value = null )
    {
        $this->subject = $value;
    }
    
    private $title = null;
    public function getTitle( )
    {
        return $this->title;
    }
    public function setTitle( $value = null )
    {
        $this->title = $value;
    }
    
    private $author = null;
    public function getAuthor( )
    {
        return $this->author;
    }
    public function setAuthor( $value = null )
    {
        $this->author = $value;
    }
    
    private $format = null;
    public function getFormat( )
    {
        return $this->format;
    }
    public function setFormat( $value = null )
    {
        $this->format = $value;
    }
    
    private $creator = null;
    public function getCreator( )
    {
        return $this->creator;
    }
    public function setCreator( $value = null )
    {
        $this->creator = $value;
    }
    
    public static function getOrientationDefinition( )
    {
        return array( 
                     'P' => 'Hochformat',

                     'L' => 'Querformat'
        
                     );
    }

    /**
     * Creates an Exercise object, for database post(insert) and put(update).
     * Not needed attributes can be set to null.
     *
     * @param string $exerciseId The id of the exercise.
     * @param string $courseId The id of the course. (do not use!)
     * @param string $sheetId The id of the exercise sheet.
     * @param string $maxPoints the max points
     * @param string $type the id of the exercise type
     * @param string $link the id of the exercise, this exercise belongs to
     * @param string $linkName the name of the sub exercise.
     * @param string $bonus the bonus flag
     *
     * @return an exercise object
     */
    public static function createPdf(
                                          $text,
                                          $orientation=null,
                                          $font=null,
                                          $fontSize=null,
                                          $textColor=null,
                                          $subject=null,
                                          $title=null,
                                          $author=null,
                                          $format=null,
                                          $creator=null
                                          )
    {
        return new Pdf( array(
                                   'text' => $text,
                                   'orientation' => $orientation,
                                   'font' => $font,
                                   'fontSize' => $fontSize,
                                   'textColor' => $textColor,
                                   'subject' => $subject,
                                   'title' => $title,
                                   'author' => $author,
                                   'format' => $format,
                                   'creator' => $creator
                                   ) );
    }
    
    /**
     * the constructor
     *
     * @param $data an assoc array with the object informations
     */
    public function __construct( $data = array( ) )
    {
        foreach ( $data AS $key => $value ){
            if ( isset( $key ) ){
                $func = 'set' . strtoupper($key[0]).substr($key,1);
                $methodVariable = array($this, $func);
                if (is_callable($methodVariable)){
                    $this->$func($value);
                } else
                    $this->{$key} = $value;
            }
        }
    }

    /**
     * encodes an object to json
     *
     * @param $data the object
     *
     * @return the json encoded object
     */
    public static function encodePdf( $data )
    {
        return json_encode( $data );
    }

    /**
     * decodes $data to an object
     *
     * @param string $data json encoded data (decode=true)
     * or json decoded data (decode=false)
     * @param bool $decode specifies whether the data must be decoded
     *
     * @return the object
     */
    public static function decodePdf(
                                          $data,
                                          $decode = true
                                          )
    {
        if ( $decode &&
             $data == null )
            $data = '{}';

        if ( $decode )
            $data = json_decode( $data );
        if ( is_array( $data ) ){
            $result = array( );
            foreach ( $data AS $key => $value ){
                $result[] = new Pdf( $value );
            }
            return $result;

        } else
            return new Pdf( $data );
    }

    /**
     * the json serialize function
     *
     * @return an array to serialize the object
     */
    public function jsonSerialize( )
    {
        $list = array( );
        if ( $this->text !== null )
            $list['text'] = $this->text;
        if ( $this->orientation !== null )
            $list['orientation'] = $this->orientation;
        if ( $this->font !== null )
            $list['font'] = $this->font;
        if ( $this->fontSize !== null )
            $list['fontSize'] = $this->fontSize;
        if ( $this->textColor !== null )
            $list['textColor'] = $this->textColor;
        if ( $this->subject !== null )
            $list['subject'] = $this->subject;
        if ( $this->title !== null )
            $list['title'] = $this->title;
        if ( $this->author !== null )
            $list['author'] = $this->author;
        if ( $this->format !== null )
            $list['format'] = $this->format;
        if ( $this->creator !== null )
            $list['creator'] = $this->creator;
        return array_merge($list,parent::jsonSerialize( ));
    }
}

?>