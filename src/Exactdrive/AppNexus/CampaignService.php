<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// CampaignService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Campaign Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class CampaignService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Campaign properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Campaign+Service#CampaignService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'state',               // 'active' / 'inactive'
        'code',                // custom code for the campaign
        'name',                // name of the campaign
        'short_name',          // name used by the Imp Bus
        'advertiser_id',       // id of the advertiser which the campaign belongs
        'profile_id',          // may associate an optional profile_id with this campaign
        'line_item_id',        // id of the line item to which the campaign is associated
        'start_date',          // date and time when the campaign should start serving
        'end_date',            // date and time when the campaign should stop serving
        'creatives',           // list of creative IDs or codes associated to the campaign
        'creative_groups',     // bucket a group of creatives and then add them to a campaign all at once
        'timezone',            // timezone of the campaign
        'last_modified',       // time of last modification to this campaign
        'inventory_type',      // type of inventory targeted:  "real_time", "direct", or "both"
        'roadblock_creatives', // only serve this campaign if all creatives attached to it
        'roadblock_type',      // "no_roadblock", "normal_roadblock", "partial_roadblock", and "exact_roadblock"
        'comments',            // comments about the campaign
        'labels',              // optional labels applied to the campaign
        'broker_fees',         // fees that the network must pay to brokers when serving an ad
        'click_url',           // landing page URL for non-3rd party image and flash creatives
        'is_malicious',        // if true, then the campaign's status will be set to inactive
        'valuation',            // object containing several fields relating to performance goals and minimum margin
        'cpm_bid_type',
        'bid_margin',
        'lifetime_budget_imps',
        'enable_pacing',
        'lifetime_pacing',
        'daily_budget',
        'lifetime_budget',
        'daily_budget_imps',
        'cadence_modifier_enabled',
        'priority',
        'creative_distribution_type',
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus campaign service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/campaign';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new campaign.
     *
     * @param  int $advertiserId => Advertiser id of campaign.
     * @param  array $campaign => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $campaign     => Newly created appnexus campaign.
     */
    public static function addCampaign($advertiserId, $campaign)
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createCampaignHash($campaign);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::POST, $data);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing campaign.
     *
     * @param  int $id => Id of campaign.
     * @param  int $advertiserId => Id of the associated advertiser.
     * @param  array $campaign => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $campaign     => Updated appnexus campaign.
     */
    public static function updateCampaign($id, $advertiserId, $campaign)
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createCampaignHash($campaign);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::PUT, $data);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View all campaigns for an advertiser results are paged.
     *
     * @param int $advertiserId
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $campaigns
     */
    public static function getAllCampaigns(
        $advertiserId,
        $start_element = 0,
        $num_elements = 100
    ) {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                    'start_element' => $start_element,
                    'num_elements'  => $num_elements,
                )
            );

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusArray($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View campaigns specified by ids, results are paged.
     *
     * @param  int[] $ids
     *
     * @return AppNexusArray $campaigns
     */
    public static function getCampaigns($ids)
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => implode(',', $ids),
                )
            );

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response to be an array if only single result queried
        if (count($ids) == 1) {
            $key            = $response['dbg_info']['output_term'];
            $response[$key] = array($response[$key]);
        }

        // wrap response with app nexus object
        return new AppNexusArray($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific campaign.
     *
     * @param  int $id
     *
     * @return AppNexusObject $campaign
     */
    public static function getCampaign($id)
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => $id,
                )
            );

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Search for campaigns with ids or names containing certain characters,
     *  results are paged.
     *
     * @param string $term
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $campaigns
     */
    public static function searchCampaigns(
        $term,
        $start_element = 0,
        $num_elements = 100
    ) {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'search'        => $term,
                    'start_element' => $start_element,
                    'num_elements'  => $num_elements,
                )
            );

        // query app nexus server
        $response = self::makeRequest($url, Api::GET);

        // wrap response with app nexus object
        return new AppNexusArray($response, AppNexusObject::MODE_READ_WRITE);
    }

    //-------------------------------------------------------------------------

    /**
     * Delete a campaign.
     *
     * @param  int $id => Id of campaign.
     * @param  int $advertiserId => Advertiser id of campaign.
     *
     * @return bool $status
     */
    public static function deleteCampaign($id, $advertiserId)
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // query app nexus server
        self::makeRequest($url, Api::DELETE);

        return true;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns a campaign hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  array $campaign
     *
     * @return array|object
     */
    private static function _createCampaignHash($campaign)
    {
        if (is_object($campaign)) {
            $pruned = new \stdClass();
            foreach (self::$fields as $key) {
                if (property_exists($campaign, $key)) {
                    $pruned->$key = $campaign->$key;
                }
            }

            // return null if no valid fields found
            return empty($pruned) ? null : (object)array('campaign' => $pruned);
        } else {
            $pruned = array();
            foreach (self::$fields as $key) {
                if (array_key_exists($key, $campaign)) {
                    $pruned[$key] = $campaign[$key];
                }
            }

            // return null if no valid fields found
            return empty($pruned) ? null : array('campaign' => $pruned);
        }
    }

}
