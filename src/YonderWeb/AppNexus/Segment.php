<?php

namespace YonderWeb\AppNexus;

//-----------------------------------------------------------------------------
// Segment.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Segment.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class Segment
{
    //-------------------------------------------------------------------------
    // constants
    //-------------------------------------------------------------------------

    const FLAG_DELETED = 0x1;
    const FLAG_EMAIL = 0x2;
    const FLAG_SYNC = 0x4;

    //-------------------------------------------------------------------------
    // static fields
    //-------------------------------------------------------------------------

    /**
     * @var hash
     */
    public static $expiryUnits = array(
        'minute' => 'Minutes',
        'hour' => 'Hours',
        'day' => 'Days',
    );

    //-------------------------------------------------------------------------
    // fields
    //-------------------------------------------------------------------------

    /**
     * @var Campaigns_Model_DbTable_Pixels_Row
     */
    public $row;

    /**
     * @var hash
     */
    protected $_data;

    /**
     * @var object
     */
    protected $_appNexus;

    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * Add a new retargeting pixel to the database.
     *
     * @param int  $campaignId
     * @param hash $data
     *
     * @return Segment
     */
    public static function create($campaignId, $data)
    {
        // save to database
        $pixelTable = new Campaigns_Model_DbTable_Pixels();
        $row = $pixelTable->createRow(array(
            'campaignId' => $campaignId,
            'type' => Campaigns_Model_DbTable_Pixels::TYPE_RETARGETING,
            'name' => $data['name'],
            'data' => json_encode($data),
            'flags' => self::FLAG_SYNC,
        ));
        $row->save();

        return new self($row);
    }

    //-------------------------------------------------------------------------
    // object
    //-------------------------------------------------------------------------

    public function __construct($segmentRow)
    {
        $this->row = $segmentRow;
        $this->_data = null;
        $this->_appNexus = null;
    }

    //-------------------------------------------------------------------------

    public function __destruct()
    {
    }

    //-------------------------------------------------------------------------
    // properties
    //-------------------------------------------------------------------------

    /**
     * Retrives the retargeting pixel data.
     *
     * @return int
     */
    public function getData()
    {
        if ($this->_data == null) {
            $this->_data = json_decode($this->row->data, true);
        }

        return $this->_data;
    }

    //-------------------------------------------------------------------------

    /**
     * Retrives the AppNexus segment data.
     *
     * @return int
     */
    public function getAppNexusData()
    {
        if ($this->_appNexus == null) {
            $data = json_decode($this->row->appNexusData, true);
            if ($data) {
                $this->_appNexus =
                    new AppNexusObject($data, AppNexusObject::MODE_READ_WRITE);
            }
        }

        return $this->_appNexus;
    }

    //-------------------------------------------------------------------------

    /**
     * Retrives the AppNexus segment data.
     *
     * @return int
     */
    public function getCampaign()
    {
        return new Campaign($this->row->fetchCampaign());
    }

    //-------------------------------------------------------------------------
    // methods
    //-------------------------------------------------------------------------

    /**
     * Returns true if pixel is marked as deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return Flags::isFlagSet($this->row->flags, self::FLAG_DELETED);
    }

    //-------------------------------------------------------------------------

    /**
     * Returns true if pixel requires email to user.
     *
     * @return bool
     */
    public function shouldEmail()
    {
        return Flags::isFlagSet($this->row->flags, self::FLAG_EMAIL);
    }

    //-------------------------------------------------------------------------

    /**
     * Returns true if pixel requires sync with AppNexus.
     *
     * @return bool
     */
    public function shouldSync()
    {
        return Flags::isFlagSet($this->row->flags, self::FLAG_SYNC);
    }

    //-------------------------------------------------------------------------

    /**
     * Update retargeting pixel data in database.
     *
     * @param hash $data
     *
     * @return this
     */
    public function update($data)
    {
        $current = $this->getData();
        $flags = 0x0;

        // keys triggering sync
        $syncKeys = array(
            'name',
            'expiryType',
            'expiryAmount',
            'expiryUnit',
        );

        // check if changes require sync
        foreach ($syncKeys as $key) {
            if ($current[$key] != $data[$key]) {
                Flags::setFlag($flags, self::FLAG_SYNC);
                break;
            }
        }

        // save data
        $this->row->name = $data['name'];
        $this->row->data = json_encode($data);
        $this->row->flags |= $flags;
        $this->row->save();

        // clear cache
        $this->_data = null;

        return $this;
    }

    //-------------------------------------------------------------------------

    /**
     * Sync retargeting pixel data to AppNexus.
     */
    public function sync()
    {
        // grab appnexus data
        $advertiserId = $this->row->fetchAppNexusAdvertiserId();
        $syncData = $this->_getSyncData();

        // create new segment
        if ($this->getAppNexusData() == null) {
            $segment = SegmentService::addSegment(
                $syncData, $advertiserId);

            // pixel should be emailed after inital sync
            $this->row->flags |= self::FLAG_EMAIL;

        // update existing segment
        } else {
            $pixelId = $this->getAppNexusData()->id;
            $segment = SegmentService::updateSegment(
                $pixelId, $syncData, $advertiserId);
        }

        // save to database
        $this->row->appNexusData = $segment->toJson();
        $this->row->save();

        // clear cache
        $this->_appNexus = null;
    }

    //-------------------------------------------------------------------------

    /**
     * Update pixels deleted status.
     *
     * @param bool $delete
     *
     * @return this
     */
    public function setDeleted($delete)
    {
        // updating deleted status will require sync to appnexus
        if ($this->isDeleted() != $delete) {
            $flags = $this->row->flags;

            // update flags
            Flags::setFlag($flags, self::FLAG_SYNC);
            if ($delete) {
                Flags::setFlag($flags, self::FLAG_DELETED);
            } else {
                Flags::unsetFlag($flags, self::FLAG_DELETED);
            }

            // save to db
            $this->row->flags = $flags;
            $this->row->save();
        }

        return $this;
    }

    //-------------------------------------------------------------------------

    /**
     * Clear email flag.
     *
     * @return this
     */
    public function clearEmailFlag()
    {
        $this->_clearFlag(self::FLAG_EMAIL);

        return $this;
    }

    //-------------------------------------------------------------------------

    /**
     * Clear sync flag.
     *
     * @return this
     */
    public function clearSyncFlag()
    {
        $this->_clearFlag(self::FLAG_SYNC);

        return $this;
    }

    //-------------------------------------------------------------------------

    /**
     * Get segment code.
     *
     * @return string
     */
    public function generateTag()
    {
        // grab pixel
        $pixel = $this->getData();
        $jsType = $pixel['tagType'] == 'javascript';
        $secure = $pixel['securityType'] == 'secure';

        // generate tag
        return $this->_generateTag($jsType, $secure);
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Get segment code.
     *
     * @param bool $javascript
     * @param bool $secure
     *
     * @return string
     */
    public function _generateTag($javascript, $secure)
    {
        // grab pixel
        $pixel = $this->getAppNexusData();
        if (!$pixel) {
            return '';
        }

        // url depends on security option
        if ($secure) {
            $url = 'https://secure.adnxs.com';
        } else {
            $url = 'http://ads.exactdrive.com';
        }

        // tag depends on type
        if ($javascript) {
            $tag =
                "<script src=\"$url/seg?add={$pixel->id}&t=1\" type=\"text/javascript\"></script>";
        } else {
            $tag =
                "<img src=\"$url/seg?add={$pixel->id}&t=2\" width=\"1\" height=\"1\" />";
        }

        // generate tag
        $html =
            "<!-- Segment Pixel - {$this->row->name} - DO NOT MODIFY -->".PHP_EOL.
            $tag.PHP_EOL.
            '<!-- End of Segment Pixel -->';

        return $html;
    }

    //-------------------------------------------------------------------------

    /**
     * Clear flag from database field..
     */
    protected function _clearFlag($flag)
    {
        $flags = $this->row->flags;
        if (Flags::isFlagSet($flags, $flag)) {
            Flags::unsetFlag($flags, $flag);
            $this->row->flags = $flags;
            $this->row->save();
        }
    }

    //-------------------------------------------------------------------------

    /**
     * Get retargeting pixel data formated in AppNexus format.
     *
     * @return string
     */
    protected function _getSyncData()
    {
        $campaign = $this->getCampaign();
        $data = $this->getData();

        // calculate expiration minutes
        if ($data['expiryType'] == 'no-expire') {
            $minutes = null;
        } else {
            switch ($data['expiryUnit']) {
                case 'minute':
                    $multiplier = 1;
                    break;
                case 'hour':
                    $multiplier = 60;
                    break;
                case 'day':
                    $multiplier = 60 * 24;
                    break;
            }
            $minutes = $data['expiryAmount'] * $multiplier;
        }

        // construct sync hash
        $deleted = $this->isDeleted();
        $status = $deleted ? ' (inactive)' : '';
        $name = "{$campaign->row->name} - {$this->row->name}".$status;
        $syncData = array(
            'code' => "pixel-{$this->row->id}_campaign-{$campaign->row->id}",
            'state' => $deleted ? 'inactive' : 'active',
            'short_name' => $name,
            'expire_minutes' => $minutes,
        );

        return $syncData;
    }
}
