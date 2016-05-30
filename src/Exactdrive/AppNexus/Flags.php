<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// Flags.php
//-----------------------------------------------------------------------------

/**
 * Flags class encapsulating bitwise operations.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class Flags
{
    //-------------------------------------------------------------------------
    // static methods
    //-------------------------------------------------------------------------

    /**
     * Checks weather the desired bit is set in flags.
     *
     * @param int flags
     * @param int flag
     */
    public static function isFlagSet($flags, $flag)
    {
        return ($flags & $flag) == $flag;
    }

    //-------------------------------------------------------------------------

    /**
     * Sets the desired bit in flags.
     *
     * @param int flags
     * @param int flag
     */
    public static function setFlag(&$flags, $flag)
    {
        $flags |= $flag;
    }

    //-------------------------------------------------------------------------

    /**
     * Unsets the desired bit in flags.
     *
     * @param int flags
     * @param int flag
     */
    public static function unsetFlag(&$flags, $flag)
    {
        $flags &= ~$flag;
    }

    //-------------------------------------------------------------------------

    /**
     * Checks if any of the flags are only present in new.
     *
     * @param int   old
     * @param int   new
     * @param array flags
     */
    public static function wasFlagSet($old, $new, $flags)
    {
        $old = $old == 0 ? ~$old - 1 : ~$old;
        foreach ($flags as $flag) {
            if (($old & $new & $flag) == $flag) {
                return true;
            }
        }

        return false;
    }
}
