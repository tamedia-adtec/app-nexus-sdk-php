<?php

declare(strict_types = 1);

namespace Exactdrive\AppNexus;

use Exception;

/**
 * Class CreativeHTMLService
 *
 * @package Exactdrive\AppNexus
 */
class CreativeHTMLService extends Api
{
    const TARGET_ADVERTISER = 'target_advertiser';
    const TARGET_PUBLISHER = 'target_publisher';

    /**
     * Creative properties which can be updated with AppNexus server.
     *   https://wiki.appnexus.com/pages/viewpage.action?title=Creative+HTML+Service&spaceKey=api#CreativeHTMLService-JSONFields
     *
     * @var array
     */
    public static $fields = [
        'adservers',
        'advertiser_id',
        'adx_audit',
        'allow_audit',
        'allow_safety_pacing',
        'allow_ssl_audit',
        'audit_feedback',
        'audit_status',
        'brand',
        'brand_id',
        'campaigns',
        'categories',
        'click_track_result',
        'click_url',
        'code',
        'code2',
        'competitive_brands',
        'competitive_categories',
        'created_on',
        'currency',
        'custom_macros',
        'daily_budget',
        'daily_budget_imps',
        'enable_pacing',
        'file_name',
        'first_run',
        'folder',
        'id',
        'is_control',
        'is_expired',
        'is_hosted',
        'is_prohibited',
        'is_self_audited',
        'language',
        'last_modified',
        'last_run',
        'lifetime_budget',
        'lifetime_budget_imps',
        'line_items',
        'media_assets',
        'media_url',
        'media_url_secure',
        'member_id',
        'name',
        'profile_id',
        'publisher_id',
        'segments',
        'sla',
        'sla_eta',
        'ssl_status',
        'state',
        'status',
        'technical_attributes',
        'template',
        'type',
    ];

    /**
     * @throws Exception
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/creative-html';

        return $url;
    }

    /**
     * @param int    $targetId
     * @param array  $creative
     * @param string $target
     *
     * @throws Exception
     *
     * @return AppNexusObject|null
     */
    public static function addCreativeHTML(int $targetId, array $creative, string $target = self::TARGET_ADVERTISER):? AppNexusObject
    {
        switch ($target) {
            case self::TARGET_ADVERTISER:
                $http_query = http_build_query(['advertiser_id' => $targetId]);

                break;
            case self::TARGET_PUBLISHER:
                $http_query = http_build_query(['publisher_id' => $targetId]);

                break;
            default:
                $http_query = http_build_query(['advertiser_id' => $targetId]);

                break;
        }

        $url = self::getBaseUrl() . '?' . $http_query;

        $data = self::_createCreativeHTMLHash($creative);
        if ($data == null) {
            return null;
        }

        $response = self::makeRequest($url, Api::POST, $data);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    /**
     * @param int    $creativeId
     * @param int    $targetId
     * @param array  $creative
     * @param string $target
     *
     * @throws Exception
     *
     * @return AppNexusObject|null
     */
    public static function updateCreativeHTML(int $creativeId, int $targetId, array $creative, string $target = self::TARGET_ADVERTISER): ?AppNexusObject
    {
        switch ($target) {
            case self::TARGET_ADVERTISER:
                $http_query = http_build_query(['id' => $creativeId, 'advertiser_id' => $targetId]);

                break;
            case self::TARGET_PUBLISHER:
                $http_query = http_build_query(['id' => $creativeId, 'publisher_id' => $targetId]);

                break;
            default:
                $http_query = http_build_query(['id' => $creativeId, 'advertiser_id' => $targetId]);

                break;
        }

        $url = self::getBaseUrl() . '?' . $http_query;
        $data = self::_createCreativeHTMLHash($creative);
        if ($data == null) {
            return null;
        }

        $response = self::makeRequest($url, Api::PUT, $data);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    /**
     * @param int|null $targetId
     * @param string   $target
     * @param int      $startElement
     * @param int      $numElements
     *
     * @throws Exception
     *
     * @return AppNexusObject|null
     */
    public static function getAllHTMLCreatives(
        ?int $targetId = null,
        string $target = self::TARGET_ADVERTISER,
        int $startElement = 0,
        int $numElements = 100
    ): ?AppNexusObject {
        $query = [
            'start_element' => $startElement,
            'num_elements' => $numElements,
        ];

        if ($targetId != null) {
            if ($target == self::TARGET_ADVERTISER) {
                $query['advertiser_id'] = $targetId;
            } elseif ($target ==self::TARGET_PUBLISHER) {
                $query['publisher_id'] = $targetId;
            }
        }

        $url = self::getBaseUrl() . '?' . http_build_query($query);
        $response = self::makeRequest($url, Api::GET);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    /**
     * @param int[] $ids
     *
     * @throws Exception
     *
     * @return AppNexusArray
     */
    public static function getHTMLCreatives(array $ids): AppNexusArray
    {
        $url = self::getBaseUrl() . '?' . http_build_query(['id' => implode(',', $ids)]);

        $response = self::makeRequest($url, Api::GET);
        if (count($ids) == 1) {
            $key = $response['dbg_info']['output_term'];
            $response[$key] = [$response[$key]];
        }

        return new AppNexusArray($response, AppNexusObject::MODE_READ_WRITE);
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return AppNexusObject
     */
    public static function getCreativeHTML(int $id): AppNexusObject
    {
        $url = self::getBaseUrl() . '?' . http_build_query(['id' => $id]);
        $response = self::makeRequest($url, Api::GET);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }

    /**
     * @param int    $id
     * @param int    $targetId
     * @param string $target
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function deleteCreativeHTML(int $id, int $targetId, string $target = self::TARGET_ADVERTISER): bool
    {
        switch ($target) {
            case self::TARGET_ADVERTISER:
                $http_query = http_build_query(['id' => $id, 'advertiser_id' => $targetId]);

                break;
            case self::TARGET_PUBLISHER:
                $http_query = http_build_query(['id' => $id, 'publisher_id' => $targetId]);

                break;
            default:
                $http_query = http_build_query(['id' => $id, 'advertiser_id' => $targetId]);

                break;
        }

        $url = self::getBaseUrl() . '?' . $http_query;
        self::makeRequest($url, Api::DELETE);

        return true;
    }

    /**
     * Returns a creative hash containing only the fields which are allowed
     *  to be updated in the format accepted by AppNexus.
     *
     * @param array $creative
     *
     * @return array $creative
     */
    private static function _createCreativeHTMLHash($creative)
    {
        $pruned = [];
        foreach (self::$fields as $key) {
            if (array_key_exists($key, $creative)) {
                $pruned[$key] = $creative[$key];
            }
        }

        // return null if no valid fields found
        return empty($pruned) ? null : ['creative-html' => $pruned];
    }
}
