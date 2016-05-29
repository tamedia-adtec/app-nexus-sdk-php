<?php

namespace YonderWeb\AppNexus;

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
class Reporting
{
    /**
     * @var array
     */
    public static $reports = array(
        'AdvertiserAnalytics' => 'Advertiser Analytics',
        'SiteDomainPerformance' => 'Site Domain Performance',
    );

    const CUSTOM_DATE = 'custom';

    /**
     * Pending report status.
     *
     * @var string
     */
    const PENDING_STATUS = 'pending';
    const READY_STATUS = 'ready';

    /**
     * Advertiser id to pull reports from.
     *
     * @var string
     */
    protected $_advertiserId;

    /**
     * Report id of report being pulled.
     *
     * @var string
     */
    protected $_reportId;

    /**
     * Report name.
     *
     * @var string
     */
    protected $_reportName;

    /**
     * Request data to send to reporting API.
     *
     * @var string
     */
    protected $_requestData;

    /**
     * User selected interval.
     *
     * @var string
     */
    protected $_selectedInterval;

    /**
     * User selected filters.
     *
     * @var array
     */
    protected $_selectedFilters;

    /**
     * User selected metrics.
     *
     * @var array
     */
    protected $_selectedMetrics;

    /**
     * User selected groups.
     *
     * @var array
     */
    protected $_selectedGroups;

    /**
     * The user selected countries.
     *
     * @var array
     */
    protected $_selectedCountries;

    /**
     * User selected line items (advertisers in ExactDrive system).
     *
     * @var array
     */
    protected $_selectedLineItems;

    /**
     * User selected campaigns.
     *
     * @var array
     */
    protected $_selectedCampaigns;

    /**
     * User selected creatives.
     *
     * @var array
     */
    protected $_selectedCreatives;

    /**
     * Custom start date.
     *
     * @var string
     */
    protected $_startDate;

    /**
     * Custom end date.
     *
     * @var string
     */
    protected $_endDate;

    /**
     * Saved report name.
     *
     * @var string
     */
    protected $_savedReportName;

    /**
     * Saved report scheduling.
     *
     * @var string
     */
    protected $_savedReportScheduling;

    /**
     * Fetch the user report.
     */
    public function fetchReport()
    {
        $reportId = ReportService::requestReport(
            $this->getAdvertiserId(),
            $this->getRequestData()
        );
        $this->setReportId($reportId);

        // give appnexus some time to create the report
        // sleep(3);

        return $this->downloadReport($reportId);
    }

    /**
     * Check the status of the report.
     */
    public function fetchStatus($reportId)
    {
        $response = ReportService::getReport($reportId);

        return $response['execution_status'];
    }

    /**
     * Download the report.
     */
    public function downloadReport($reportId)
    {
        $response = ReportService::getReport($reportId);

        if ($response['execution_status'] != self::READY_STATUS) {
            error_log(print_r($response, true));

            return;
        }

        // download the report
        $downloadReport = ReportService::downloadReport($reportId);

        // This will be an array of all possible headings to show in the report
        $filtersAndMetrics = array();
        $filtersAndMetrics = array_merge($this->getStaticFilters(), $filtersAndMetrics);
        $filtersAndMetrics = array_merge($this->getFilters(), $filtersAndMetrics);
        $filtersAndMetrics = array_merge($this->getMetrics(), $filtersAndMetrics);
        $filtersAndMetrics = array_merge($this->getTimeGroups(), $filtersAndMetrics);

        return $this->parseDownloadedReport($downloadReport, $filtersAndMetrics);
    }

    /**
     * Download the report.
     */
    public function downloadSavedReport($id)
    {
        $reportId = ReportService::getSavedReportId($id);
        $this->setReportId($reportId);

        return $this->downloadReport($reportId);
    }

