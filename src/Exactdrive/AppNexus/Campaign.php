<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// Campaign.php
//-----------------------------------------------------------------------------

/**
 * AppNexus Campaign.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class Campaign
{
    //-------------------------------------------------------------------------
    // object
    //-------------------------------------------------------------------------

    public function __construct($campaignRow)
    {
        $this->row = $campaignRow;
        $this->_appNexusAdvertiserId = null;
    }

    //-------------------------------------------------------------------------

    public function __destruct()
    {
    }

    //-------------------------------------------------------------------------
    // properties
    //-------------------------------------------------------------------------

    /**
     * Retrives the AppNexus advertiser id associated with this campaign.
     *
     * @return int
     */
    public function appNexusAdvertiserId()
    {
        if ($this->_appNexusAdvertiserId === null) {
            $this->_appNexusAdvertiserId =
                $this->row->fetchAdvertiser()->fetchUser()->appNexusAdvertiserID;
        }

        return $this->_appNexusAdvertiserId;
    }

    //-------------------------------------------------------------------------

    /**
     * Retrieves the conversion pixel generated for this campaign.
     *
     * @return hash
     */
    public function conversionPixel()
    {
        // TODO: Re-implement without dependency from the old campaign model
        // $pixelTable = new Campaigns_Model_DbTable_Pixels();
        // $pixels = $pixelTable->fetchAllConversionPixels($this->row->id);
        // if (count($pixels) == 0) {
        //     return;
        // } else {
        //     return new Pixel($pixels->current());
        // }
    }

    //-------------------------------------------------------------------------

    /**
     * Retrieves the retargetting pixels generated for this campaign.
     *
     * @param bool $deleted => retrieve deleted pixels
     *
     * @return hash
     */
    public function retargetingPixels($deleted = false)
    {
        // TODO: Re-implement without dependency from the old campaign model
        // $pixelTable = new Campaigns_Model_DbTable_Pixels();
        // $pixels = $pixelTable->fetchAllRetargetingPixels(
        //     $this->row->id, $deleted);
        // if (count($pixels) == 0) {
        //     return;
        // } else {
        //     return array_map(function ($pixel) {
        //         return new Segment($pixel);
        //     }, iterator_to_array($pixels));
        // }
    }

    //-------------------------------------------------------------------------
    // conversion pixel methods
    //-------------------------------------------------------------------------

    /**
     * Checks if this campaign has a conversion pixel.
     *
     * @return bool
     */
    public function hasConversionPixel()
    {
        return $this->conversionPixel() != null;
    }

    //-------------------------------------------------------------------------

    /**
     * Add conversion pixel to AppNexus.
     *
     * @return Pixel
     */
    public function addConversionPixelToAppNexus()
    {
        $pixel = Pixel::create($this->row->id, $this->row->toArray());
        $pixel->sync();

        return $pixel;
    }

    //-------------------------------------------------------------------------

    /**
     * Update conversion pixel in AppNexus.
     *
     * @return Pixel
     */
    public function syncConversionPixelToAppNexus()
    {
        // update and sync pixel
        $pixel = $this->conversionPixel();
        $pixel->update($this->row->toArray());
        $pixel->sync();
        $pixel->clearSyncFlag();

        /* // clear log entries
        if ($pixel->row->flags == 0x0) {
            $pixel->row->clearDirtyCampaignLogs();
        } */

        return $pixel;
    }

    //-------------------------------------------------------------------------

    /**
     * Get conversion pixel javascript code.
     *
     * @return string
     */
    public function generateConversionPixelTag()
    {
        $pixel = $this->conversionPixel();

        return $pixel->generateTag();
    }

    //-------------------------------------------------------------------------
    // retargeting pixel methods
    //-------------------------------------------------------------------------

    /**
     * Checks if this campaign has any retargeting pixels.
     *
     * @return bool
     */
    public function hasRetargetingPixels($deleted = false)
    {
        return $this->retargetingPixels($deleted) != null;
    }
}
