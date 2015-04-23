<?php 

namespace Member;

class Server extends \Home {

	use \Helper\Server;

	protected
		$server;

	function beforeRoute($f3) {
		parent::beforeRoute($f3);
		if ( $this->me->isAdmin()) $f3->reroute('/home/admin/server/');
		$this->server = new \Server;
	}

	function All($f3) {
		$server = $this->server->find(array('active=1'));
		$f3->set('servers',$server);
		$f3->set('subcontent','member/servers.html');
	}

	function Id($f3) {
		$server = $this->loadServer();
		$f3->set('server',$server);
		$f3->set('subcontent','member/server.html');
	}

	function Set($f3) {
		$server = $this->loadServer();
		$server->copyFrom('POST');
		$pass = $f3->get('POST.root_pass');
		if ( ! empty($pass))
			$server->setPass($pass);
		$server->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/server/'.$server->id);
	}

	function Lock($f3,$data) {
		$server = $this->loadServer();
		$server->active = $data['active'];
		$server->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/server/'.$server->id);

	}

	function Delete($f3) {
		$server = $this->loadServer();
		$server->erase();
		$this->flash('Server Berhasil Dihapus','success');
		$f3->reroute('/home/admin/server/');
	}

}