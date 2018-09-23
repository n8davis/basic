<?php

namespace App\Manager\Basic;

class Ftp
{

    protected $connection ;
    protected $isLoggedIn;
    protected $port;
    protected $host;
    protected $username;
    protected $password;
    protected $files = [];
    protected $is_secure;
    protected $timeout = 5;
    protected $directory;

    /**
     * Ftp constructor.
     * @param string $username
     * @param string $password
     * @param string $host
     * @param string $port
     * @param int $timeout
     * @param bool $isSecure
     */
    public function __construct( string $username , string $password , string $host , string $port , int $timeout = 5 , bool $isSecure = true )
    {
        $this->setHost( $host )
            ->setUsername( $username )
            ->setPassword( $password )
            ->setPort( $port )
            ->setTimeout( $timeout )
            ->setIsSecure( $isSecure );

        if( $this->getIsSecure() ) {
            $this->setConnection( ftp_ssl_connect( $this->getHost() , $this->getPort() , $this->getTimeout() ) );
        }
        else{
            $this->setConnection( ftp_connect( $this->getHost() , $this->getPort() , $this->getTimeout() ) );
        }

        if( $this->getConnection() ) {
            $isLoggedIn = ftp_login($this->getConnection(), $this->getUsername(), $this->getPassword());
            $this->setIsLoggedIn($isLoggedIn);
        }

        $this->setPassiveMode( true ) ;

    }

    /**
     * @param $oldName
     * @param $newName
     * @return bool
     */
    public function rename( $oldName , $newName )
    {

        if( ! $this->getIsLoggedIn() ) return false ;

        return ftp_rename( $this->getConnection() , $oldName , $newName ) ;

    }

    /**
     * @param $remoteFile
     * @param $downloadTo
     * @return bool
     */
    public function download( $remoteFile , $downloadTo )
    {
        if( ! $this->getIsLoggedIn() ) return false ;

        $localPath = rtrim( $downloadTo , '/' ) . DIRECTORY_SEPARATOR . ltrim( $remoteFile , '/' ) ;

        $this->createIfNotExists( $localPath ) ;

        if( is_dir( $localPath ) ) return false;

        return ftp_get( $this->getConnection() , $localPath , $remoteFile , FTP_BINARY ) ;

    }

    /**
     * @param $localFile
     * @param $uploadTo
     * @return bool
     */
    public function upload( $localFile , $uploadTo )
    {

        if( ! $this->getIsLoggedIn() ) return false ;

        return ftp_put( $this->getConnection() , $uploadTo , $localFile, FTP_BINARY  );

    }

    /**
     * @param $file
     * @return bool
     */
    public function delete( $file )
    {

        if( ! $this->getIsLoggedIn() ) return false ;

        return ftp_delete( $this->getConnection() , $file ) ;

    }

    /**
     * @param $path
     * @return bool
     */
    public function createIfNotExists( $path ){

        $extensions = [ '.txt' , '.csv' , '.log' , '.jpg' , '.jpeg' , '.png' , '.gif' ];
        $dir        = pathinfo( $path , PATHINFO_DIRNAME ) ;


        if( is_dir( $dir ) || file_exists( $dir ) ) return true;

        if( $this->createIfNotExists( $dir ) ) {
            // check if last part is a file
            $parse   = explode( "/" , $path ) ;
            $lastDir = array_pop( $parse ) ;

            if( in_array( substr( $lastDir , -4 ) , $extensions ) ){
                $this->createLocalFile( $path ) ;
                return true;
            }

            if( mkdir( $path , 0775 , true ) ) return true;

        }

        return false ;
    }

    /**
     * @param $path
     * @return bool
     */
    public function createLocalFile( $path )
    {

        try {
            if ( ! file_exists( $path ) ) {
                $file = fopen( $path, 'w' );
                fwrite( $file, "" );
                fclose( $file );

                return true;

            }

        }
        catch ( \Exception $exception ){}

        return false;
    }

    /**
     * Gets files from given directory
     *
     * @param string $directory
     * @return $this
     */
    public function getFilesFromDirectory( string $directory )
    {
        if( ! $this->getIsLoggedIn() ) return $this;

        $this->setDirectory( $directory ) ;

        $contents = ftp_nlist( $this->getConnection() , $directory ) ;

        foreach( $contents as $content ) $this->addFile( $content );

        return $this;
    }

    /**
     * Closes Ftp connection
     *
     * @return bool
     */
    public function close()
    {
        return ftp_close( $this->getConnection() ) ;
    }

    /**
     * Declares passive mode
     *
     * @param bool $mode
     * @return bool
     */
    public function setPassiveMode( bool $mode )
    {
        return ftp_pasv( $this->getConnection() , $mode ) ;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Ftp
     */
    public function setTimeout(int $timeout )
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSecure()
    {
        return $this->is_secure;
    }

    /**
     * @param mixed $is_secure
     * @return Ftp
     */
    public function setIsSecure($is_secure)
    {
        $this->is_secure = $is_secure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     * @return Ftp
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsLoggedIn()
    {
        return $this->isLoggedIn;
    }

    /**
     * @param mixed $isLoggedIn
     * @return Ftp
     */
    public function setIsLoggedIn($isLoggedIn)
    {
        $this->isLoggedIn = $isLoggedIn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return Ftp
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return Ftp
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return Ftp
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Ftp
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $files
     * @return Ftp
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @param $file
     * @return array
     */
    public function addFile( $file )
    {
        $files = $this->getFiles();
        $files[] = $file;
        $this->setFiles( $files );
        return $files;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     * @return Ftp
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }


}