<?php
	require_once('util.php');
	require_once('RedisDAO.class.php');

	/*
	 * limit request rate, prevent spam
	 * normally used in fields such as login, register
	 * based on fatigue degree
	 * 
	 * fatigue degree grow algorithm rule, call increase($degree)
	 * rule format 
	 * array(
	 *    'degree'=>100, //min point to reach this punishment
	 *    'interval'=>300 //count interval, degree will expire after interval
	 *  )
	 * 
	 *  rules are stored in redis db
	 *  will auto grow by fatigue degree, call setAutoIncrease($bool)
	 *  punish directlly, call punish($arr)
	 */
	class RateLimiter
	{
		private static $keyPrefix = 'rl:';
		private static $id = '';
		private static $rules = array();


		/*
		 * @param $key customize your own key, default is ip2long(ip)
		 * TODO: validate $rules
		 */
		public static function configure($config)
		{
			self::$keyPrefix = $config->get('key_prefix', self::$keyPrefix);
			self::$id = $config->get('id', cr_get_client_ip());
			self::$rules = $config->get('rules', self::$rules);
		}


		/*
		 * @param
		 *
		 */
		public static function increase($degree)
		{
			if(!is_numeric($degree))
			{
				return false;
			}
			$lua_script = <<<LUA
				local degree = redis.call('incrby', KEYS[1], ARGV[1])
				if degree == tonumber(ARGV[1]) then
					redis.call('expire', KEYS[1], ARGV[2])
				end
				return degree
LUA;
			$redis = RedisDAO::instance();
			if($redis===null)
			{
				return false;
			}
			foreach(self::$rules as $rule){
				$interval = $rule['interval'];
				$key = self::$keyPrefix.'degree:'.self::$id.'-'.$interval;
				$redis->eval($lua_script, 1, $key, $degree, $interval);
			}
			$redis->disconnect();
			$rule = self::whichRuleToPunish();
			if($rule !== null)
			{
				self::punish($rule);
			}
			return true;
		}


		/**/
		public static function punish($rule)
		{
			$redis = RedisDAO::instance();
			if($redis === null)
			{
				return false;
			}
			$lua_script = <<<LUA
				local degree = redis.call('get', KEYS[1])
				if(tonumber(degree) == tonumber(ARGV[1])) then
					return 0
				else
					redis.call('set', KEYS[1], ARGV[1])
					redis.call('expire', KEYS[1], ARGV[2])
				end
				return 1
LUA;
			$count = $redis->eval($lua_script, 1, self::$keyPrefix.'punishing:'.self::$id, $rule['degree'], $rule['interval']);
			$redis->disconnect();
			return $count === 1;
		}


		/*
		 * get punish time left, negative means not being punished
		 */
		public static function getFreezeTime()
		{
			$redis = RedisDAO::instance();
			if($redis === null)
			{
				return 0;
			}
			$freezeTime = (int)$redis->ttl(self::$keyPrefix.'punishing:'.self::$id);
			$redis->disconnect();
			return $freezeTime;
		}

		/*
		 * get which rule to punish current user
		 * mostly of the time, you dont have to call this, as it is called automatically
		 */
		private static function whichRuleToPunish()
		{
			$redis = RedisDAO::instance();
			if($redis === null)
			{
				return null;
			}
			foreach(self::$rules as $rule)
			{
				$interval = $rule['interval'];
				$key = self::$keyPrefix.'degree:'.self::$id.'-'.$interval;
				$degree = (int)$redis->get($key);
				if($degree > $rule['degree'])
				{
					$redis->disconnect();
					return $rule;
				}
			}
			$redis->disconnect();
		}

	}