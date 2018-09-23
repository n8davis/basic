<?php

namespace App\Manager\Basic;

class XmlParser
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