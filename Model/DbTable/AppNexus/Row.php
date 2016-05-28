<?php
//-----------------------------------------------------------------------------
// Model/DbTable/AppNexus/Row.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Row.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class AppNexus_Model_DbTable_AppNexus_Row extends Zend_Db_Table_Row
{

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * Check if the time is more than two hours old.
     *
     * @return bool
     */
    public static function isMoreThanTwoHoursOld($time)
    {
        $twoHoursAgo = new DateTime("-2 hours");
        return new DateTime($time) < $twoHoursAgo;
    }

    //-------------------------------------------------------------------------
    // methods
    //-------------------------------------------------------------------------

    /**
     * Save the token and the request time.
     *
     * @param string $token
     */
    public function saveToken($token)
    {
        $this->token   = $token;
        $this->created = date('Y-m-d H:i:s');
        $this->save();
    }

}
