<?php

declare(strict_types = 1);

namespace Exactdrive\AppNexus;

use Exception;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

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
        $url = Api::getBaseUrl() . '/creative-upload';

        return $url;
    }

    /**
     * @param string $fileName
     * @param int $memberId
     * @param Logger|null $debugLogger
     *
     * @throws Exception
     *
     * @return string
     */
    public static function uploadCreative(string $fileName, int $memberId, ?Logger $debugLogger): string
    {
        $query = [
            'member_id' => $memberId,
        ];

        $data = [
            'type' => self::TYPE_HTML,
            'file'  => realpath($fileName),
        ];

        $url = self::getBaseUrl() . '?' . http_build_query($query);
        $response = self::makeRequest($url, Api::POST, $data, $debugLogger);

        return json_encode($response);
    }
}
