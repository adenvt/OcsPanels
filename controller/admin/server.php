<?php 

namespace Admin;

class Server extends \Home {

	use \Helper\Server;

	protected
		$server;

	function beforeRoute($f3) {
		parent::beforeRoute($f3);
		if ( ! $this->me->isAdmin()) $f3->reroute('/logout');
		$this->server = new \Server;
	}

	function All($f3) {
		$server = $this->server->find();
		$f3->set('servers',$server);
		$f3->set('subcontent','admin/servers.html');
	}

	function Id($f3) {
		$server = $this->loadServer();
		$f3->set('server',$server);
		$f3->set('subcontent','admin/server.html');
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

	function Lock($f3) {
		$server = $this->loadServer();
		$server->active = $f3->get('PARAMS.active');
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

	function Accounts($f3) {
		$server = $this->loadServer();
		$webmin = new \Webmin($server);
		$account = $webmin->find();
		$f3->set('server',$server);
		$f3->set('users',$account);
		$f3->set('subcontent','admin/accounts.html');
	}

	function loadUser($server) {
		$app = \Base::instance();
		$webmin = new \webmin($server);
		if ($app->exists('PARAMS.uid',$uid)) {
			$webmin->load($uid);
			$webmin->reroute('/home/admin/server/'.$server->id.'/account/');
		}
		return $webmin;
	}

	function Edit($f3) {
		$server = $this->loadServer();
		$account = $this->loadUser($server);
		$f3->set('server',$server);
		$f3->set('user',$account);
		$f3->set('subcontent','admin/account.html');
	}

	function Apply($f3) {
		$server = $this->loadServer();
		$account = $this->loadUser($server);
		if ($account->dry()) {
			if ( ! $account->check($f3->get('POST.user'))) {
				$this->flash('User Sudah Terdaftar');
				$f3->reroute($f3->get('URI'));
			}
			$account->real = $this->me->username;
		}
		$account->copyFrom('POST');
		if ($f3->exists('POST.pass',$pass)) {
			if ( ! \Check::Confirm('POST.pass')) {
				$this->flash('Konfirmasi Password Tidak Cocok');
				$f3->reroute($f3->get('URI'));
			}
			$account->pass = $account->crypt($pass);
		}
		if ($f3->exists('POST.exp',$exp))
			$account->expire = \Webmin::exp_encode($exp);
		$account->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/server/'.$server->id.'/account/'.$account->uid);
	}

	function Block($f3) {
		$server = $this->loadServer();
		$account = $this->loadUser($server);
		if ($f3->exists('PARAMS.active',$active)) {
			if ($active)
				$account->pass = ltrim($account->pass,'!');
			else
				$account->pass = '!'.$account->pass;
		}
		$account->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/server/'.$server->id.'/account/'.$account->uid);
	}

	function Remove($f3) {
		$server = $this->loadServer();
		$account = $this->loadUser($server);
		$account->erase();
		$this->flash('Account Berhasil Dihapus','success');
		$f3->reroute('/home/admin/server/'.$server->id.'/account');
	}
// "create\:$svr['user']\:$svr['pass']\:$svr['uid']\:$svr['gid']\:$svr['real']\:$svr['home']\:$svr['shell']\:$svr['min']\:$svr['max']\:$svr['warn']\:$svr['inactive']\:$svr['expire']";
}