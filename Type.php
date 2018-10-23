<?php
namespace App\Manager\Basic;


class Type
{

    const OUTGOING  = 1;

    const INCOMING  = 2;

    const RETURNLY  = 3;

    const FEDEX     = 4;

    const NETSUITE  = 5;
    
    const SHIP_HERO = 6;

    public static function get( $code = null ) {

        try {
            $reflectionClass = new \ReflectionClass(Type::class );
            $constants       = $reflectionClass->getConstants() ;

            foreach( $constants as $name => $value ){
                if( ( int ) $code === ( int ) $value ) return ucfirst( strtolower( $name ) )  ;
            }

            return false;
        }
        catch ( \ReflectionException $reflectionException ){
            return false;
        }

    }

}