<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// PixelService.php
//-----------------------------------------------------------------------------


namespace YonderWeb\AppNexus;

/**
 * AppNexus Pixel Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class PixelService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Pixel properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Pixel+Service#PixelService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'code',                    // custom code for the pixel
        'name',                    // name of the pixel
        'state',                   // 'active' / 'inactive'
        'trigger_type',            // type of event required for a valid conversion: "view", "click", "hybrid"
        'min_minutes_per_conv',    // eligibility rules for repeat conversions: count all conversions (0), count one per user (NULL), count one conversion per X Minutes
        'post_view_expire_mins',   // interval from impression time allowed for a view conversion to be counted as eligible
        'post_click_expire_mins',  // interval from impression time allowed for a click conversion to be counted as eligible
        'post_click_value',        // value you attribute to a conversion after a click
        'post_view_value',         // value you attribute to a conversion after a view
        'piggyback_pixels',        // urls of pixels you want us to fire when conversion pixel fires
        'advertiser_id'            // id of the advertiser to which the pixel is attached
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus pixel service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/pixel';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new pixel.
     *
     * @param  int  $advertiserId => Advertiser id of pixel.
     * @param  hash $pixel        => Only valid fields will be passed to api.
     * @return hash $pixel        => Newly created appnexus pixel.
     */
    public static function addPixel($advertiserId, $pixel)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'advertiser_id' => $advertiserId
        ));

        // package up the data, don't bother running query on invalid data
        $data = self::_createPixelHash($pixel);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::POST, $data);

        // wrap response with app nexus object
        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing pixel.
     *
     * @param  int  $id           => Id of pixel.
     * @param  int  $advertiserId => Id of the associated advertiser.
     * @param  hash $pixel        => Only valid fields will be passed to api.
     * @return hash $pixel        => Updated appnexus pixel.
     */
    public static function updatePixel($id, $advertiserId, $pixel)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id'            => $id,
            'advertiser_id' => $advertiserId
        ));

        // package up the data, don't bother running query on invalid data
        $data = self::_createPixelHash($pixel);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::PUT, $data);

        // wrap response with app nexus object
        return new Object($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View all pixels, can filter by advertiser, results are paged.
     *
     * @param  int   $advertiserId
     * @return array $pixels
     */
    public static function getAllPixels($advertiserId = null, $start_element = 0, $num_elements = 100)
    {
        // construct query
        $query = array(
            'start_element' => $start_element,
            'num_elements'  => $num_elements
        );

        // add advertiser filter if requested
        if ($advertiserId != null) {
            $query['advertiser_id'] = $advertiserId;
        }

        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query($query);

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusArray($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View pixels speficied by ids, results are paged.
     *
     * @param  array(int) $ids
     * @return array      $pixels
     */
    public static function getPixels($ids)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id' => implode(',', $ids)
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response to be an array if only single result queried
        if (count($ids) == 1) {
            $key            = $response['dbg_info']['output_term'];
            $response[$key] = array($response[$key]);
        }

        // wrap response with app nexus object
        return new AppNexusArray($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific pixel.
     *
     * @param  int  $id
     * @return hash $pixel
     */
    public static function getPixel($id)
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
     * Delete a pixel.
     *
     * @param  int  $id           => Id of pixel.
     * @param  int  $advertiserId => Advertiser id of pixel.
     * @return bool $status
     */
    public static function deletePixel($id, $advertiserId)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id'            => $id,
            'advertiser_id' => $advertiserId
        ));

        // query app nexus server
        $response = self::makeRequest($url, Api::DELETE);

        return true;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns a pixel hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  hash $pixel
     * @return hash $pixel
     */
    private static function _createPixelHash($pixel)
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $pixel)) {
                $pruned[$key] = $pixel[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : array('pixel' => $pruned);
    }

}
