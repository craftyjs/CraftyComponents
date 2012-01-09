<?php
namespace FWM\ServicesBundle\Services;

class ArrayService
{

    public static function arraySort($array, $on, $order = SORT_DESC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0)
        {
            foreach ($array as $k => $v)
            {
                if (is_array($v))
                {
                    foreach ($v as $k2 => $v2)
                    {
                        if ($k2 == $on)
                        {
                            $sortable_array[$k] = $v2;
                        }
                    }
                }
                else
                {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order)
            {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v)
            {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }
    
	public static function maxKey($array) {
		foreach ($array as $key => $val) {
    		if ($val == max($array)) return $key;
    	}
	}
	
    public static function objectToArray( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
            return $object;

        if( is_object( $object ) )
            $object = get_object_vars( $object );

        return array_map('\FWM\ServicesBundle\Services\ArrayService::objectToArray', $object );
    }
}