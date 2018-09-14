<?php

namespace Davis\Basic;

class Writer{

    public $delimiter = ',';

    protected $filename = __DIR__ . '/file.txt';
    protected $mode = 'w';
    protected $enclosure = '"';
    protected $includeHeader = false;
    protected $logger;
    protected $fields = [];
    protected $_properties = [];

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

    public function write($mode = 'wb', $delimiter = null, $enclosure = null)
    {
        if ( $delimiter === null ) {
            $delimiter = $this->getDelimiter();
        }

        if ($enclosure === null) $enclosure = chr( 0 );

        $data = $this->toArray();

        try {
            $fileInfo = pathinfo( $this->getFilename() );
            $directoryPath = $fileInfo['dirname'];

            if (!is_dir($directoryPath) && !is_dir($directoryPath)) {
                $oldmask = umask(0);
                try {
                    mkdir($directoryPath, 0755, true);
                } catch (\Exception $e){
                    Logger::writeToLogFile( $e->getMessage() . '[ TRACE ]' , 'system_' ) ; 
                }
                umask($oldmask);
            }
            $fp = fopen( $this->getFilename(), $mode);

            $hasHeader = \fstat( $fp );

            if (!empty( $data )) {
                if ($this->includeHeader && isset($hasHeader['size']) && $hasHeader['size'] === 0) {
                    fwrite($fp, implode($delimiter, array_keys($data)) . "\r\n");
                }
                fwrite( $fp, implode( $delimiter, $data ) . PHP_EOL);
            }

            fclose($fp);

            return true;

        } catch (\Exception $e){
            echo $e->getMessage();
        }

        return false;
    }

    public function toArray() {
        $data = [];
        foreach ( $this->fields as $index => $field ) {
            $method = 'get' . ucwords(  $field, '_' );
            $method = str_replace( '_', '', $method );
            if ( $field === '' ) {
                $data[] = '';
            } else {
                $data[ $field ] = $this->{ $method }();
            }
        }
        return $data;
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

    /**
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function setMode( $mode ) {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnclosure() {
        return $this->enclosure;
    }

    /**
     * @param $enclosure
     * @return $this
     */
    public function setEnclosure( $enclosure ) {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeHeader()
    {
        return $this->includeHeader;
    }

    /**
     * @param bool $includeHeader
     * @return Writer
     */
    public function setIncludeHeader($includeHeader)
    {
        $this->includeHeader = $includeHeader;
        return $this;
    }


}