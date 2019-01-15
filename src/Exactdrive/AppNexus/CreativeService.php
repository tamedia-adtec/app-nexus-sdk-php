<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// CreativeService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Creative Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class CreativeService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Creative properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/pages/viewpage.action?title=Creative+Service&spaceKey=api#CreativeService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'code',                    // custom code for the creative
        'code2',                   // additional custom code for the creative
        'name',                    // name of the creative
        'advertiser_id',           // id of the advertiser to which the creative is attached
        'brand_id',                // id of the brand of the company advertising the creative
        'state',                   // 'active' / 'inactive'
        'campaigns',               // ids and/or codes to which the creative is associated to
        'template',                // creative template for the creative's format and media type
        'thirdparty_page',         // brand page in Facebook where the News Feed creative will be added as a hidden post
        'custom_macros',           // values for custom macros used in the creative template
        'width',                   // width of the creative
        'height',                  // height of the creative
        'media_url',               // url of the creative
        'media_url_secure',        // url of the secure (https) creative
        'click_url',               // landing page url for non-3rd party image and flash creatives
        'file_name',               // file name and extension for a hosted creative
        'flash_click_variable',    // clickTag variable in a Flash creative
        'content',                 // javascript or html content when "format" is "raw-js" or "iframe-html"
        'content_secure',          // javascript or html content when "format" is "raw-js" or "iframe-html" served on a secure ad call
        'original_content',        // value you pass into the "content" field through the UI will be returned in this field unchanged
        'original_content_secure', // secure version of original_content
        'audit_status',            // audit status of the creative: "no_audit", "pending", "rejected", or "audited"
        'allow_audit',             // true, the creative will be submitted for auditing; false, the creative will not be submitted
        'ssl_status',              // ssl (https) status of the creative: 'disabled', 'pending', 'approved', 'failed'
        'allow_ssl_audit',         // true, the creative will be submitted for secure (https) auditing
        'is_self_audited',         // true, the creative is self-audited and thus will not go through platform (AppNexus) audit
        'lifetime_budget',         // lifetime budget in dollars
        'lifetime_budget_imps',    // lifetime limit for number of impressions
        'daily_budget',            // daily budget in dollars
        'daily_budget_imps',       // daily limit for number of impressions
        'enable_pacing',           // true, daily budgeted spend is spread evenly throughout a day
        'allow_safety_pacing',     // true, spend per minute is limited to a maximum of 1% of the lifetime budget and 5% of the daily budget
        'profile_id',              // attach targeting such as gender and geography to a creative by creating a profile and associating it here
        'folder',                  // arrange your creatives in folders for convenience
        'line_items',              // line items that are associated with the creative
        'pixels',                  // pixels to serve with the creative
        'track_clicks',            // must be set to true for AppNexus to track clicks
        'flash_backup_content',    // flash creative, this is the content of the backup creative that will be served if a user's browser does not support flash
        'flash_backup_file_name',  // file name and extension of the backup creative
        'flash_backup_url',        // url of a 3rd-party creative that will be served if the user's browser does not support flash
        'is_control',              // ??
        'segments',                // list of segments that a user will be added to upon viewing or clicking on this creative
        'media_subtypes',          // ways in which the advertiser will allow a creative to be displayed
        'use_dynamic_click_url',   // true, the (optional) landing page url for non-3rd party image and flash creatives is set at the campaign or line item level
        'text_title',              // top line of text displayed in a text creative
        'text_description',        // lower line of text displayed in a text creative
        'text_display_url',        // readable URL displayed in a text creative
        'click_action',            // action that the device should take when the creative is clicked
        'click_target',            // target of the click_action
        'categories',              // categories that describe the creative and offer type
        'adservers',               // ad servers that deliver the creative or are called for data collection purposes during the delivery the creative
        'technical_attributes',    // attributes that describe technical characteristics of the creative, such as "Expandable" or "Video"
        'language',                // language of the creative
        'pop_values',              // pop settings for the creative
        'sla',                     // priority audit request
        'mobile',                  // information needed for mobile creatives to pass the creative audit
        'content_source',          // source of this creative's content: 'standard', 'mediation'
        'custom_request_template', // association between this creative and a custom request template which is used to populate the creative with content
        'competitive_brands',      // creatives associated with the brands in this array will not serve together in /mtj auctions
        'competitive_categories',  // creatives associated with the categories in this array will not serve together in /mtj auctions
        'native',
        'currency',
        'thirdparty_pixels',
        'impression_trackers',
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus creative service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/creative';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new creative.
     *
     * @param  int $advertiserId => Advertiser id of creative.
     * @param  array $creative => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $creative     => Newly created appnexus creative.
     */
    public static function addCreative( $advertiserId, $creative )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createCreativeHash( $creative );
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
     * Update an existing creative.
     *
     * @param  int $id => Id of creative.
     * @param  int $advertiserId => Id of the associated advertiser.
     * @param  array $creative => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $creative     => Updated appnexus creative.
     */
    public static function updateCreative( $id, $advertiserId, $creative )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createCreativeHash( $creative );
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
     * View all creatives, can filter by advertiser, results are paged.
     *
     * @param int $advertiserId
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $creatives
     */
    public static function getAllCreatives(
        $advertiserId = null,
        $start_element = 0,
        $num_elements = 100
    ) {
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
     * View creatives specified by ids, results are paged.
     *
     * @param  int[] $ids
     *
     * @return AppNexusArray $creatives
     */
    public static function getCreatives( $ids )
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
     * View a specific creative.
     *
     * @param  int $id
     *
     * @return AppNexusObject $creative
     */
    public static function getCreative( $id )
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
     * Delete a creative.
     *
     * @param  int $id => Id of creative.
     * @param  int $advertiserId => Advertiser id of creative.
     *
     * @return bool $status
     */
    public static function deleteCreative( $id, $advertiserId )
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
     * Returns a creative hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  array $creative
     *
     * @return array $creative
     */
    private static function _createCreativeHash( $creative )
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists( $key, $creative )) {
                $pruned[$key] = $creative[$key];
            }
        }

        // return null if no valid fields found
        return empty( $pruned ) ? null : array( 'creative' => $pruned );
    }

}