    /**
     * Get raw data to export the report.
     *
     * @param string $id
     * @param string $resellerName
     */
    public function exportReport($id, $resellerName = 'Report')
    {
        $initialReport = ReportService::downloadReport($id);

        // $initialReport = str_replace("'", "", $initialReport);
        // $initialReport = str_replace('"', "'", $initialReport);

        // We dont know what type of report we have so just GET ALL THE THINGS. If new reports are added they need added here also
        $advertiserFiltersAndMetrics = array_merge(Reports_AdvertiserAnalytics::getFilters(), Reports_AdvertiserAnalytics::getMetrics(), Reports_AdvertiserAnalytics::getTimeGroups());
        $siteDomainFiltersAndMetrics = array_merge(Reports_SiteDomainPerformance::getFilters(), Reports_SiteDomainPerformance::getMetrics(), Reports_SiteDomainPerformance::getStaticFilters());

        $report = $this->parseDownloadedReport($initialReport, array_merge($advertiserFiltersAndMetrics, $siteDomainFiltersAndMetrics), '.', '', '', '', true);

        // create a unique has so we never get any name collisions when temp file is created
        $seed = microtime(true).'JvKnrQWPsThuJteNQAuH';
        $hash = sha1(uniqid($seed.mt_rand(), true));
        $hash = substr($hash, 0, 15);

        $safeResellerName = str_replace(' ', '_', $resellerName);
        $fileName = $safeResellerName.'_AnalyticsReport_'.date('m_d_y').'_'.$hash.'_csv.csv';
        $fileDirectory = getcwd().'/resellers/temp_reports/'.$resellerName;
        $fileLocation = $fileDirectory.'/'.$fileName;

        // If directory does not exist then create it
        if (!file_exists($fileDirectory)) {
            mkdir($fileDirectory);
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Pragma: no-cache');
        header('Expires: 0');

        $fp = fopen($fileLocation, 'w');

        fputcsv($fp, $report['heading']);

        if (array_key_exists('body', $report)) {
            foreach ($report['body'] as $row) {
                fputcsv($fp, $row);
            }
        }

        $footerFields = array();
        foreach ($report['heading'] as $item) {
            if (array_key_exists($item, $report['footer'])) {
                $footerFields[] = $report['footer'][$item];
            } else {
                $footerFields[] = '--';
            }
        }
        fputcsv($fp, $footerFields);

        fclose($fp);
        ob_clean();
        flush();
        readfile($fileLocation);
        unlink($fileLocation);
    }

    /**
     * Get the request data.
     *
     * @return null|string
     */
    public function getRequestData()
    {
        if (!$this->_requestData) {

            // set parameters
            $report = array(
                'report_type' => $this->getReportName(),
                'columns' => $this->getRequestDataColumns(),
                'filters' => $this->getUserSelectedFiltersDetails(),
                'row_per' => $this->getSelectedGroups(),
                'format' => 'csv',
            );

            // set inverval
            if ($this->getStartDate() && $this->getEndDate()) {
                $startDate = new DateTime($this->getStartDate());
                $endDate = new DateTime($this->getEndDate());
                $report['start_date'] = $startDate->format('Y-m-d');
                $report['end_date'] = $endDate->format('Y-m-d');
            } else {
                $report['report_interval'] = $this->getSelectedInterval();
            }

            $this->_requestData = $report;
        }

        return $this->_requestData;
    }

    /**
     * Get the request data for saved report.
     *
     * @return null|string
     */
    public function getSavedRequestData()
    {
        // set parameters
        $savedReport = array(
            'name' => $this->getSavedReportName(),
            'report' => $this->getRequestData(),
            'scheduling' => $this->getSavedReportScheduling(),
            'expires_on' => date('Y-m-d', strtotime('+1 years')).' 0:00:00',
            'format' => 'csv',
        );

        $this->_requestData = $savedReport;

        return $this->_requestData;
    }

    /**
     * Format the columns to show in report data json.
     *
     * @return array
     */
    public function getRequestDataColumns()
    {
        $data = array_merge($this->getSelectedFilters(), $this->getSelectedMetrics());

        return array_merge($this->getSelectedGroups(), $data);
    }

    /**
     * Set the advertiser ID.
     *
     * @param string $advertiserId
     *
     * @return Reporting $this
     */
    public function setAdvertiserId($advertiserId)
    {
        $this->_advertiserId = $advertiserId;

        return $this;
    }

    /**
     * Get the advertiser ID to use to pull report.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getAdvertiserId()
    {
        if (!$this->_advertiserId) {
            throw new Exception('Advertiser ID for reporting is not set');
        }

        return $this->_advertiserId;
    }

    /**
     * Set the report ID.
     *
     * @param string $reportId
     *
     * @return Reporting $this
     */
    public function setReportId($reportId)
    {
        $this->_reportId = $reportId;

        return $this;
    }

    /**
     * Get the report ID.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getReportId()
    {
        if (!$this->_reportId) {
            throw new Exception('Report ID for reporting is not set');
        }

        return $this->_reportId;
    }

    /**
     * Parse the CSV data returned from AppNexus.
     *
     * @param string $report
     * @param array  $filtersAndMetrics
     * @param string $decimal
     * @param string $thousands
     * @param string $dollar
     * @param string $percent
     * @param bool   $export
     *
     * @return array
     */
    public function parseDownloadedReport($report, $filtersAndMetrics, $decimal = '.', $thousands = ',', $dollar = '$', $percent = '%', $export = false)
    {
        $possibleWholeNumberMetrics = array('Impressions', 'Clicks', 'Conversions');
        $possibleMoneyMetrics = array('Spend', 'CPM', 'CPC', 'CPA', 'Cost Per Click', 'Media Cost');
        $possibleConvertToPercent = array('Conversion Rate', 'CTR');

        $totalImps = 0;
        $totalClicks = 0;
        $totalConversions = 0;
        $totalCtr = 0;
        $totalConversionRate = 0;
        $totalCpc = 0;
        $totalCpa = 0;
        $totalCpm = 0;
        $totalSpend = 0;
        $totalMediaCost = 0;

        $nonValidatedReport = explode("\r\n", $report);
        $initialReport = array();

        // Loop through the raw data and weed out any empty rows
        foreach ($nonValidatedReport as $report) {
            if (!empty($report)) {
                $initialReport[] = $report;
            }
        }

        $finalData = array();
        $finalData['footer'] = array();

        $totalRows = count($initialReport);
        for ($i = 0; $i < $totalRows; ++$i) {
            $row = str_getcsv($initialReport[$i]);

            if ($i === 0) {
                // This is the heading
                foreach ($row as $item) {
                    $finalData['heading'][] = $filtersAndMetrics[$item];
                }
            } else {
                $formattedRow = array();

                foreach ($row as $key => $value) {
                    if (array_key_exists($key, $finalData['heading'])) {
                        $headingItem = $finalData['heading'][$key];

                        if (in_array($headingItem, $possibleWholeNumberMetrics)) {
                            // This should be a string converted to a whole number
                            $intValue = intval($value);
                            $formattedRow[$key] = number_format($intValue, 0, $decimal, $thousands);

                            switch ($headingItem) {
                                case 'Impressions':
                                    $totalImps += $intValue;
                                    $finalData['footer'][$headingItem] = number_format($totalImps, 0, $decimal, $thousands);
                                    break;
                                case 'Clicks':
                                    $totalClicks += $intValue;
                                    $finalData['footer'][$headingItem] = number_format($totalClicks, 0, $decimal, $thousands);
                                    break;
                                case 'Conversions':
                                    $totalConversions += $intValue;
                                    $finalData['footer'][$headingItem] = number_format($totalConversions, 0, $decimal, $thousands);
                                    break;
                            }
                        } elseif (in_array($headingItem, $possibleMoneyMetrics)) {
                            // This should be formatted for money
                            $formattedRow[$key] = $dollar.number_format((float) $value, 2, $decimal, $thousands);

                            switch ($headingItem) {
                                case 'CPC':
                                    $totalCpc += $value;
                                    $finalData['footer'][$headingItem] = $dollar.number_format(($totalCpc / ($totalRows - 1)), 2, $decimal, $thousands);
                                    break;
                                case 'CPA':
                                    $totalCpa += $value;
                                    $finalData['footer'][$headingItem] = $dollar.number_format(($totalCpa / ($totalRows - 1)), 2, $decimal, $thousands);
                                    break;
                                case 'CPM':
                                    $totalCpm += $value;
                                    $finalData['footer'][$headingItem] = $dollar.number_format(($totalCpm / ($totalRows - 1)), 2, $decimal, $thousands);
                                    break;
                                case 'Spend':
                                    $totalSpend += $value;
                                    $finalData['footer'][$headingItem] = $dollar.number_format($totalSpend, 2, $decimal, $thousands);
                                    break;
                                case 'Media Cost':
                                    $totalMediaCost += $value;
                                    $finalData['footer'][$headingItem] = $dollar.number_format($totalMediaCost, 2, $decimal, $thousands);
                                    break;
                            }
                        } elseif (in_array($headingItem, $possibleConvertToPercent)) {
                            // This should be formatted for percentage
                            switch ($headingItem) {
                                case 'Conversion Rate':
                                    $formattedRow[$key] = number_format(($value * 100), 5, $decimal, $thousands).$percent;
                                    $totalConversionRate += $value;
                                    $finalData['footer'][$headingItem] = number_format((($totalConversionRate / ($totalRows - 1)) * 100), 5, $decimal, $thousands).$percent;
                                    break;
                                case 'CTR':
                                    // Client wants to export CTR differently from showing it in website
                                    if ($export) {
                                        $formattedRow[$key] = number_format($value, 5, $decimal, $thousands);
                                        $totalCtr += $value;
                                        $finalData['footer'][$headingItem] = number_format(($totalCtr / ($totalRows - 1)), 5, $decimal, $thousands);
                                    } else {
                                        $formattedRow[$key] = number_format(($value * 100), 3, $decimal, $thousands).$percent;
                                        $totalCtr += $value;
                                        $finalData['footer'][$headingItem] = number_format((($totalCtr / ($totalRows - 1)) * 100), 3, $decimal, $thousands).$percent;
                                    }
                                    break;
                            }
                        } else {
                            $formattedRow[$key] = $value;
                        }
                    }
                }
                $finalData['body'][$i - 1] = $formattedRow;
            }
        }

        return $finalData;
    }

    /**
     * Set the user selected filters.
     *
     * @param array $filters
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedFilters($filters)
    {
        $this->_selectedFilters = $filters;

        return $this;
    }

    /**
     * Get user selected filters.
     *
     * @return array
     */
    public function getSelectedFilters()
    {
        if (!$this->_selectedFilters || !is_array($this->_selectedFilters)) {
            $this->_selectedFilters = array();
        }

        return $this->_selectedFilters;
    }

    /**
     * The user selected interval.
     *
     * @param string $interval
     *
     * @return $this
     */
    public function setSelectedInterval($interval)
    {
        $this->_selectedInterval = $interval;

        return $this;
    }

    /**
     * Get the user selected interval.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSelectedInterval()
    {
        if (!$this->_selectedInterval) {
            $this->_selectedInterval = 'yesterday';
        }

        // Check to make sure the selected interval is a valid interval
        if (!array_key_exists($this->_selectedInterval, $this->getIntervals())) {
            throw new Exception('Reporting time interval is not set');
        }

        return $this->_selectedInterval;
    }

    /**
     * Set selected metrics.
     *
     * @param array $metrics
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedMetrics($metrics)
    {
        $this->_selectedMetrics = $metrics;

        return $this;
    }

    /**
     * Get the user selected metrics.
     *
     * @return array
     */
    public function getSelectedMetrics()
    {
        if (!$this->_selectedMetrics  || !is_array($this->_selectedMetrics)) {
            $this->_selectedMetrics = array('imps', 'clicks', 'ecpm', 'ecpc', 'ecpa', 'media_cost');
        }

        return $this->_selectedMetrics;
    }

    /**
     * Set the selected groups.
     *
     * @param array $groups
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedGroups($groups)
    {
        $this->_selectedGroups = $groups;

        return $this;
    }

    /**
     * Get user selected groups.
     *
     * @return array
     */
    public function getSelectedGroups()
    {
        if (!$this->_selectedGroups || !is_array($this->_selectedGroups)) {
            $this->_selectedGroups = array();
        }

        return $this->_selectedGroups;
    }

    /**
     * Set the user selected countries.
     *
     * @param array $countries
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedCountries($countries)
    {
        $this->_selectedCountries = $countries;

        return $this;
    }

    /**
     * Get the user selected countries.
     *
     * @return array
     */
    public function getSelectedCountries()
    {
        if (!$this->_selectedCountries || !is_array($this->_selectedCountries)) {
            return array();
        }

        return $this->_selectedCountries;
    }

    /**
     * Set the user selected line items.
     *
     * @param array $lineItems
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedLineItems($lineItems)
    {
        $this->_selectedLineItems = $lineItems;

        return $this;
    }

    /**
     * Get the user selected line items.
     *
     * @return array
     */
    public function getSelectedLineItems()
    {
        if (!$this->_selectedLineItems || !is_array($this->_selectedLineItems)) {
            return array();
        }

        return $this->_selectedLineItems;
    }

    /**
     * Set the user selected campaigns.
     *
     * @param array $campaigns
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedCampaigns($campaigns)
    {
        $this->_selectedCampaigns = $campaigns;

        return $this;
    }

    /**
     * Get the user selected campaigns.
     *
     * @return array
     */
    public function getSelectedCampaigns()
    {
        if (!$this->_selectedCampaigns || !is_array($this->_selectedCampaigns)) {
            return array();
        }

        return $this->_selectedCampaigns;
    }

    /**
     * Set the user selected creatives.
     *
     * @param array $creatives
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSelectedCreatives($creatives)
    {
        $this->_selectedCreatives = $creatives;

        return $this;
    }

    /**
     * Get the user selected creatives.
     *
     * @return array
     */
    public function getSelectedCreatives()
    {
        if (!$this->_selectedCreatives || !is_array($this->_selectedCreatives)) {
            return array();
        }

        return $this->_selectedCreatives;
    }

    /**
     * Set custom start date.
     *
     * @param string $startDate
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setStartDate($startDate)
    {
        $this->_startDate = $startDate;

        return $this;
    }

    /**
     * Get custom start date.
     *
     * @return bool|string
     */
    public function getStartDate()
    {
        if (!$this->_startDate) {
            return false;
        }

        return $this->_startDate;
    }

    /**
     * Set custom end date.
     *
     * @param string $endDate
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setEndDate($endDate)
    {
        $this->_endDate = $endDate;

        return $this;
    }

    /**
     * Get the custom end date.
     *
     * @return bool|string
     */
    public function getEndDate()
    {
        if (!$this->_endDate) {
            return false;
        }

        return $this->_endDate;
    }

    /**
     * Set saved report name.
     *
     * @param string $name
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSavedReportName($name)
    {
        $this->_savedReportName = $name;

        return $this;
    }

    /**
     * Get the custom end date.
     *
     * @return bool|string
     */
    public function getSavedReportName()
    {
        if (!$this->_savedReportName) {
            return false;
        }

        return $this->_savedReportName;
    }

    /**
     * Set saved report scheduling.
     *
     * @param string $scheduling
     *
     * @return Reports_AdvertiserAnalytics $this
     */
    public function setSavedReportScheduling($scheduling)
    {
        $this->_savedReportScheduling = $scheduling;

        return $this;
    }

    /**
     * Get saved report scheduling.
     *
     * @return bool|string
     */
    public function getSavedReportScheduling()
    {
        if (!$this->_savedReportScheduling) {
            return false;
        }

        return $this->_savedReportScheduling;
    }
}
