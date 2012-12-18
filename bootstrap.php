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

Autoloader::add_core_namespace('Message');

Autoloader::add_classes(array(
	'Message\\Message'          => __DIR__.'/classes/message.php',
	'Message\\MessageException' => __DIR__.'/classes/message.php',
	
	// Models 
	'Message\\Model_User'       => __DIR__.'/classes/model/user.php',
	'Message\\Model_Message'    => __DIR__.'/classes/model/message.php',
));


/* End of file bootstrap.php */