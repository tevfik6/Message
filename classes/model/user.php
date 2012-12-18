<?php
/**
 * Message is a FuelPHP package.
 *
 * @package    Message - Fuel
 * @version    1.0
 * @author     Tevfik TÜMER
 * @license    MIT License
 * @copyright  2012 Fuel Development Team
 * @link       http://tevfik.me - http://fuelphp.com
 */
namespace Message;
class Model_User extends \Orm\Model
{
	protected static $_table_name = 'users';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
		'id', 
		'username', 
		'password', 
		'group', 
		'email', 
		'last_login', 
		'login_hash', 
		'profile_fields',
		'created_at'
	);

	protected static $_has_many = array(
		'message' => array(
			'key_from' => 'id',
			'model_to' => 'Message\\Model_Message',
			'key_to' => 'from_user_id'
		)
	);
}