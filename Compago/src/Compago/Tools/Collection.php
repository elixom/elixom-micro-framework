<?php
namespace Compago\Tools;
use Compago\Contracts\Arrayable;
use ArrayIterator;

class Collection implements Arrayable, \ArrayAccess, \IteratorAggregate
{
    /**
     * The source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create new collection
     *
     * @param array $items Pre-populate collection with this key-value array
     */
    public function __construct(array $items = [])
    {
        $this->replace($items);
    }

    /********************************************************************************
     * Collection interface
     *******************************************************************************/

    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get collection item for key
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Add item to collection, replacing existing items with the same data key
     *
     * @param array $items Key-value array of data to append to this collection
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get all items in collection
     *
     * @return array The collection's source data
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return array The collection's source data keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Remove all items from collection
     */
    public function clear()
    {
        $this->data = [];
    }

    /********************************************************************************
     * ArrayAccess interface
     *******************************************************************************/

    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /********************************************************************************
     * IteratorAggregate interface
     *******************************************************************************/

    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
    public function first(/*Array $limit=array(), $onFailureReturnDefault =false*/)
        {
            $n = func_num_args();
            if ($n){
                $limit = func_get_arg(0);
                if ($limit===null) $limit = array();
                if (!is_array($limit)) $limit = (array)$limit;
            } else {
                $limit = array();
            }
            foreach ($limit as $k=>$value){
                if($value === null){
                    unset($limit[$k]);
                }
            }
            if (count($limit)){
                foreach ($this->data as $item){
                    foreach ($limit as $k=>$value){
                        if (is_array($value)){
                            if (!in_array($item->$k,$value)){
                                continue 2;
                            }
                        } elseif ($item->$k != $value){
                            continue 2;
                        }
                    }
                    return $item;
                }
            } elseif (count($this->data)){
                if (key($this->data)===null)reset($this->data);
                return current($this->data);
            }
            
            if ($n==2){
                if (func_get_arg(1)==true) return new PropertyBag;
                return func_get_arg(1);
            }
            return false;
        }
}
