<?php

namespace Davis\Basic;


class Status
{

    /**
     * An entity is in a pending status
     */
    const PENDING             = 1;

    /**
     * An entity is in a QUEUED status
     * A file has been written and is waiting to be sent to 3rd party
     */
    const QUEUED              = 2;

    /**
     * An entity is in a SENT status
     *
     */
    const SENT                = 3;

    /**
     * An entity is in a ERROR status
     *
     */
    const ERROR               = 4;

    /**
     * An entity is in a REJECTED status
     *
     */
    const REJECTED            = 5;

    /**
     * An entity is in a SHIPPED status
     *
     */
    const SHIPPED             = 6;

    /**
     * An entity is in a REFUNDED status
     *
     */
    const REFUNDED            = 7;

    /**
     * An entity is in a PARTIALLY_REFUNDED status
     *
     */
    const PARTIALLY_REFUNDED  = 8;

    /**
     * An entity is in a FULFILLED status
     *
     */
    const FULFILLED           = 9;

    /**
     * An entity is in a PARTIALLY_FULFILLED status
     *
     */
    const PARTIALLY_FULFILLED = 10;

    /**
     * An entity is in a CANCELLED status
     *
     */
    const CANCELLED           = 11;

    /**
     * An entity is in a ARCHIVE status
     *
     */
    const ARCHIVE             = 12;

    /**
     * An entity is in a SUCCESS status
     *
     */
    const SUCCESS             = 13;

    /**
     * An entity is in a IMPORTED status
     */
    const IMPORTED            = 14;

    /**
     * Get statuses
     *
     * @param null $code
     * @return bool|string
     */
    public static function get( $code = null ) {

        try {
            $reflectionClass = new \ReflectionClass(Status::class );
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