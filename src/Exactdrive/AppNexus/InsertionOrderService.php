<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// InsertionOrderService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Insertion Order Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>, Oliver Milanovic <omilanovic@codeframe.ch>
 * @version $Id$
 */
class InsertionOrderService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Insertion order properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Insertion+Order+Service#InsertionOrderService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'state',                        // 'active' / 'inactive'
        'code',                         // custom code for the campaign
        'name',                         // name of the campaign
        'advertiser_id',                // id of the advertiser which the campaign belongs
        'start_date',                   // date and time when the campaign should start serving
        'end_date',                     // date and time when the campaign should stop serving
        'timezone',                     // timezone of the campaign
        'currency',                     // currency used for this insertion order
        'comments',                     // comments about the insertion order
        'billing_code',                 // for reference
        'spend_protection_pixels',      // for reference
        'labels',                       // optional: 'Trafficker', 'Sales Rep', 'Campaign Type'
        'broker_fees',                  // commissions that the network must pass to brokers when serving an ad
        'budget_intervals',
        'lifetime_pacing',
        'lifetime_budget',              // lifetime budget in revenue
        'lifetime_budget_imps',         // lifetime budget in impressions
        'enable_pacing',                // if true, daily budgeted spend is spread out evenly throughout a day
        'lifetime_pacing_span',
        'daily_budget',                 // daily budget in revenue
        'daily_budget_imps',            // daily budget in impressions
        'lifetime_pacing_pct',
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus insertion order service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/insertion-order';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new insertion order.
     *
     * @param  int $advertiserId => Advertiser id of insertion order.
     * @param  array $insertionOrder => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $insertionOrder     => Newly created appnexus insertion order.
     */
    public static function addInsertionOrder( $advertiserId, $insertionOrder )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createInsertionOrderHash( $insertionOrder );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::POST, $data );

        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing insertion order.
     *
     * @param  int $id => Id of insertion order.
     * @param  int $advertiserId => Advertiser id of insertion order.
     * @param  array $insertionOrder => Only valid fields will be passed to api.
     *
     * @return AppNexusObject $insertionOrder     => Updated appnexus insertion order.
     */
    public static function updateInsertionOrder( $id, $advertiserId, $insertionOrder )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id'            => $id,
                    'advertiser_id' => $advertiserId,
                )
            );

        // package up the data, don't bother running query on invalid data
        $data = self::_createInsertionOrderHash( $insertionOrder );
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::PUT, $data );

        return new AppNexusObject( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View all insertion orders for an advertiser, results are paged.
     *
     * @param int $advertiserId
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $insertionOrders
     */
    public static function getAllInsertionOrders(
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
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View insertion orders specified by ids, results are paged.
     *
     * @param  int[] $ids
     *
     * @return array|AppNexusArray
     */
    public static function getInsertionOrders( $ids )
    {
        // [moiz] need to fix this...

        // shortcut if only single id is specified
        if (count( $ids ) == 1) {
            return array( self::getInsertionOrder( $ids[0] ) );
        }

        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => implode( ',', $ids ),
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * View a specific insertion order.
     *
     * @param  int $id
     *
     * @return AppNexusObject $insertionOrder
     */
    public static function getInsertionOrder( $id )
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
     * Search for insertion orders with ids or names containing certain characters,
     *  results are paged.
     *
     * @param string $term
     * @param int $start_element
     * @param int $num_elements
     *
     * @return AppNexusArray $insertionOrders
     */
    public static function searchInsertionOrders(
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
        $response = self::makeRequest( $url, Api::GET );

        // wrap response with app nexus object
        return new AppNexusArray( $response, AppNexusObject::MODE_READ_WRITE );
    }

    //-------------------------------------------------------------------------

    /**
     * Delete a insertion order.
     *
     * @param  int $id => Id of insertion order.
     * @param  int $advertiserId => Advertiser id of insertion order.
     *
     * @return bool $status
     */
    public static function deleteInsertionOrder( $id, $advertiserId )
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
     * Returns a insertion order hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  array $insertionOrder
     *
     * @return array|null|object
     */
    private static function _createInsertionOrderHash( $insertionOrder )
    {
        if (is_object( $insertionOrder )) {
            $pruned = new \stdClass();
            foreach (self::$fields as $key) {
                if (property_exists( $insertionOrder, $key )) {
                    $pruned->$key = $insertionOrder->$key;
                }
            }

            // return null if no valid fields found
            return empty( $pruned ) ? null : (object) array( 'insertion-order' => $pruned );
        } else {
            $pruned = array();
            foreach (self::$fields as $key) {
                if (array_key_exists( $key, $insertionOrder )) {
                    $pruned[$key] = $insertionOrder[$key];
                }
            }

            // return null if no valid fields found
            return empty( $pruned ) ? null : array( 'insertion-order' => $pruned );
        }
    }

}
