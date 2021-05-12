<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/



abstract class Singleton
{
    protected static $_instances = array();


    protected function __construct()
    {
    }

    final public static function instance()
    {
        $class = get_called_class();

	if (!isset($_instances[$class]))
        {
            $_instances[$class] = new $class();
	    $_instances[$class]->init();
        }
        return $_instances[$class];
    }

    final private function __clone() {
    }

    protected function init() {

    }
}



