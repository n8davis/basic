<?php

namespace App\Manager\Basic;

class XmlParser implements \JsonSerializable
{

    protected $simpleXMLElement;

    public function __construct( \SimpleXMLElement $simpleXMLElement )
    {
        $this->setSimpleXMLElement( $simpleXMLElement ) ;
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return json_decode( json_encode( $this->getSimpleXMLElement() ) , true ) ;
    }

    public function toJson()
    {
        return json_encode( $this->getSimpleXMLElement() ) ;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach( get_object_vars($this) as $key => $value ){

            if( is_null( $value ) || $key[ 0 ] === '_'  || empty( $value ) ) continue;

            $method = 'get' . ucwords( $key, '_' );
            $method = str_replace( '_', '', $method );

            if( ! method_exists( $this , $method ) ) continue;

            $data[ $key ] = $this->{ $method }( $value );
        }
        return $data;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getSimpleXMLElement()
    {
        return $this->simpleXMLElement;
    }

    /**
     * @param \SimpleXMLElement $simpleXMLElement
     * @return XmlParser
     */
    public function setSimpleXMLElement( \SimpleXMLElement $simpleXMLElement)
    {
        $this->simpleXMLElement = $simpleXMLElement;
        return $this;
    }


}