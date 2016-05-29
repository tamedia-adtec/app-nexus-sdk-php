<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// ContentCategoryService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Content Category Api service.
 *
 * @package AppNexus
 * @author Chris Mears <chris@exactdrive.com>
 * @version $Id$
 */
class ContentCategoryService extends Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Content Category properties.
     *   https://wiki.appnexus.com/display/api/Content+Category+Service#ContentCategoryService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'id',               // Integer
        'name',             // String
        'description',      // String
        'is_system',        // READ ONLY. Boolean
        'parent_category',  // Object (see docs)
        'type',             // Enum('standard')
        'last_modified',    // READ ONLY. timestamp
        'category_type'
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus content category service url.
     */
    public static function getBaseUrl()
    {
        $url = Api::getBaseUrl() . '/content-category';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * View all custom content categories belonging to your member
     *
     * @return array $countries
     */
     public static function getAllUniversalCategories(
         $start_element = 0, $num_elements = 100)
     {
         // construct url
         $url = self::getBaseUrl() . '?' . http_build_query(array(
             'category_type' => 'universal',
             'start_element' => $start_element,
             'num_elements'  => $num_elements
         ));

         // query app nexus server
         $response = self::makeRequest($url, Api::GET);

         // wrap response with app nexus object
         return new AppNexusArray($response, Object::MODE_READ_ONLY);
     }

}
