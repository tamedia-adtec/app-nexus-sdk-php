<?php
//-----------------------------------------------------------------------------
// DomainListService.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Domain List API service.
 *
 * @package AppNexus
 * @author Chris Mears <chris@exactdrive.com>
 * @version $Id$
 */
class AppNexus_DomainListService extends AppNexus_Api
{

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * Domain List properties.
     * https://wiki.appnexus.com/display/api/Domain+List+Service#DomainListService-JSONFields
     *
     * @var array
     */
    public static $fields = array(
        'id',           // Integer
        'name',         // String
        'description',  // String
        'type',         // ['white', 'black']
        'domains',      // Array of Strings,
        'last_modified' // String
    );

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * App Nexus content category service url.
     */
    public static function getBaseUrl()
    {
        $url = AppNexus_Api::getBaseUrl() . '/domain-list';
        return $url;
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new Domain List
     *
     * @return array $domainList
     */
     public static function addDomainList($domainListData)
     {
         // construct url
         $url = self::getBaseUrl();

         // package up the data, don't bother running query on invalid data
         $data = self::_createHash($domainListData);
         if ($data == null) {
             return null;
         }

         // query app nexus server
         $response = self::makeRequest($url, AppNexus_Api::POST, $data);

         return new AppNexus_Object($response, AppNexus_Object::MODE_READ_WRITE);
     }

     /**
      * Update an existing domain list.
      *
      * @param  int  $id           => Id of domain list.
      * @param  hash $domainList   => Only valid fields will be passed to api.
      * @return hash $domainList   => Updated domain list.
      */
     public static function updateDomainList($id, $domainListData)
     {
         // construct url
         $url = self::getBaseUrl() . '?' . http_build_query(array(
             'id' => $id
         ));

         // package up the data, don't bother running query on invalid data
         $data = self::_createHash($domainListData);
         if ($data == null) {
             return null;
         }

         // query app nexus server
         $response = self::makeRequest($url, AppNexus_Api::PUT, $data);

         return new AppNexus_Object($response, AppNexus_Object::MODE_READ_WRITE);
     }

     //-------------------------------------------------------------------------
     // internal methods
     //-------------------------------------------------------------------------

     /**
      * Returns a hash containing only the fields which are allowed to be
      *   updated in the format accepted by AppNexus.
      *
      * @param  hash $data
      * @return hash $data
      */
     private static function _createHash($data)
     {
         $pruned = new stdClass();
         foreach (self::$fields as $key) {
             if (property_exists($data, $key)) {
                 $pruned->$key = $data->$key;
             }
         }

         // return null if no valid fields found
         return empty($pruned) ? null : (object) array('domain-list' => $pruned);
     }

}
