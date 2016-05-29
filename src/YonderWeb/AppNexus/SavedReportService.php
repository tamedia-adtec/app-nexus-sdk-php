<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// SavedReportService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Saved Report Api service.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class SavedReportService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Saved Report properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Saved+Report+Service#SavedReportService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'name',       // name of the report which is display in the ui
        'report',     // report defined in the same format as used in the reporting service
        'scheduling', // frequency with which the report is executed: 'daily', 'weekly', 'monthly'
        'forma',      // format in which the report results will be saved
        'emails',     // list of emails to send the report
        'expires_on', // date and time on which a saved report expires
        'created_on', // date and time on which the saved report was created
        'entity_id',  // entity id of the user creating the report
        'category'    // category of the report
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus saved report service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/saved-report';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new saved report.
     *
     * @param  int  $advertiserId => Advertiser id of saved report.
     * @param  hash $savedReport  => Only valid fields will be passed to api.
     * @return int  $appnexus_id  => Newly created appnexus saved report id.
     */
    public static function addSavedReport($advertiserId, $savedReport)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'advertiser_id' => $advertiserId
        ));

        // package up the data, don't bother running query on invalid data
        $data = self::_createSavedReportHash($savedReport);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::POST, $data);

        return $response['id'];
    }

    //-------------------------------------------------------------------------

    /**
     * Update an existing saved report.
     *
     * @param  int  $id           => Id of saved report.
     * @param  int  $advertiserId => Advertiser id of saved report.
     * @param  hash $savedReport  => Only valid fields will be passed to api.
     * @return int  $appnexus_id  => Updated appnexus saved report id.
     */
    public static function updateSavedReport($id, $advertiserId, $savedReport)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'id'            => $id,
            'advertiser_id' => $advertiserId
        ));

        // package up the data, don't bother running query on invalid data
        $data = self::_createSavedReportHash($savedReport);
        if ($data == null) {
            return null;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::PUT, $data);

        return $response['id'];
    }

    //-------------------------------------------------------------------------

    /**
     * View all saved reports, results are paged.
     *
     * @param  int   $advertiserId => Advertiser id to filter with.
     * @return array $savedReports
     */
    public static function getAllSavedReports($advertiserId,
        $start_element = 0, $num_elements = 100)
    {
        // construct url
        $url = self::getBaseUrl() . '?' . http_build_query(array(
            'advertiser_id' => $advertiserId,
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
     * View a specific saved report.
     *
     * @param  int  $id
     * @return hash $savedReport
     */
    public static function getSavedReport($id)
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
     * Delete a saved report.
     *
     * @param  int  $id
     * @return bool $status
     */
    public static function deleteSavedReport($id)
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
     * Returns a saved report hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param  hash $savedReport
     * @return hash $savedReport
     */
    private static function _createSavedReportHash($savedReport)
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $savedReport)) {
                $pruned[$key] = $savedReport[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : array('saved-report' => $pruned);
    }

}
