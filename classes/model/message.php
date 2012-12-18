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
class Model_Message extends \Orm\Model 
{
	protected static $_table_name = 'messages';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
		'id', 
		'from_user_id', 
		'to_user_id', 
		'conversation_id', 
		'content', 
		'send_time', 
		'is_read', 
		'is_removed_sender',
		'is_removed_receiver'
	);

	protected static $_has_one = array(
		'user' => array(
			'key_from' => 'from_user_id',
			'model_to' => 'Message\\Model_User',
			'key_to' => 'id'
		),
		'from_user' => array(
			'key_from' => 'from_user_id',
			'model_to' => 'Message\\Model_User',
			'key_to' => 'id'
		),
		'to_user' => array(
			'key_from' => 'to_user_id',
			'model_to' => 'Message\\Model_User',
			'key_to' => 'id'
		)
	);
}