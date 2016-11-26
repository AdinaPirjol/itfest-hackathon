<?php

namespace Project\AppBundle\Services;

class UtilService
{
    const ID = 'app.default';

    public static function isNullObject($object)
    {
        if (is_null($object)) {
            return true;
        }

        return false;
    }
}