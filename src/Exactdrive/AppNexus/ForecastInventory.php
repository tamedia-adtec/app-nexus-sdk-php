<?php

/*
 * This file is part of the app-nexus-sdk-php package.
 *
 * (c) Oliver Milanovic <omilanovic@codeframe.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exactdrive\AppNexus;

class ForecastInventory extends Api
{

    /**
     * Forecast properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/display/api/Inventory+Forecasting+Services#InventoryForecastingServices-ForecastContention-MultiService
     *
     * @var array
     */
    public static $fields = [
        'start_date',
        'end_date',
        'profile',
    ];

    /**
     * AppNexus report service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl().'/forecast-inventory-multi';

        return $url;
    }

    /**
     * View all campaigns for an advertiser results are paged.
     *
     * @param $forecast
     *
     * @return array|int
     * @throws \Exception
     */
    public static function getForecast($forecast)
    {
        // construct url
        $url = self::getBaseUrl();

        // package up the data, don't bother running query on invalid data
        $data = self::_createForecastHash($forecast);
        if ($data == null) {
            return 0;
        }

        // query app nexus server
        $response = self::makeRequest($url, Api::POST, $data);

        return $response;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Returns an report hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param array $forecast
     *
     * @return array $forecast
     */
    private static function _createForecastHash($forecast)
    {
        $pruned = [];
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $forecast)) {
                $pruned[$key] = $forecast[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : ['line_item' => $pruned, 'campaigns' => []];
    }
}
