<?php

namespace Davis\Basic;

class Reader
{

    protected $filename = '';
    private $delimiter;
    protected $data = [];
    protected $headers = [];

    protected $includeHeader;

    public $_properties = [];

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        $methodName = substr( $method, 3 );

        if (strpos( $method, "get" ) !== FALSE && array_key_exists( $methodName, $this->_properties )) {
            return $this->_properties[$methodName];
        }
        if (strpos( $method, "set" ) !== FALSE) {
            $this->_properties[$methodName] = $params[0];

        } else {
            if (isset($this->$method)) {
                $func = $this->$method;
                return call_user_func_array($func, $params);
            }
        }

        return null;
    }

    /**
     * @return array|bool
     */
    public function process() {
        if ( file_exists( $this->filename ) ) {
            $results = [];
            $i      = - 1;
            $file   = fopen( $this->filename, 'r' );
            while ( ( $line = fgetcsv( $file, 4096, $this->getDelimiter() ) ) !== false ) {
                if( $this->getIncludeHeader() === false && $i === - 1 ) {
                    if( is_array( $line ) && ! empty( $line ) ) {
                        foreach( $line as $index => $value ){
                            $method = 'set' . ucwords(  $value , '_' );
                            $method = str_replace( '_', '', $method );
                            $this->headers[] = $method ;
                        }
                    }

                    $i++;
                    continue;
                }

                $entity = new self;
                if( ! empty( $this->headers ) ){
                    foreach( $this->headers as $index => $method ){
                        $entity->{$method}( $line[ $index ] );
                    }
                }

                $results[] = $entity;
                $i ++;
            }
            fclose( $file );

            $this->data = $results;
            return $results;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param $filename
     * @return $this
     */
    public function setFilename( $filename ) {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter() {
        return $this->delimiter;
    }

    /**
     * @param $delimiter
     * @return $this
     */
    public function setDelimiter( $delimiter ) {
        $this->delimiter = $delimiter;

        return $this;
    }



    public function getData() {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getIncludeHeader()
    {
        return $this->includeHeader;
    }

    /**
     * @param mixed $includeHeader
     * @return Reader
     */
    public function setIncludeHeader($includeHeader)
    {
        $this->includeHeader = $includeHeader;
        return $this;
    }



}
