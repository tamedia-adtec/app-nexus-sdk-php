<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// DemographicAreaService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Demographic Area Api service.
 *
 * @package AppNexus
 * @author Chris Mears <chris@exactdrive.com>
 * @version $Id$
 */
class DemographicAreaService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Demographic Area properties.
     * https://wiki.appnexus.com/display/api/Demographic+Area+Service#DemographicAreaService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'id',               // Integer
        'name'              // String
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus content category service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/dma';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * View all custom content categories belonging to your member
     *
     * @return array $countries
     */
     public static function getAll($start_element = 0, $num_elements = 100)
     {
         // construct url
         $url = self::getBaseUrl() . '?' . http_build_query(array(
             'start_element' => $start_element,
             'num_elements'  => $num_elements
         ));

         // query app nexus server
         $response = self::makeRequest($url, Api::GET);

         // wrap response with app nexus object
         return new AppNexusArray($response, AppNexusObject::MODE_READ_ONLY);
     }

}
