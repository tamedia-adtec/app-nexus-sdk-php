<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// SegmentService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Segment Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class SegmentService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Segment properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/pages/viewpage.action?title=Segment+Service&spaceKey=api#SegmentService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'code',                // custom code for the segment
        'state',               // 'active' / 'inactive'
        'short_name',          // short name used to describe the segment
        'description',         // optional description for this segment
        'member_id',           // id of the member that owns this segment
        'category',            //
        'price',               // flat cpm price of the segment
        'expire_minutes',      // expiration time for the segment
        'enable_rm_piggyback', // true, piggybacking RM pixels is enabled
        'max_usersync_pixels', // maximum number of third-party user sync pixels to piggyback onto the segment pixel
        'advertiser_id',       // id of the advertiser using the segment if the segment should be on the ddvertiser level rather than the Network level
        'piggyback_pixels',    // urls of the pixels you want us to fire when the segment pixel fires
        'parent_segment_id',   // id of the parent segment
        'querystring_mapping'  //
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus segment service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/segment';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new segment.
     *
     * @param  hash $segment      => Only valid fields will be passed to api.
     * @param  int  $advertiserId => Advertiser id of segment.
     * @return hash $segment      => Newly created appnexus segment.
     */
    public static function addSegment($segment, $advertiserId = null)
    {
        // construct query
        $query = array();

        // add advertiser filter if requested
        if ($advertiserId != null) {
            $query['advertiser_id'] = $advertiserId;
        }

        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query($query);

        // package up the data, don't bother running query on invalid data
        $data = self::_createSegmentHash($segment);
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
     * Update an existing segment.
     *
     * @param  int  $id           => Id of segment.
     * @param  int  $advertiserId => Id of the associated advertiser.
     * @param  hash $segment      => Only valid fields will be passed to api.
     * @return hash $segment      => Updated appnexus segment.
     */
    public static function updateSegment($id, $segment, $advertiserId = null)
    {
        // construct query
        $query = array(
            'id' => $id
        );

        // add advertiser filter if requested
        if ($advertiserId != null) {
            $query['advertiser_id'] = $advertiserId;
        }

        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query($query);

        // package up the data, don't bother running query on invalid data
        $data = self::_createSegmentHash($segment);
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
     * View all segments, can filter by advertiser, results are paged.
     *
     * @param  int   $advertiserId
     * @return array $segments
     */
    public static function getAllSegments($advertiserId = null,
        $start_element = 0, $num_elements = 100)
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
     * View segments speficied by ids, results are paged.
     *
     * @param  array(int) $ids
     * @return array      $segments
     */
    public static function getSegments($ids)
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
     * View a specific segment.
     *
     * @param  int  $id
     * @return hash $segment
     */
    public static function getSegment($id)
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
     * Search for segments with ids or names containing certain characters,
     *  results are paged.
     *
     * @param  string $term
     * @return array  $segments
     */
    public static function searchSegments($term,
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
        return new AppNexusArray($response, Object::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Delete a segment.
     *
     * @param  int  $id     => Id of segment.
     * @return bool $status
     */
    public static function deleteSegment($id)
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
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns a segment hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  hash $segment
     * @return hash $segment
     */
    private static function _createSegmentHash($segment)
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $segment)) {
                $pruned[$key] = $segment[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : array('segment' => $pruned);
    }

}
