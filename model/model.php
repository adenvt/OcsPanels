<?php

class Model extends DB\SQL\Mapper {

    function __construct($table) {
        $db  = Base::instance()->get('DATABASE');
        parent::__construct($db,$table);
    }

    function id($id) {
    	$this->load(array('id=?',$id));
    	return $this;
    }

    function reroute($url) {
    	if ($this->dry()) Base::instance()->reroute($url);
    	return $this;
    }
}