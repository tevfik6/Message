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
class MessageException extends \FuelException {}

/**
* Message
* 
* @package Message
*/
class Message
{
	/**
	 * get the message
	 * 
	 * @param  integer 				$message_id 	message id
	 * @return Model_Message|null 	$message 		
	 */
	public static function get($message_id = -1)
	{
		$message = Model_Message::find_by_id($message_id);
		return $message;
	}
	
	/**
	 * get all the messages in the conversation
	 *
	 *	Some sample usage of conversation
	 *	
	 *	conversation( $conversation_id, array(
	 *		'order_by' => array('id' => 'asc')
	 *	));
	 *
	 * 
	 * @param  integer $conversation_id conversation id
	 * @param  Array   $options         options
	 * @return Array                   	Array of Model_Message 
	 */
	public static function conversation ( $conversation_id = -1, array $options = array() ) 
	{
		if( $conversation_id == -1 )
		{
			throw new MessageException("Undefined conversation id!", 0);
		}
		else if ( is_array($conversation_id) )
		{
			throw new MessageException("Conversation ID must be defined as a paramater!", 0);
		}

		$where = array();
		$where[] = array('conversation_id', '=', $conversation_id);

		//check if $options has where  then marge it with conversation id condition.
		array_key_exists('where', $options) and $where = array_merge($options['where'], $where);
		$options['where'] = $where;

		return Model_Message::query($options)->get();
	}

	/**
	 * gives the conversation id between two user
	 * 
	 * @param  integer $user  			user id
	 * @param  integer $user2 			user id
	 * @return integer $conversation_id conversation id
	 */
	public static function conversation_id($user, $user2)
	{
		$conversation_id = Model_Message::query()
			->where_open()
				->where("from_user_id", $user)
				->where("to_user_id", $user2)
			->where_close()
			->or_where_open()
				->where("to_user_id", $user)
				->where("from_user_id", $user2)
			->or_where_close()
			->max("conversation_id");
		return $conversation_id;
	}

	/**
	 * create a new conversation id, if there is already a conversation 
	 * between this two user it return their conversation number
	 * 
	 * @param  integer $user  			user id
	 * @param  integer $user2 			user id
	 * @return integer $conversation_id	conversation_id
	 */
	private static function __create_conversation($user, $user2)
	{
		$conversation_id = self::conversation_id($user, $user2);
		if ( ! $conversation_id )
		{
			$conversation_id = Model_Message::query()
				->max("conversation_id") + 1;
		}
		return $conversation_id;
	}


	// FIXME: Documentation for conversation_count($conversation_id) 
	public static function conversation_count($conversation_id)
	{
		$conversation_count = Model_Message::query()
			->where("conversation_id", $conversation_id)
			->count();

		return $conversation_count;
	}


	/**
	 * send messages from a user to another user
	 * 
	 * @param  integer  $from_user_id    user id
	 * @param  integer  $to_user_id      user id
	 * @param  text 	$message_content user message
	 * @return null|bool             	 when the message successfully send, returns true
	 */
	public static function send($from_user_id, $to_user_id, $message_content)
	{
		$message = new Model_Message();

		$message->from_user_id        = $from_user_id;
		$message->to_user_id          = $to_user_id;
		$message->conversation_id     = self::__create_conversation($from_user_id, $to_user_id);
		$message->content             = $message_content;
		$message->send_time           = time();
		$message->is_read             = false;
		$message->is_removed_sender   = false;
		$message->is_removed_receiver = false;

		return $message->save();
	}
	
