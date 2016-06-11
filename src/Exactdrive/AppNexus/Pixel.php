<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// Pixel.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Pixel.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class Pixel
{
    //-------------------------------------------------------------------------
    // constants
    //-------------------------------------------------------------------------

    const FLAG_DELETED = 0x1;
    const FLAG_EMAIL = 0x2;
    const FLAG_SYNC = 0x4;

    const TYPE_HYBRID = 'hybrid';
    const TYPE_VIEW = 'view';
    const TYPE_CLICK = 'click';

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
     * Get the type of conversion pixel.
     *
     * @return string => null is returned if neither type found.
     */
    public static function getPixelType($viewCPA, $clickCPA)
    {
        // grab view/click cpa values
        $hasViewCPA = ($viewCPA != null) && ($viewCPA > 0.0);
        $hasClickCPA = ($clickCPA != null) && ($clickCPA > 0.0);

        // evaluate pixel type
        if ($hasViewCPA && $hasClickCPA) {
            return self::TYPE_HYBRID;
        } elseif ($hasViewCPA) {
            return self::TYPE_VIEW;
        } elseif ($hasClickCPA) {
            return self::TYPE_CLICK;
        } else {
            return;
        }
    }

    //-------------------------------------------------------------------------

    /**
     * Add a new conversion pixel to AppNexus and the database.
     *
     * @param int  $campaignId
     * @param hash $data
     *
     * @return Pixel
     */
    public static function create($campaignId, $data)
    {
        // TODO: Re-implement without dependency from the old campaign model
        // $viewCPA = $data['postViewCPA'];
        // $clickCPA = $data['postClickCPA'];

        // // grab conversion type
        // $type = self::getPixelType($viewCPA, $clickCPA);
        // if ($type == null) {
        //     throw new \Exception("Invalid conversion pixel type: $type.");
        // }

        // // save to database

        // $pixelTable = new Campaigns_Model_DbTable_Pixels();
        // $row = $pixelTable->createRow(array(
        //     'campaignId' => $campaignId,
        //     'type' => Campaigns_Model_DbTable_Pixels::TYPE_CONVERSION,
        //     'name' => $data['name'],
        //     'data' => json_encode(array('type' => $type)),
        //     'flags' => self::FLAG_SYNC,
        // ));
        // $row->save();

        // return new self($row);
    }

    //-------------------------------------------------------------------------
    // object
    //-------------------------------------------------------------------------

    public function __construct($pixelRow)
    {
        $this->row = $pixelRow;
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
     * Retrives the conversion pixel data.
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
     * Retrives the AppNexus pixel data.
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
     * Sync pixel data with AppNexus.
     *
     * @param hash $data
     *
     * @return Pixel
     */
    public function update($data)
    {
        $viewCPA = $data['postViewCPA'];
        $clickCPA = $data['postClickCPA'];

        // grab conversion type
        $type = self::getPixelType($viewCPA, $clickCPA);
        if ($type == null) {
            throw new Exception("Invalid conversion pixel type: $type.");
        }

        // save to database
        $this->row->name = $data['name'];
        $this->row->data = json_encode(array('type' => $type));
        $this->row->save();

        return $this;
    }

    //-------------------------------------------------------------------------

    /**
     * Sync conversion pixel data to AppNexus.
     */
    public function sync()
    {
        // grab appnexus data
        $advertiserId = $this->row->fetchAppNexusAdvertiserId();
        $syncData = $this->_getSyncData();

        // create new segment
        if ($this->getAppNexusData() == null) {
            $pixel = PixelService::addPixel($advertiserId, $syncData);

            // pixel should be emailed after inital sync
            $this->row->flags |= self::FLAG_EMAIL;

        // update existing segment
        } else {
            $pixelId = $this->getAppNexusData()->id;
            $pixel = PixelService::updatePixel(
                $pixelId, $advertiserId, $syncData);
        }

        // save to database
        $this->row->appNexusData = $pixel->toJson();
        $this->row->save();

        // clear cache
        $this->_appNexus = null;
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
     * Get pixel javascript code.
     *
     * @return string
     */
    public function generateTag()
    {
        return $this->_generateTag(true, true);
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Get pixel code.
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
                "<script src=\"$url/px?id={$pixel->id}&t=1\" type=\"text/javascript\"></script>";
        } else {
            $tag =
                "<img src=\"$url/px?id={$pixel->id}&t=2\" width=\"1\" height=\"1\" />";
        }

        // generate tag
        $html =
            "<!-- Conversion Pixel - {$this->row->name} - DO NOT MODIFY -->".PHP_EOL.
            $tag.PHP_EOL.
            '<!-- End of Conversion Pixel -->';

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
        $data = $this->getData();

        // [moiz] looks like the post_view_value and the post_click_value
        //  fields are used as overrides and not implemented in the appnexus
        //  console.  appnexus advised not using this field and instead
        //  setting the values on the line item.

        // construct sync hash
        $syncData = array(
            'code' => "pixels_{$this->row->id}",
            'name' => $this->row->name,
            'trigger_type' => $data['type'],
        );

        return $syncData;
    }
}
