<?php

require_once('util4p/util.php');
require_once('util4p/CRObject.class.php');
require_once('util4p/SQLBuilder.class.php');
require_once('util4p/MysqlPDO.class.php');
require_once('util4p/Validator.class.php');

class UserManager
{

	/**/
	public static function add(CRObject $user)
	{
		$username = $user->get('username');
		$email = $user->get('email');
		$password = $user->get('password');
		$role = $user->get('role');
		if (!Validator::isEmail($email)) {
			return false;
		}

		$key_values = array(
			'username' => '?', 'email' => '?', 'password' => '?', 'role' => '?', 'reg_time' => '?', 'reg_ip' => '?'
		);
		$builder = new SQLBuilder();
		$builder->insert('qa_user', $key_values);
		$sql = $builder->build();

		$params = array(
			$username, $email, $password, $role, time(), ip2long(cr_get_client_ip(false))
		);
		return (new MysqlPDO())->execute($sql, $params);
	}

	/**/
	public static function update(CRObject $user)
	{
		$username = $user->get('username');
		$email = $user->get('email');
		$email_verified = $user->getInt('email_verified');
		$password = $user->get('password');
		$role = $user->get('role');
		if (!Validator::isEmail($email)) {
			return false;
		}

		$key_values = array(
			'email' => '?', 'email_verified' => '?', 'password' => '?', 'role' => '?'
		);
		$where_arr = array('username' => '?');
		$builder = new SQLBuilder();
		$builder->update('qa_user', $key_values);
		$builder->where($where_arr);
		$sql = $builder->build();
		$params = array($email, $email_verified, $password, $role, $username);
		return (new MysqlPDO())->execute($sql, $params);
	}

	/**/
	public static function getByUsername($username)
	{
		$selected_rows = array();
		$where_arr = array('username' => '?');
		$builder = new SQLBuilder();
		$builder->select('qa_user', $selected_rows);
		$builder->where($where_arr);
		$sql = $builder->build();
		$params = array($username);
		$users = (new MysqlPDO())->executeQuery($sql, $params);
		return $users !== null && count($users) === 1 ? $users[0] : null;
	}

	/**/
	public static function getByEmail($email)
	{
		$selected_rows = array();
		$where_arr = array('email' => '?');
		$builder = new SQLBuilder();
		$builder->select('qa_user', $selected_rows);
		$builder->where($where_arr);
		$sql = $builder->build();
		$params = array($email);
		$users = (new MysqlPDO())->executeQuery($sql, $params);
		return $users !== null && count($users) === 1 ? $users[0] : null;
	}

	/**/
	public static function gets(CRObject $rule)
	{
		$offset = $rule->getInt('offset', 0);
		$limit = $rule->getInt('limit', -1);
		$selected_rows = array('username', 'email', 'email_verified', 'role', 'reg_time', 'reg_ip');
		$where = array();
		$opts = array();
		// add search
		$search = trim($rule->get('search',''));
		if( strlen($search) > 0 ){
			$where['email'] = '"%'. $search . '%"';			
			$opts['email'] = 'LIKE';
		}
		
		
		$builder = new SQLBuilder();
		$builder->select('qa_user', $selected_rows);
		$builder->where($where,$opts);
		
		$builder->limit($offset, $limit);
		$sql = $builder->build();
		$params = array();
		$users = (new MysqlPDO())->executeQuery($sql, $params);
		return $users;
	}

	/**/
	public static function getCount(CRObject $rule)
	{
		$selected_rows = array('COUNT(1) AS `count`');
		$where_arr = array();
		$builder = new SQLBuilder();
		$builder->select('qa_user', $selected_rows);
		$builder->where($where_arr);
		$sql = $builder->build();
		$params = array();
		$res = (new MysqlPDO())->executeQuery($sql, $params);
		return $res !== null ? intval($res[0]['count']) : 0;
	}
}