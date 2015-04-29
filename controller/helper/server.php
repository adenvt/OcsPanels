<?php

namespace Helper;

trait Server {

	function loadServer() {
		$f3 = \Base::instance();
		$server = $this->server;
		if ($f3->exists('PARAMS.id',$id)) {
			if ($this->me->isAdmin()) {
				$server->id($id);
				$server->reroute('/home/admin/server');
			} else {
				$server->load(array('id=? AND active=1',$id));
				$server->reroute('/home/member/server');
			}
		}
		return $server;
	}

}