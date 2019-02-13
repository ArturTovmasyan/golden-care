<?php

namespace App\Model;


class Grant
{
    public static $IDENTITY_ALL = 0;
    public static $IDENTITY_SEVERAL = 1;
    public static $IDENTITY_OWN = 2;

    public static $LEVEL_NONE = 0;
    public static $LEVEL_VIEW = 1;
    public static $LEVEL_EDIT = 2;
    public static $LEVEL_CREATE = 3;
    public static $LEVEL_DELETE = 4;
    public static $LEVEL_UNDELETE = 5;

}