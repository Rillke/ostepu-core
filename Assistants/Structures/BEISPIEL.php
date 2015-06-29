<?php 

// fügt die Objektklasse hinzu, hier sind noch allgemeine Eigenschaften enthalten (Statuscode, Antworttext etc.)
include_once ( dirname( __FILE__ ) . '/Object.php' );

/**
 * @author Till Uhlig
 * @date 2015
 */
class BEISPIEL extends Object implements JsonSerializable // muss eingebunden werden, damit das Objekt serialisierbar wird
{

    // Attribute sollten stets über getParam und setParam angesprochen werden
    private $param = null;
    public function getParam( )
    {
        return $this->param;
    }
    public function setParam( $value = null )
    {
        $this->param = $value;
    }

    // diese Funktionen sollen das Erstellen neuer Objekte erleichtern, vorallem wenn 
    // die Strukturen aus verschiedenen Strukturen zusammengesetzt wurden und 
    // einzelne Felder für einen Datenbankeintrag benötigt werden
    public static function createBEISPIEL( $newParam )
    {
        return new BEISPIEL( array('param' => $param ) );
    }

    // wandelt Datenbankfelder namentlich in Objektattribute um 
    public static function getDbConvert( )
    {
        return array( 
                     'P_pa' => 'param'
                     );
    }

    // wandelt die gesetzten Attribute des Objekts in eine Zusammenstellung
    // für einen UPDATE oder INSERT Befehl einer MySql Anweisung um
    public function getInsertData( )
    {
        $values = '';

        if ( $this->param !== null )
            $this->addInsertData( 
                                 $values,
                                 'P_pa',
                                 DBJson::mysql_real_escape_string( $this->param )
                                 );

        if ( $values != '' ){
            $values = substr( 
                             $values,
                             1
                             );
        }
        return $values;
    }

    // gibt den primären Datenbankschlüssel (eventuell auch ein array) der Struktur zurück
    public static function getDbPrimaryKey( )
    {
        return'P_pa';
    }

    // ruft passende set() Funktionen des Objekts auf, um dessen Attribute zu belegen
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

    // wandelt ein solches Objekt in eine Textdarstellung um (Serialisierung)
    public static function encodeBEISPIEL( $data )
    {
        return json_encode( $data );
    }

    // wandelt die Textdarstellung des Objekts in ein Objekt um (Deserialisierung
    // ,behandelt auch Objektlisten
    public static function decodeBEISPIEL( 
                                                   $data,
                                                   $decode = true
                                                   )
    {
        if ( $decode && 
             $data == null )
            $data = '{}'; // stellt sicher, dass übergebene Daten nicht zu einem Absturz führen

        if ( $decode )
            $data = json_decode( $data );
        if ( is_array( $data ) ){
            $result = array( ); // erzeugt eine Liste von Objekten
            foreach ( $data AS $key => $value ){
                $result[] = new BEISPIEL( $value );
            }
            return $result;
            
        } else // erzeugt ein einzelnes Objekt
            return new BEISPIEL( $data );
    }

    // bereitet die Attribute des Objekts für die 
    // Serialisierung vor (nur belegte Felder sollen übertragen werden)
    public function jsonSerialize( )
    {
        $list = array( );
        if ( $this->param !== null )
            $list['param'] = $this->param;
        
        // ruft auch die Serialisierung des darüber liegenden Objekts auf (Object.php)
        return array_merge($list,parent::jsonSerialize( ));
    }

    // wandelt ein assoziatives Array, welches einer Datenbankanfrage entstammt
    // anhand der DBConvert und der Primärschlüssel in Objekte um
    public static function ExtractBEISPIEL( 
                                                    $data
                                                    )
    {

        $res = DBJson::getResultObjectsByAttributes( 
                                                    $data,
                                                    BEISPIEL::getDBPrimaryKey( ),
                                                    BEISPIEL::getDBConvert( )
                                                    );
        return $res;
    }
}

 