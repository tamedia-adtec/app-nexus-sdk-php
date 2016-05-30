<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// AppNexusObject.php
//-----------------------------------------------------------------------------

/**
 * AppNexus API object class.  Generic wrapper object around AppNexus hashes
 *  enforcing reading/writing of keys.  Keys are converted into properties.
 *
 * @author Moiz Merchant <moiz@exactdrive.com>
 *
 * @version $Id$
 */
class AppNexusObject
{
    //-------------------------------------------------------------------------
    // constants
    //-------------------------------------------------------------------------

    /**
     * Read/Write modes, READ_ONLY does not allow values on object to be
     *  modified.  READ_WRITE allows only existing keys to be modified and
     *  tracks dirtiness of the object.
     */
    const MODE_READ_ONLY = 0;
    const MODE_READ_WRITE = 1;

    //-------------------------------------------------------------------------
    // fields
    //-------------------------------------------------------------------------

    /**
     * Read/Write mode.
     *
     * @var enum
     */
    private $_mode;

    /**
     * Raw AppNexus data.
     *
     * @var array
     */
    private $_raw;

    /**
     * Track when data has been updated.
     *
     * @var bool
     */
    private $_dirty = false;

    //-------------------------------------------------------------------------
    // object methods
    //-------------------------------------------------------------------------

    public function __construct($data, $mode)
    {
        // set the mode and dirty bit
        $this->_mode = $mode;

        // parse out data from response
        if (isset($data['status'])) {

            // grab the key from the debug info
            $key = $this->_extractKey($data['dbg_info']);

            // set the raw data
            $this->_raw = $data[$key];

        // assume raw AppNexus object data was passed in
        } else {
            $this->_raw = $data;
        }
    }

    //-------------------------------------------------------------------------

    /**
     * Run when writing data to inaccessible properties.
     */
    public function __set($name, $value)
    {
        // only allow updating data if mode allows it
        if ($this->_mode == self::MODE_READ_WRITE) {
            if (array_key_exists($name, $this->_raw)) {

                // only update the key if new and mark dirty
                if ($this->_raw[$name] != $value) {
                    $this->_raw[$name] = $value;
                    $this->_dirty = true;
                }
            }
        }
    }

    //-------------------------------------------------------------------------

    /**
     * Utilized for reading data from inaccessible properties.
     */
    public function __get($name)
    {
        // only return values for keys in raw data
        if (array_key_exists($name, $this->_raw)) {
            return $this->_raw[$name];
        }

        return;
    }

    //-------------------------------------------------------------------------

    /**
     * Triggered by calling isset() or empty() on inaccessible properties.
     */
    public function __isset($name)
    {
        return isset($this->_raw[$name]);
    }

    //-------------------------------------------------------------------------

    /**
     * Invoked when unset() is used on inaccessible properties.
     */
    public function __unset($name)
    {
        // don't allow unsetting of data, the keys/values in the raw data
        //  cannot be modified.
    }

    //-------------------------------------------------------------------------
    // methods
    //-------------------------------------------------------------------------

    /**
     * Returns the object as an array.
     */
    public function toArray()
    {
        return $this->_raw;
    }

    //-------------------------------------------------------------------------

    /**
     * Returns the object as a json string.
     */
    public function toJson()
    {
        return json_encode($this->_raw);
    }

    //-------------------------------------------------------------------------

    /**
     * Returns list of keys/properties which are available.
     *
     * @param $search_value => only keys containing these values are returned.
     */
    public function keys($search_value = null)
    {
        if (func_num_args() == 0) {
            return array_keys($this->_raw);
        } else {
            return array_keys($this->_raw, $search_value);
        }
    }

    //-------------------------------------------------------------------------

    /**
     * Check if data is dirty.
     */
    public function isDirty()
    {
        return $this->_dirty;
    }

    //-------------------------------------------------------------------------
    // internal methods
    //-------------------------------------------------------------------------

    /**
     * Extracts the object key from the debug information.
     */
    protected function _extractKey($data)
    {
        // extract key from parent debug info if specified
        if (array_key_exists('parent_dbg_info', $data)) {
            return $this->_extractKey($data['parent_dbg_info']);
        } else {
            return $data['output_term'];
        }
    }
}
