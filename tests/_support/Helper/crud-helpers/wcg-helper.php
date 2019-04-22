<?php

require_once __DIR__ . '/includes/class-dummy.php';

abstract class WCG_Helper {
    /**
     * Stores instance of Dummy
     */
    protected $dummy;

    protected function __construct() {
        $this->dummy = new Dummy();
    }

    public static function instance(){
        return new static();
    }

    public abstract function print_query( $id );
}