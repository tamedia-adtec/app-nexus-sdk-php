<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// ThirdPartyPixelService.php
//-----------------------------------------------------------------------------


namespace Exactdrive\AppNexus;

/**
 * AppNexus ThirdPartyPixel Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>, Oliver Milanovic <omilanovic@codeframe.ch>
 * @version $Id$
 */
class ThirdPartyPixelService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Third Party Pixel properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Third-Party+Pixel+Service#Third-PartyPixelService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'name',                     // name of the third party pixel
        'active',                   // true / false
        'advertiser_id',            // id of the advertiser to which the thirdPartyPixel is attached
        'format',                   // "raw-js", "url-html", "url-js", "url-image", or "raw-url".
        'content',
        'secure_content',
        'url',
        'secure_url',
        'advertisers',
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus thirdPartyPixel service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/thirdparty-pixel';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new third party pixel.
     *
     * @param  int $advertiserId => Advertiser id of thirdPartyPixel.
     * @param  array $thirdPartyPixel => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $thirdPartyPixel        => Newly created appnexus thirdPartyPixel.
     */
    public static function addThirdPartyPixel( $advertiserId, $thirdPartyPixel )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createPixelHash( $thirdPartyPixel );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::POST, $data );

        // wrap response with app nexus object
        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing third party pixel.
     *
     * @param  int $id => Id of thirdPartyPixel.
     * @param  int $advertiserId => Id of the associated advertiser.
     * @param  array $thirdPartyPixel => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $thirdPartyPixel        => Updated appnexus thirdPartyPixel.
     */
    public static function updateThirdPartyPixel( $id, $advertiserId, $thirdPartyPixel )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createPixelHash( $thirdPartyPixel );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::PUT, $data );

        // wrap response with app nexus object
        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View all third party pixels, can filter by advertiser, results are paged.
     *
     * @param int $advertiserId
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $pixels
     */
    public static function getAllThirdPartyPixels( $advertiserId = null, $start_element = 0, $num_elements = 100 )
    {
        // construct query
        $query = array(
            'start_element' => $start_element,
            'num_elements'  => $num_elements,
        );

        // add advertiser filter if requested
        if ($advertiserId != null) {
            $query['advertiser_id'] = $advertiserId;
        }

        // construct url
        $url = self::getBaseUrl().'?'.http_build_query( $query );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View pixels specified by ids, results are paged.
     *
     * @param  array(int) $ids
     *
     * @return AppNexusArray $pixels
     */
    public static function getThirdPartyPixels( $ids )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => implode( ',', $ids ),
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response to be an array if only single result queried
        if (count( $ids ) == 1) {
            $key            = $response['dbg_info']['output_term'];
            $response[$key] = array( $response[$key] );
        }

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific thirdPartyPixel.
     *
     * @param  int $id
     *
     * @return AppNexusObject $thirdPartyPixel
     */
    public static function getThirdPartyPixel( $id )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => $id,
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * Delete a thirdPartyPixel.
     *
     * @param  int $id => Id of thirdPartyPixel.
     * @param  int $advertiserId => Advertiser id of thirdPartyPixel.
     *
     * @return bool $status
     */
    public static function deleteThirdPartyPixel( $id, $advertiserId )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // query app nexus server
        self::makeRequest( $url, Api::DELETE );

        return true;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns a thirdPartyPixel hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  array $thirdPartyPixel
     *
     * @return array
     */
    private static function _createPixelHash( $thirdPartyPixel )
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists( $key, $thirdPartyPixel )) {
                $pruned[$key] = $thirdPartyPixel[$key];
            }
        }

        // return null if no valid fields found
        return empty( $pruned ) ? null : array( 'thirdparty-pixel' => $pruned );
    }

}
