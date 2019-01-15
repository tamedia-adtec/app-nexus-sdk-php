<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// ProfileService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Profile Api service.
 *
 * @package AppNexus
 * @author Chris Mears <chris@exactdrive.com>
 * @version $Id$
 */
class ProfileService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Profile properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Profile+Service#ProfileService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        // General
        'code',                             // A custom code for the profile.
        'description',                      // Optional description.
        'is_template',                      // If true, the profile has been saved as a targeting template in the UI.
        'last_modified',                    // Time of last modification to this profile.
        // Frequency
        'max_lifetime_imps',                // The maximum number of impressions per person.
        'min_session_imps',                 // The minimum number of impressions per person per session.
        'max_session_imps',                 // The maximum number of impressions per person per session.
        'max_day_imps',                     // The maximum number of impressions per person per day.
        'min_minutes_per_imp',              // The minimum number of minutes between impressions per person.
        'max_page_imps',                    // The maximum number of impressions per page load from a single advertiser.
        // Targeting
        'daypart_timezone',                 // The timezone to be used with the daypart_targets.
        'daypart_targets',                  // The day parts during which to serve the campaign.
        'segment_targets',                  // The segment IDs to target, each of which has an associated action (include or exclude).
        'segment_group_targets',            // The segment groups to target.
        'segment_boolean_operator',         // If using segment_targets, this defines the Boolean logic between the segments specified. If using segment_group_targets, this defines the Boolean logic between the segment groups (the Boolean logic between segments in a group is defined directly in the segment_group_targets array). Possible values: and or or.
        'age_targets',                      // The list of age ranges to target for this profile.
        'gender_targets',                   // The gender targeting used for the profile.
        'country_targets',                  // The countries to be either excluded or included in a profile.
        'country_action',                   // Action to be taken on the country_targets list.
        'region_targets',                   // The regions/states to be either excluded or included in a profile.
        'region_action',                    // Action to be taken on the region_targets list.
        'dma_targets',                      // The IDs of demographic areas to be either excluded or included in a profile.
        'dma_action',                       // Action to be taken on the dma_targets list.
        'city_targets',                     // The IDs of cities/towns to be either included or excluded in a profile.
        'city_action',                      // Action to be taken on the city_targets list.
        'domain_targets',                   // List of domains to be either included or excluded in a profile.
        'domain_action',                    // Action to be taken on the domain_targets list.
        'domain_list_targets',              // The IDs of domains lists to either include or exclude in a profile.
        'domain_list_action',               // Action to be taken on the domain_list_targets list.
        'platform_placement_targets',       // RTB or other Networks' inventory you can target.
        'size_targets',                     // List of eligible sizes to be included in the profile.
        'inventory_source_targets',         // Inventory Source IDs to be included in a profile.
        'inventory_group_targets',          // Inventory Group IDs to be included in a profile.
        'member_targets',                   // Seller member IDs to be either excluded or included in a profile.
        'member_default_action',            // If it's null, then all members' inventory are targeted *unless* there are any included member targets.
        'publisher_targets',                // Managed/direct publisher IDs to be either excluded or included in a profile.
        'site_targets',                     // The sites IDs to be either excluded or included in a profile.
        'placement_targets',                // The placement IDs to be either excluded or included in a profile.
        'inventory_action',                 // Action to be taken on the inventory_targets, publisher_targets, site_targets, and placement_targets list.
        'content_category_targets',         // The content categories to target for this profile as well as whether to allow unknown categories.
        'deal_targets',                     // The deal IDs to be targeted by this profile.
        'platform_publisher_targets',       // Third party publisher IDs to be either excluded or included in a profile.
        'platform_content_category_targets', // List of network resold content categories to target for this profile.
        'use_inventory_attribute_targets',  // If true, the profile restricts to inventory that has only the allowed attributes in "inventory_attribute_targets".
        'trust',                            // Indicates the level of audit which inventory must meet in order to be eligible.
        'certified_supply',                 // If true, this profile will only target Certified Supply inventory.
        'allow_unaudited',                  // If true, this profile will allow unaudited inventory to pass targeting.
        'session_freq_type',                // Indicates how the number of impressions seen by the user are counted during the current browsing session.
        'inventory_attribute_targets',      // The IDs of inventory attributes to target for this profile.
        'intended_audience_targets',        // The intended audience targets.
        'language_targets',                 // The IDs of the browser languages to either include or exclude in the profile.
        'language_action',                  // Action to be taken on language_targets.
        'querystring_targets',              // The query string targets to either include or exclude in the profile, as defined by the querystring_action field.
        'querystring_action',               // Action to be taken on the querystring_targets.
        'querystring_boolean_operator',     // Boolean logic to be applied to the querystring_targets.
        'zip_targets',                      // The zip codes to target.
        'supply_type_targets',              // The type(s) of supply to either include in or exclude from targeting, as defined by the supply_type_action field.
        'supply_type_action',               // Supply types are "web", "mobile_web", "mobile_app", and "facebook_sidebar".
        'user_group_targets',               // Every user is randomly assigned to 1 of 100 user groups, no group holding any advantage over another.
        'position_targets',                 // The fold positions to target.
        'browser_targets',                  // The IDs of browsers to either include in or exclude from your targeting.
        'browser_action',                   // Action to be taken on the browser_targets.
        'location_target_latitude',         // The latitude of the user's location.
        'location_target_longitude',        // The longitude of the user's location.
        'location_target_radius',           // See "location_target_latitude" for more information.
        'device_model_targets',             // The models of mobile devices (i.e., IPhone) to either include in or exclude from your targeting.
        'device_model_action',              // Action to be taken on device_model_targets.
        'device_type_targets',              // The types of devices to either include in or exclude from your targeting.
        'device_type_action',               // Action to be taken on device_type_targets.
        'carrier_targets',                  // The mobile carriers to either include in or exclude from your targeting.
        'carrier_action',                   // Action to be taken on the carrier_targets.
        'operating_system_family_targets',  // The operating systems as a whole (e.g., Android, Apple iOS, Windows 7, etc.).
        'operating_system_family_action',   // Action to be taken on operating_system_family_targets.
        'use_operating_system_extended_targeting', // If true, the operating_system_extended_targets field will be respected.
        'operating_system_extended_targets', // The list of specific operating systems to either include in or exclude from your targeting.
        'operating_system_action',           // Deprecated. Please use operating_system_extended_targets instead.
        'operating_system_targets',         // Deprecated. Please use operating_system_extended_targets instead.
        'require_cookie_for_freq_cap',      // Indicates whether you want to serve only to users that use cookies in order to maintain your frequency cap settings.
        'mobile_app_instance_targets',      // A list of mobile app instances that you'd like to include or exclude from targeting.
        'mobile_app_instance_action_include', // Whether to include the mobile app instances defined in mobile_app_instance_targets in your campaign targeting.
        'mobile_app_instance_list_targets', // This list contains mobile app instance lists (in other words, it's a list of lists).
        'mobile_app_instance_list_action_include', // Whether to include the mobile app instance lists defined in mobile_app_instance_list_targets in your campaign targeting.
        'ip_range_list_targets',            // A list of IP address ranges to be included or excluded from campaign targeting.
        'exclude_unknown_seller_member_group',
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus Profile service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/profile';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new Profile.
     *
     * @param  int $advertiserId => Advertiser id of profile.
     * @param  \stdClass $profile => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $profile => Newly created AppNexus profile.
     */
    public static function addProfile( $advertiserId, $profile )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createProfileHash( $profile );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::POST, $data );

        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing Profile.
     *
     * @param  int $id => Id of profile.
     * @param  int $advertiserId => Id of the associated advertiser.
     * @param  \stdClass $profile => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $profile => Updated AppNexus profile.
     */
    public static function updateProfile( $id, $advertiserId, $profile )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createProfileHash( $profile );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::PUT, $data );

        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View all Profiles for an advertiser; results are paged.
     *
     * @param  int $advertiserId
     *
     * @return AppNexusArray $profiles
     */
    public static function getAllProfiles( $advertiserId )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific Advertiser's Profile.
     *
     * @param  int $id
     * @param  int $advertiserId
     *
     * @return AppNexusObject $profile
     */
    public static function getProfile( $id, $advertiserId )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    /**
     * Delete a specific Advertiser's Profile.
     *
     * @param  int $id
     * @param  int $advertiserId
     *
     * @return bool
     */
    public static function deleteProfile( $id, $advertiserId )
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

    //-------------------------------------------------------------------------
    // private methods
    //-------------------------------------------------------------------------

    /**
     * Returns a Profile hash containing only the fields which are allowed
     * to be updated in the format accepted by AppNexus.
     *
     * @param \stdClass $profile
     *
     * @return array|object
     */
    private static function _createProfileHash( $profile )
    {
        if (empty( $profile )) {
            return null;
        }

        $pruned = new \stdClass();
        foreach (self::$fields as $key) {
            if (property_exists( $profile, $key )) {
                $pruned->$key = $profile->$key;
            }
        }

        // return null if no valid fields found
        return empty( $pruned ) ? null : (object) array( 'profile' => $pruned );
    }

}