	/**
	 * returns incoming messages given user_id
	 *
	 * if there is two parameter after user id, parameter $limit and 
	 * $offset switch the orders to use like natural offset, limit order.
	 *
	 * Example conversation;
	 * 
	 * 		inbox($user_id, $limit) 
	 * 			LIMIT 0, $limit
	 *
	 * 		inbox($user_id = -1, $offset, $limit)
	 * 			LIMIT $offset, $limit
	 *
	 * @param  integer $user_id user id
	 * @param  integer $offset  page offset
	 * @param  integer $limit   limit of query number for once
	 * @return Array            returns Message\Model_Message array.
	 * 
	 */
	public static function inbox($user_id = -1, $limit = -1, $offset = 0)
	{	
		$query = 'SELECT *, count(*) as `message_count`, (count(*) - sum(`is_read`)) as `unread_count` FROM ( SELECT * FROM `messages` WHERE `to_user_id` = '.$user_id.' ORDER BY `send_time` DESC ) as `messages` GROUP BY `conversation_id` ORDER BY `send_time` DESC';

		// if user enter only limit after user id
		if ( $limit != -1 && $offset == 0)
		{
			$query .= " LIMIT ".$offset.",".$limit;
		}
		// if user define both parameter $limit and $offset we will use them in order as they use in sql. First offset then limit. So in here we will change the order of it.
		else if ( $limit != -1 && $offset != -1 )
		{
			$query .= " LIMIT ".$limit.",".$offset;
		}

		$messages = \DB::query($query)
			->as_object("Message\\Model_Message")
			->execute();

		return $messages;
	}

	/**
	 * return outgoing message/s given user id
	 * 	 
	 * if there is two parameter after user id, parameter $limit and 
	 * $offset switch the orders to use like natural offset, limit order.
	 *
	 * Example conversation;
	 * 
	 * 		inbox($user_id, $limit) 
	 * 			LIMIT 0, $limit
	 *
	 * 		inbox($user_id = -1, $offset, $limit)
	 * 			LIMIT $offset, $limit
	 * 		
	 * @param  integer $user_id user id
	 * @param  integer $limit   limit of query number for once
	 * @param  integer $offset  offset of the result
	 * @return Array            returns Message\Model_Message array.
	 * 
	 */
	public static function outbox($user_id = -1, $limit = -1, $offset = 0)
	{
		$query = 'SELECT * FROM ( SELECT * FROM `messages` WHERE `from_user_id` = '.$user_id.' ORDER BY `send_time` DESC ) as `messages` GROUP BY `conversation_id` ORDER BY `send_time` DESC';

		// if user enter only limit after user id
		if ( $limit != -1 && $offset == 0)
		{
			$query .= " LIMIT ".$offset.",".$limit;
		}
		// if user define both parameter $limit and $offset we will use them in order as they use in sql. First offset then limit. So in here we will change the order of it.
		else if ( $limit != -1 && $offset != -1 )
		{
			$query .= " LIMIT ".$limit.",".$offset;
		}

		$messages = \DB::query($query)
			->as_object("Message\\Model_Message")
			->execute();

		return $messages;
	}
	

	// FIXME: Documentation for update()
	public static function update($message_id, $content)
	{
		$message = Model_Message::find_by_id($message_id);
		$message->content = $content;
		return $message->save();
	}	


	// TODO: delete conversation
	// TODO: inbox_unread_count
	// TODO: outbox_unread_count
	// TODO: mark_message_deleted
	// TODO: unmark_message_deleted


	// FIXME: Documentation for mark_message_read()
	public static function mark_message_read($message_ids = -1)
	{	
		self::mark_message($message_ids);
	}
	
	// FIXME: Documentation for mark_message_unread()
	public static function mark_message_unread($message_ids = -1)
	{
		self::mark_message($message_ids, false);
	}

	// FIXME: Documentation for mark_message()
	public static function mark_message($message_ids = -1, $read = true)
	{
		return \DB::update('messages')
			->set(array("is_read" => $read))
			->where("id","IN", $message_ids)
			->execute();	
	}


	// FIXME: Documentation for mark_read_conversation()
	public static function mark_read_conversation($user_id, $conversation_id)
	{
		self::mark_conversation($user_id, $conversation_id);
	}

	// FIXME: Documentation for mark_unread_conversation()
	public static function mark_unread_conversation($user_id, $conversation_id)
	{
		self::mark_conversation($user_id, $conversation_id, false);
	}

	// FIXME: Documentation for mark_conversation()
	// TODO: mark_conversation() must contain offset and limit
	public static function mark_conversation($user_id, $conversation_id, $read = true)
	{
		\DB::update('messages')
			->set(array("is_read" => $read))
			->where("conversation_id", $conversation_id)
			->where("from_user_id", "<>" , $user_id)
			->execute();
	}

	// FIXME: Documentation for delete_message()
	public static function delete_message($message_ids = -1)
	{
		return \DB::delete('messages')
			->where("id", $message_ids)
			->execute();
	}

}