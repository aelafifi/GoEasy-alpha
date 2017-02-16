<?php

class User {

	static $user;

	static function init() {
		$user = Data::session('logged_user') ?: Data::cookie('logged_user');
		self::$user = MySQL::table('user')->one($user);
		Data::set('logged_user', (object) self::$user);
		return self::$user;
	}

	static function login($id, $save=false) {
		Data::session('logged_user', $id);
		if ( $save ) {
			Data::cookie('logged_user', $id, 90 * Cookie::DAYS);
		}
		return self::init();
	}

	static function logout() {
		Data::cookie('logged_user', null);
		Data::session('logged_user', null);
	}

}

User::init();

?>