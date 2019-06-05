<?php

declare(strict_types = 1);

namespace Exactdrive\AppNexus;

use Exception;

/**
 * Class MemberService
 *
 * @package Exactdrive\AppNexus
 */
class MemberService extends Api
{
    /**
     * @throws Exception
     *
     * @return string
     */
    public static function getBaseUrl(): string
    {
        $url = Api::getBaseUrl() . '/member';

        return $url;
    }

    /**
     * @throws Exception
     *
     * @return AppNexusObject
     */
    public static function getMember(): AppNexusObject
    {
        $url = self::getBaseUrl();
        $response = self::makeRequest($url, Api::GET);

        return new AppNexusObject($response, AppNexusObject::MODE_READ_WRITE);
    }
}
