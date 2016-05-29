<?php
//-----------------------------------------------------------------------------
// Model/DbTable/AppNexus.php
//-----------------------------------------------------------------------------

/**
 * AppNexus model holding token and expiry information.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class Model_DbTable_AppNexus extends Zend_Db_Table_Abstract
{
    //-------------------------------------------------------------------------
    // fields
    //-------------------------------------------------------------------------

    /**
     * @var string
     */
    protected $_name = 'appNexus';

    /**
     * @var string
     */
    protected $_rowClass = 'Model_DbTable_Row';

    /**
     * Primary Key column(s).
     *
     * @var string
     */
    protected $_primary = array('id');

    /**
     * Is PK column auto-incrementing.
     *
     * @var string
     */
    protected $_sequence = true;
}
