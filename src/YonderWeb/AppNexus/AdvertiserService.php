<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// AdvertiserService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Advertiser Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class AdvertiserService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Advertiser properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/pages/viewpage.action?title=Advertiser+Service&spaceKey=api#AdvertiserService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'code',             // custom code for the advertiser
        'name',             // name of advertiser
        'state',            // 'active' / 'inactive'
        'billing_name',     // for reference
        'billing_phone',    // for reference
        'billing_address1', // for reference
        'billing_address2', // for reference
        'billing_city',     // for reference
        'billing_state',    // for reference
        'billing_country',  // for reference
        'billing_zip'       // for reference
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus advertiser service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/advertiser';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new advertiser.
     *
     * @param  hash $advertiser => Only valid fields will be passed to api.
     * @return hash $advertiser => Newly created appnexus advertiser id.
     */
    public static function addAdvertiser($advertiser)
    {
        // construct url
        $url = self::getBaseUrl();

        // package up the data, don't bother running query on invalid data
        $data = self::_createAdvertiserHash($advertiser);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::POST, $data);

        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing advertiser.
     *
     * @param  int  $id         => Id of advertiser.
     * @param  hash $advertiser => Only valid fields will be passed to api.
     * @return hash $advertiser => Updated appnexus advertiser.
     */
    public static function updateAdvertiser($id, $advertiser)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // package up the data, don't bother running query on invalid data
        $data = self::_createAdvertiserHash($advertiser);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::PUT, $data);

        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View all advertisers, results are paged.
     *
     * @return array $advertisers
     */
    public static function getAllAdvertisers(
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
        return new Array($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View advertisers speficied by ids, results are paged.
     *
     * @param  array(int) $ids
     * @return array      $advertisers
     */
    public static function getAdvertisers($ids)
    {
        // [moiz] need to fix this...

        // shortcut if only single id is specified
        if (count($ids) == 1) {
            return array(self::getAdvertiser($ids[0]));
        }

        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => implode(',', $ids)
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific advertiser.
     *
     * @param  int  $id
     * @return hash $advertiser
     */
    public static function getAdvertiser($id)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Search for advertisers with ids or names containing certain characters,
     *  results are paged.
     *
     * @param  string $term
     * @return array  $advertisers
     */
    public static function searchAdvertisers($term,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'search'        => $term,
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Array($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Delete an advertiser.
     *
     * @param  int  $id
     * @return bool $status
     */
    public static function deleteAdvertiser($id)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => $id
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::DELETE);

        return true;
    }

    //-------------------------------------------------------------------------

    /**
     * Retrive quick statistics about an advertiser.
     *
     * @param  int    $id
     * @param  string $interval
     * @return hash   $advertiser
     */
    public static function getQuickStats($id, $interval = '7day')
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id'       => $id,
            'stats'    => 'true',
            'interval' => $interval
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns an advertiser hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  hash $advertiser
     * @return hash $advertiser
     */
    private static function _createAdvertiserHash($advertiser)
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $advertiser)) {
                $pruned[$key] = $advertiser[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : array('advertiser' => $pruned);
    }

}
