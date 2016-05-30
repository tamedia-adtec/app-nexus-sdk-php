<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// CountryService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Country Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class CountryService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Country properties.
     *   https://wiki.appnexus.com/display/api/Country+Service#CountryService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'name', // name of country
        'code'  // ISO code of the country
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus country service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/country';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * View all countries, results are paged.
     *
     * @return array $countries
     */
    public static function getAllCountries(
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
        return new AppNexusArray($response, AppNexusObject::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific country.
     *
     * @param  int  $id
     * @return hash $country
     */
    public static function getCountry($id)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusObject($response, AppNexusObject::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific countries.
     *
     * @param  array $names
     * @return array $countries
     */
    public static function getCountriesByName($names)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'name' => implode(',', $names)
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusArray($response, AppNexusObject::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific countries.
     *
     * @param  array $codes
     * @return array $countries
     */
    public static function getCountriesByCode($codes)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'code' => implode(',', $codes)
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusArray($response, AppNexusObject::MODE_READ_ONLY);
    }

}
