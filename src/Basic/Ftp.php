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
    protected $is_secure = true ;
    protected $timeout = 5;
    protected $directory;

    /**
     * Ftp constructor.
     * @param $host
     * @param $user
     * @param $password
     * @param $port
     */
    public function __construct( $host , $user , $password , $port )
    {
        try {

            $this->setHost(trim( $host ) )
                ->setUsername(trim( $user ) )
                ->setPassword(trim( $password ) )
                ->setPort( trim( $port ) )
                ->setTimeout( $this->timeout )
                ->setIsSecure( $this->is_secure );

            if ( $this->getIsSecure() ) {
                $this->setConnection( ftp_ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout()));
            } else {
                $this->setConnection( ftp_connect($this->getHost(), $this->getPort(), $this->getTimeout()));
            }

            if ($this->getConnection() && $this->getUsername() && $this->getPassword()) {
                try {
                    $isLoggedIn = ftp_login($this->getConnection(), $this->getUsername(), $this->getPassword());
                    $this->setIsLoggedIn($isLoggedIn);
                } catch (\Exception $exception) {
                    $message = $exception->getMessage() . ' [ TRACE ] ' . $exception->getTraceAsString();
                    Logger::writeToLogFile($message, 'ftp');
                    Assist::display($message);
                }
            }

            $this->setPassiveMode(true);
        }
        catch ( \Exception $exception ){
            $message = $this->shopOwner->shop . " There was an error with the Ftp Class " .PHP_EOL . $exception->getMessage() ;
            Logger::writeToLogFile( $message ,'ftp') ;
        }

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

        $file = rtrim( $downloadTo , '/' ) . DIRECTORY_SEPARATOR . ltrim( $remoteFile , '/' ) ;
        $file = explode( '/' , $file );
        $file = $file[ count( $file ) - 1 ];
        $this->createIfNotExists( $file ) ;

        if( is_dir( $file ) ) return false;

        return ftp_get( $this->getConnection() , $downloadTo . $file , $remoteFile , FTP_BINARY ) ;

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

        if( ! file_exists( $path ) ){
            $file  = fopen( $path , 'w') ;
            fwrite( $file ,"" );
            fclose($file);
        }

        return true;

    }

    /**
     * Gets files from given directory
     *
     * @param string $directory
     * @return $this
     */
    public function getFilesFromDirectory( $directory )
    {
        if( ! $this->getIsLoggedIn() ) return $this;

        $this->setDirectory( $directory ) ;

        $contents = ftp_nlist( $this->getConnection() , $directory ) ;

        if( is_array( $contents ) ) foreach( $contents as $content ) $this->addFile( $content );

        return $this;
    }

    /**
     * Closes Ftp connection
     *
     * @return bool|$this
     */
    public function close()
    {
        if( ! $this->getIsLoggedIn() ) return $this;

        return ftp_close( $this->getConnection() ) ;
    }

    /**
     * Declares passive mode
     *
     * @param bool $mode
     * @return bool
     */
    public function setPassiveMode( $mode )
    {

        if( is_null( $this->getConnection() ) || is_bool( $this->getConnection() ) ) return false;

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
    public function setTimeout($timeout )
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