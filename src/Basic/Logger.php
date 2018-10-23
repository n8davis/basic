<?php
namespace App\Manager\Basic;


class Logger
{

    public $timezone  = 'America/Los_Angeles';
    public $shop      = 'default';
    public $extension = '.log';

    private function directory( $directory = null )
    {
        if( ! is_null( $directory ) ) return $directory;
        $directory = dirname( dirname( dirname( __DIR__ ) ) ) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR .
        $this->shop . DIRECTORY_SEPARATOR;


        if( ! is_dir( $directory ) ){
            mkdir( $directory , 0777 , true ) ;

            $htaccess = $directory . DIRECTORY_SEPARATOR . '.htaccess' ;
            $file     = fopen( $htaccess , 'w') ;
            fwrite( $file ,"deny from all" );
            fclose($file);
        }

        return $directory;
    }

    public static function writeToLogFile( $content , $filename , $shop = null , $directory = null ){

        $logger       = new self;
        $logger->shop = is_null( $shop ) ? $logger->shop : $shop ;
        $date         = new \DateTime( 'now' , new \DateTimeZone( $logger->timezone ) ) ;
        $dateOfFile   = $date->format( 'Ymd' ) . $logger->extension;
        $time         = $date->format("M d, Y h:i:s A");
        $content      = "\n" . $time." - ". $content;

        // check if
        if( ! is_dir( $dir = $logger->directory( $directory ) ) ){
            mkdir( $dir , 0777 , true ) ;
            $htaccess = $dir . DIRECTORY_SEPARATOR . '.htaccess' ;
            $file     = fopen( $htaccess , 'w') ;
            fwrite( $file ,"deny from all" );
            fclose($file);
        }

        if (!$handle = fopen($logger->directory( $directory ) . $filename . '_' . $dateOfFile , 'a') ) return false;

        $result = fwrite($handle,  $content);
        fclose($handle);

        if ( $result === FALSE) return false;
        return true ;
    }


    public static function cleanFiles( $directory , $days = 30 )
    {
        $logger = new self;
        try {
            $files = scandir($directory);

            if ( empty( $files ) ) return false;

            foreach ($files as $index => $file) {

                if ( $file[0] === '.') continue;

                if( is_dir( $directory . DIRECTORY_SEPARATOR . $file ) ) self::cleanFiles( $directory . DIRECTORY_SEPARATOR . $file , $days ) ;

                $parseFileName = explode( $logger->extension , $file );

                if (!array_key_exists(0, $parseFileName)) continue;

                $findFileDate = substr($parseFileName[0], -8);

                if ( \DateTime::createFromFormat('Ymd', $findFileDate) === false) continue;

                $fileDate = new \DateTime($findFileDate);
                $nowDate = new \DateTime();

                if ($fileDate->diff($nowDate)->days <= $days) continue;

                unlink($directory . DIRECTORY_SEPARATOR . $file);
            }

            return true;
        }
        catch ( \Exception $exception ){
            return false;
        }
    }
}