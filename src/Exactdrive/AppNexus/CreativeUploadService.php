<?php

declare(strict_types = 1);

namespace Exactdrive\AppNexus;

use Exception;

/**
 * Class CreativeUploadService
 *
 * @see https://wiki.appnexus.com/display/api/Creative+Upload+Service+-+File+Format
 *
 * @package Exactdrive\AppNexus
 */
class CreativeUploadService extends Api
{
    const TYPE_HTML = 'html';

    public static $fields = [
        'type',
        'file',
    ];

    /**
     * @throws Exception
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . 'creative-upload';

        return $url;
    }

    /**
     * @param string $fileName
     *
     * @throws Exception
     *
     * @return AppNexusObject
     */
    public static function uploadCreative(string $fileName): AppNexusObject
    {
        $member_id = (MemberService::getMember())->id;
        $query = [
            'member_id' => $member_id,
        ];

        $data = [
            'type' => self::TYPE_HTML,
            'file'  => $fileName,
        ];

        $url = self::getBaseUrl() . '?' . http_build_query($query);
        $response = self::makeRequest($url, Api::GET, $data);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }
}
