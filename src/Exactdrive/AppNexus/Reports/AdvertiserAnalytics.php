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
class Reports_AdvertiserAnalytics extends Reporting
{
    /**
     * Get the report name to pull the report.
     *
     * @return string
     */
    public function getReportName()
    {
        return 'advertiser_analytics';
    }

    /**
     * Get the possibly reporting intervals.
     *
     * @return array
     */
    public static function getIntervals()
    {
        return array(
            'current_hour' => 'Current Hour',
            'last_hour' => 'Last Hour',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_48_hours' => '48 Hours',
            'last_2_days' => '2 Days',
            'last_7_days' => '7 Days',
            'month_to_date' => 'Month To Date',
            'quarter_to_date' => 'Quarter To Date',
            'last_month' => 'Last Month',
            'lifetime' => 'Lifetime',
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
            'creative' => 'Creative',
            'geo_country' => 'Country',
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
        return array();
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
            'total_convs' => 'Conversions',
            'ctr' => 'CTR',
            'convs_rate' => 'Conversion Rate',
            'ecpm' => 'CPM',
            'ecpc' => 'CPC',
            'ecpa' => 'CPA',
            'spend' => 'Spend',
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

        if (count($this->getSelectedCountries()) > 0) {
            $data[] = array('geo_country' => $this->getSelectedCountries());
        }

        if (count($this->getSelectedLineItems()) > 0) {
            $data[] = array('line_item_id' => $this->getSelectedLineItems());
        }

        if (count($this->getSelectedCampaigns()) > 0) {
            $data[] = array('campaign_id' => $this->getSelectedCampaigns());
        }

        if (count($this->getSelectedCreatives()) > 0) {
            $data[] = array('creative_id' => $this->getSelectedCreatives());
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
        return '';
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
        return '';
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
