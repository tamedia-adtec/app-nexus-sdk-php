<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// ReportService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Report Api service.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class ReportService extends Api
{
    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Report properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Report+Service#ReportService-RESTAPIforDataRetrieval.
     *
     * @var array
     */
    public static $fields = array(
        'report_type',            // determines which information will be returned
        'timezone',               // determines which timezone the data will be reported in
        'filters',                // list of filter objects to apply to the report
        'group_filters',          // allows specifying an operation to perform on one or more filters
        'columns',                // list of columns to include in the report
        'row_per',                // for most reports, selected dimensions are grouped automatically
        'groups',                 // same as row_per
        'start_date',             // start date for the report
        'end_date',               // end date for the report
        'report_interval',        // time range for the report
        'orders',                 // list of columns to sort by
        'format',                 // format in which the report data will be returned
        'reporting_decimal_type', // decimal mark used in the report: 'comma', 'decimal'
        'emails',                 // list of email addresses to which the reporting data will be sent
        'escape_fields',           // when true, it adds quotes around each field in the report output
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * AppNexus report service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/report';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * AppNexus report download service url.
     */
    public static function getDownloadUrl()
    {
        $url = Api::getBaseUrl().'/report-download';

        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Request a new report.
     *
     * @param int $advertiserId => Advertiser id of report.
     * @param array $report => Only valid fields will be passed to api.
     *
     * @return int $report_id    => Requested appnexus report id.
     */
    public static function requestReport( $advertiserId = null, $report )
    {
        // construct url
        $url = self::getBaseUrl();
        if ($advertiserId !== null) {
            $url .= '?'.http_build_query(
                    array(
                        'advertiser_id' => $advertiserId,
                    )
                );
        }

        // package up the data, don't bother running query on invalid data
        $data = self::_createReportHash( $report );
        if ($data == null) {
            return 0;
        }

        // query app nexus server
        $response = self::makeRequest( $url, Api::POST, $data );

        return $response['report_id'];
    }

    //-------------------------------------------------------------------------

    /**
     * Request report information and status.
     *
     * @param int $id => Id of report.
     *
     * @return array $response => Query response.
     */
    public static function getReport( $id )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'id' => $id,
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::GET );

        // [moiz] once we can encapsulate in a report object can save the
        //   execution status in there, for now just return the response so
        //   we don't have to mess to much with the existing reporting code
        return $response;
    }

    //-------------------------------------------------------------------------

    /**
     * Request report id of saved report.
     *
     * @param int $id => Id of saved report.
     *
     * @return array $report_id => Id of report.
     */
    public static function getSavedReportId( $id )
    {
        // construct url
        $url = self::getBaseUrl().'?'.http_build_query(
                array(
                    'saved_report_id' => $id,
                )
            );

        // query app nexus server
        $response = self::makeRequest( $url, Api::POST );

        return $response['report_id'];
    }

    //-------------------------------------------------------------------------

    /**
     * Download a report.
     *
     * @param int $id => Id of report.
     *
     * @return array $response => Query response.
     */
    public static function downloadReport( $id )
    {
        // construct url
        $url = self::getDownloadUrl().'?'.http_build_query(
                array(
                    'id' => $id,
                )
            );

        // query app nexus server
        $response = self::makeRequestRaw( $url, Api::GET );

        return $response;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns an report hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param array $report
     *
     * @return array $report
     */
    private static function _createReportHash( $report )
    {
        $pruned = array();
        foreach (self::$fields as $key) {
            if (array_key_exists( $key, $report )) {
                $pruned[$key] = $report[$key];
            }
        }

        // return null if no valid fields found
        return empty( $pruned ) ? null : array( 'report' => $pruned );
    }
}
