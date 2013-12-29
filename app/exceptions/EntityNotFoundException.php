<?php
/**
 * Author: Nik Torfs
 */
class EntityNotFoundException extends Exception
{
    function __construct($type)
    {
        parent::__construct("", 404);
        $this->type = $type;
    }

    function getType(){
        return $this->type;
    }
}
