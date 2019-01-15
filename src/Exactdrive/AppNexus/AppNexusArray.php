<?php

namespace Exactdrive\AppNexus;

//-----------------------------------------------------------------------------
// AppNexusArray.php
//-----------------------------------------------------------------------------

/**
 * AppNexus API array class.  Generic wrapper object around AppNexus lists
 *  tracking paging of results.
 *
 * @package AppNexus
 * @author Moiz Merchant <moiz@exactdrive.com>
 * @version $Id$
 */
class AppNexusArray implements \ArrayAccess, \IteratorAggregate, \Countable
{

    //-------------------------------------------------------------------------
    // fields
    //-------------------------------------------------------------------------

    /**
     * Raw AppNexus data.
     *
     * @var array
     */
    private $_array;

    /**
     * Count of total number of elements available in the query.
     *
     * @var int
     */
    private $_count;

    /**
     * Start index of results.
     *
     * @var int
     */
    private $_start;

    /**
     * Number of elements requested by query.
     *
     * @var int
     */
    private $_step;

    //-------------------------------------------------------------------------
    // object methods
    //-------------------------------------------------------------------------

    public function __construct($response, $mode)
    {
        // grab the key from the debug info
        $key = $response['dbg_info']['output_term'];

        // set the raw data
        $this->_array = array();
        foreach ($response[$key] as $element) {
            array_push($this->_array, new AppNexusObject($element, $mode));
        }

        // update management variables
        $this->_count = $response['count'];
        $this->_start = $response['start_element'];
        $this->_step  = $response['num_elements'];
    }

    //-------------------------------------------------------------------------
    // ArrayAccess methods
    //-------------------------------------------------------------------------

    public function offsetSet($offset, $value)
    {
        // data in this array cannot be modified
    }

    //-------------------------------------------------------------------------

    /**
     *  Whether a offset exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->_array[$offset]);
    }

    //-------------------------------------------------------------------------

    /**
     * Offset to unset.
     */
    public function offsetUnset($offset)
    {
        // data in this array cannot be modified
    }

    //-------------------------------------------------------------------------

    /**
     * Offset to retrieve.
     */
    public function offsetGet($offset)
    {
        return isset($this->_array[$offset]) ? $this->_array[$offset] : null;
    }

    //-------------------------------------------------------------------------
    // IteratorAggregate methods
    //-------------------------------------------------------------------------

    /**
     * Retrieve an external iterator.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_array);
    }

    //-------------------------------------------------------------------------
    // Countable methods
    //-------------------------------------------------------------------------

    /**
     * Count elements of an the array.
     */
    public function count()
    {
        return count($this->_array);
    }

    //-------------------------------------------------------------------------
    // methods
    //-------------------------------------------------------------------------

    /**
     * Returns the object as an array.
     */
    public function toArray()
    {
        return $this->_array;
    }

    //-------------------------------------------------------------------------

    /**
     * Check weather there are more results available for the query.
     */
    public function areMoreResults()
    {
        return ($this->_start + count($this)) < $this->_count;
    }

    //-------------------------------------------------------------------------

    /**
     * Retrieve the start index of the next batch of results.  If no more
     *  results are available, null is returned..
     */
    public function nextIndex()
    {
        if ($this->areMoreResults()) {
            return $this->_start + count($this);
        }

        return null;
    }

}
