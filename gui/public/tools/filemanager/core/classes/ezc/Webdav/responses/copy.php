<?php
/**
 * File containing the ezcWebdavCopyResponse class.
 *
 * @package Webdav
 * @version 1.1.4
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class generated by the backend to respond to COPY requests.
 *
 * If a {@link ezcWebdavBackend} receives an instance of {@link
 * ezcWebdavCopyRequest} it might react with an instance of {@link
 * ezcWebdavCopyResponse} or with producing an error.
 *
 * @version 1.1.4
 * @package Webdav
 */
class ezcWebdavCopyResponse extends ezcWebdavResponse
{
    /**
     * Creates a new response object.
     *
     * The $replaced parameter must indicate wether the target has been
     * overwritten during the copy process.
     * 
     * @param bool $replaced
     * @return void
     */
    public function __construct( $replaced )
    {
        $this->replaced = $replaced;

        if ( $replaced )
        {
            parent::__construct( ezcWebdavResponse::STATUS_204 );
        }
        else
        {
            parent::__construct( ezcWebdavResponse::STATUS_201 );
        }
    }

    /**
     * Sets a property.
     *
     * This method is called when an property is to be set.
     * 
     * @param string $propertyName The name of the property to set.
     * @param mixed $propertyValue The property value.
     * @return void
     * @ignore
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the given property does not exist.
     * @throws ezcBaseValueException
     *         if the value to be assigned to a property is invalid.
     * @throws ezcBasePropertyPermissionException
     *         if the property to be set is a read-only property.
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'replaced':
                if ( !is_bool( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'boolean' );
                }

                $this->properties[$propertyName] = $propertyValue;
                break;

            default:
                parent::__set( $propertyName, $propertyValue );
        }
    }
}

?>
