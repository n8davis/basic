<?php

namespace Davis\Basic;

class Ftp
{

    protected $connectionType = 'ftp';
    protected $downloadDirectory = '/';
    protected $errors = [];
    protected $host = '';
    protected $httpCode;
    protected $logger;
    protected $password = '';
    protected $path;
    protected $port = 21;
    protected $results;
    protected $uri = '';
    protected $uploadDirectory = '/';
    protected $username = '';
    protected $upload;
    protected $download;
    protected $options ;
    protected $header;
    protected $body;


    public function __construct() {
    }

    private function options( $fileToUpload = false )
    {

        $options = array(
            CURLOPT_USERPWD        => $this->getUsername().':'.$this->getPassword(),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_FTP_SSL        => CURLFTPSSL_ALL, // require SSL For both control and data connections
            CURLOPT_FTPSSLAUTH     => CURLFTPAUTH_TLS, // let cURL choose the FTP authentication method (either SSL or TLS)
            CURLOPT_PORT           => $this->getPort(),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_URL            => $this->uri,
            CURLOPT_HEADER         => false
        );


        if( $this->upload === true && $fileToUpload !== false && file_exists( $fileToUpload ) ) {
            $fileInfo                      = pathinfo( $fileToUpload );
            $options[ CURLOPT_URL ]        = $options[ CURLOPT_URL ]  . $fileInfo['basename'];
            $fp                            = fopen( $fileToUpload, 'rb' );
            $options[ CURLOPT_INFILE ]     = $fp ;
            $options[ CURLOPT_UPLOAD ]     = true ;
            $options[ CURLOPT_INFILESIZE ] = filesize( $fileToUpload ) ;
        }
        if( $this->download === true ) {
            $options[ CURLOPT_URL ]           .= $fileToUpload;
            $options[ CURLOPT_UPLOAD ]         = false ;
            $options[ CURLOPT_FOLLOWLOCATION ] = true  ;

        }

        $this->setOptions( $options ) ;

        return $options;
    }

    /**
     * @param bool $file
     * @return $this
     */
    public function connect( $file = false ) {


        try{
            $this->uri =  $this->getConnectionType() . "://" . $this->getHost() . $this->getPath();


            $curl = curl_init();
            curl_setopt_array( $curl , $this->options( $file ) ) ;

            $results = curl_exec( $curl );;
            $info    = curl_getinfo( $curl );

            $this->setHttpCode( array_key_exists( 'http_code' , $info ) ? $info[ 'http_code' ] : 0 );

            if( $curlError = curl_errno( $curl ) > 0 ){
                $this->addError( $curlError ) ;
                return $this;
            }

            $header_size = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
            $header      = substr( $results , 0, $header_size );
            $body        = substr( $results , $header_size );
            $this->setResults( $results )
                ->setHeader( $header )
                ->setBody( $body ) ;
            return $this;
        }
        catch ( \Exception $exception ){
            $this->addError( [ $exception->getMessage() ] ) ;
            return $this;
        }

    }

    public function download( $file , $putContentsHere )
    {
        $this->download = true;
        $this->connect( $file ) ;

        return file_put_contents( $putContentsHere , $this->getBody() ) ;
    }


    public function getFiles() {

        try {

            $this->uri =  $this->getConnectionType() . "://" . $this->getHost() . $this->getPath();
            $ch        = curl_init();

            curl_setopt_array( $ch , $this->options() ) ;

            $result = curl_exec( $ch );

            if ( ! curl_errno( $ch ) ) {
                $matches = [];
                try {
                    $lines = explode( "\n", $result );

                    foreach ( $lines as $line ) {
                        $parse = explode(" " , $line ) ;

                        if ( ! empty( $parse ) ) {

                            $filename  = $parse[ count( $parse ) - 1 ];
                            if( strlen( $filename ) === 0 ) continue;
                            if( strpos( $filename , '.txt' ) === false ) continue;
                            $matches[] = $filename;
                        }
                    }
                } catch ( \Exception $e ) {
                    Assist::display( $e->getMessage() );
                }

                return $matches;
            }
            else {
                $error = curl_error( $ch );
                Assist::display( $this->uri );
                Assist::display( 'Error downloading file: ' . $error );

                return [];
            }

        } catch ( \Exception $e ) {
            Assist::display( $e->getMessage() );
        }

        return [];

    }


    public function upload( $file )
    {
        $this->upload = true;
        return $this->connect( $file );
    }


    /**
     * @return string
     */
    public function getUploadDirectory() {
        return $this->uploadDirectory;
    }

    /**
     * @param $uploadDirectory
     * @return $this
     */
    public function setUploadDirectory( $uploadDirectory ) {
        $this->uploadDirectory = $uploadDirectory;
        return $this;
    }

    /**
     * @return string
     */
    public function getDownloadDirectory() {
        return $this->downloadDirectory;
    }

    /**
     * @param $downloadDirectory
     * @return $this
     */
    public function setDownloadDirectory( $downloadDirectory ) {
        $this->downloadDirectory = $downloadDirectory;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @param $errors
     * @return $this
     */
    public function setErrors( $errors ) {
        $this->errors[] = $errors;
        return $this;
    }

    public function addError( $error ){
        $errors = $this->getErrors();
        $errors[] = $error;
        $this->setErrors( $errors ) ;
        return $this;
    }

    
    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param $host
     * @return $this
     */
    public function setHost( $host ) {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername( $username ) {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword( $password ) {
        $this->password = $password;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * @param $port
     * @return $this
     */
    public function setPort( $port ) {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getConnectionType() {
        return $this->connectionType;
    }

    /**
     * @param $connectionType
     * @return $this
     */
    public function setConnectionType( $connectionType ) {
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param mixed $httpCode
     * @return Ftp
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return Ftp
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param mixed $results
     * @return Ftp
     */
    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     * @return Ftp
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     * @return Ftp
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return Ftp
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return Ftp
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
        return $this;
    }




}