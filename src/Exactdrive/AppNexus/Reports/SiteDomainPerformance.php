<?php
/**
 * AppNexus Report API service.
 */
/**
 * AppNexus Report API service.
 *
 * @author Jason Michels <jmichels@nerdery.com>
 *
 * @version $Id$
 */
class Reports_SiteDomainPerformance extends Reporting
{
    /**
     * Get the report name to pull the report.
     *
     * @return string
     */
    public function getReportName()
    {
        return 'site_domain_performance';
    }

    /**
     * Get the possibly reporting intervals.
     *
     * @return array
     */
    public static function getIntervals()
    {
        return array(
            'yesterday' => 'Yesterday',
            'last_7_days' => '7 Days',
            'month_to_date' => 'Month To Date',
            'month_to_yesterday' => 'Month To Yesterday',
            'custom' => 'Custom Dates',
        );
    }

    /**
     * Get filters user can choose from.
     *
     * @return array
     */
    public static function getFilters()
    {
        return array(
            'line_item' => 'Advertiser',
            'campaign' => 'Campaign',
            'top_level_category' => 'Top Level Category',
            'second_level_category' => 'Second Level Category',
        );
    }

    /**
     * Get filters that are selected by default.
     *
     * @return array
     */
    public static function getDefaultFilters()
    {
        return array();
    }

    /**
     * Get filters that are always applied.
     *
     * @return array
     */
    public static function getStaticFilters()
    {
        return array(
            'site_domain' => 'Site Domain',
        );
    }

    /**
     * Get possibly metrics.
     *
     * @return array
     */
    public static function getMetrics()
    {
        return array(
            'imps' => 'Impressions',
            'clicks' => 'Clicks',
            'ctr' => 'CTR',
            'convs_rate' => 'Conversion Rate',
        );
    }

    /**
     * These are so a client can choose to a a new column to show the day, hour, and month of row.
     *
     * @return array
     */
    public static function getTimeGroups()
    {
        return array(
            'hour' => 'Hour',
            'day' => 'Day',
            'month' => 'Month',
        );
    }

    /**
     * Get the detailed filters user selected.
     *
     * @return array
     */
    public function getUserSelectedFiltersDetails()
    {
        $data = array();

        if (count($this->getSelectedLineItems()) > 0) {
            $data[] = array('line_item_id' => $this->getSelectedLineItems());
        }

        if (count($this->getSelectedCampaigns()) > 0) {
            $data[] = array('campaign_id' => $this->getSelectedCampaigns());
        }

        return $data;
    }

    /**
     * Get the minimum start date.
     *
     * @return string
     */
    public static function getMinStartDate()
    {
        return -30;
    }

    /**
     * Get the maximum start date.
     *
     * @return string
     */
    public static function getMaxStartDate()
    {
        return '';
    }
    /**
     * Get the minimum end date.
     *
     * @return string
     */
    public static function getMinEndDate()
    {
        return -30;
    }
    /**
     * Get the maximum end date.
     *
     * @return string
     */
    public static function getMaxEndDate()
    {
        return '';
    }
}
