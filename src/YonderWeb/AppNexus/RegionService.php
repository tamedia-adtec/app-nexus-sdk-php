<?php
//-----------------------------------------------------------------------------
// RegionService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Region Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class AppNexus_RegionService extends AppNexus_Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Region properties.
     *   https://wiki.appnexus.com/display/api/Region+Service#RegionService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'name',         // name of region
        'code',         // ISO code of the region
        'country_code', // ISO code for the country to which the region belongs
        'country_name'  // name of the country to which the region belongs
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus region service url.
     */
    public static function getBaseUrl()
    {
        $url = AppNexus_Api::getBaseUrl() . '/region';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * View all regions, results are paged.
     *
     * @return array $regions
     */
    public static function getAllRegions(
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, AppNexus_Api::GET);

        // wrap response with app nexus object
        return new AppNexus_Array($response, AppNexus_Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific region.
     *
     * @param  int  $id
     * @return hash $region
     */
    public static function getRegion($id)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // query app nexus server
        $response = self::makeRequest($url, AppNexus_Api::GET);

        // wrap response with app nexus object
        return new AppNexus_Object($response, AppNexus_Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific regions, results are paged.
     *
     * @param  array $names
     * @return array $regions
     */
    public static function getRegionsByName($names,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'name'          => implode(',', $names),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, AppNexus_Api::GET);

        // wrap response with app nexus object
        return new AppNexus_Array($response, AppNexus_Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific regions, results are paged.
     *
     * @param  array $codes
     * @return array $regions
     */
    public static function getRegionsByCode($codes,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'code'          => implode(',', $codes),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, AppNexus_Api::GET);

        // wrap response with app nexus object
        return new AppNexus_Array($response, AppNexus_Object::MODE_READ_ONLY);
    }

    //-------------------------------------------------------------------------

    /**
     * View specific regions, results are paged.
     *
     * @param  array $codes
     * @return array $regions
     */
    public static function getRegionsByCountryCode($codes,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'country_code'  => implode(',', $codes),
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, AppNexus_Api::GET);

        // wrap response with app nexus object
        return new AppNexus_Array($response, AppNexus_Object::MODE_READ_ONLY);
    }

}
