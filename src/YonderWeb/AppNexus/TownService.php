<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// TownService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Town Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class TownService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Town properties.
     *   https://wiki.appnexus.com/display/api/Town+Service#TownService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'name',         // name of the town
        'country_code', // ISO code for the country to which the town belongs
        'country_name', // name of the country to which the town belongs
        'region_name',  // name of the region to which the town belongs
        'region_id',    // id of the region to which the town belongs
        'dma_name',     // name of the demographic area to which the town belongs
        'dma_id'        // id of the demographic area to which the town belongs
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus town service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/town';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * View all towns, results are paged.
     *
     * @return array $towns
     */
    public static function getAllTowns(
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific town.
     *
     * @param  int  $id
     * @return hash $town
     */
    public static function getTown($id)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Object($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific towns, results are paged.
     *
     * @param  array $names
     * @return array $towns
     */
    public static function getTownsByName($names,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'name'          => implode(',', $names),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View all towns in a specific country, results are paged.
     *
     * @param  array $codes
     * @return array $towns
     */
    public static function getTownsByCountryCode($codes,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'country_code'  => implode(',', $codes),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View all towns in a specific country, results are paged.
     *
     * @param  array $names
     * @return array $towns
     */
    public static function getTownsByCountryName($names,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'country_name'  => implode(',', $names),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View all towns in a specific demographic area, results are paged.
     *
     * @param  array $ids
     * @return array $towns
     */
    public static function getTownsByDemographicAreaId($ids,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'dma_id'        => implode(',', $ids),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View all towns in a specific demographic area, results are paged.
     *
     * @param  array $names
     * @return array $towns
     */
    public static function getTownsByDemographicAreaName($names,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'dma_name'      => implode(',', $names),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_ONLY);
    }

}
