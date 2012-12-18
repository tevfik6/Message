# Message for FuelPHP Framework

Simple Private Messaging System for FuelPHP

## Configuration

### Create Database
```mysql
--
-- Table structure for table `messages`
--
CREATE TABLE `messages` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`from_user_id` int(11) unsigned NOT NULL,
	`to_user_id` int(11) unsigned NOT NULL,
	`conversation_id` int(11) NOT NULL DEFAULT '0',
	`content` text NOT NULL,
	`send_time` varchar(11) NOT NULL DEFAULT '0',
	`is_read` tinyint(1) NOT NULL DEFAULT '0',
	`is_removed_sender` tinyint(1) NOT NULL DEFAULT '0',
	`is_removed_receiver` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `from_user_id` (`from_user_id`),
	KEY `to_user_id` (`to_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
```
### Put the Message Package Files
Locate the files under `YourApplication\fuel\packages\` folder

### Load the Message Package
Be able to use Message, You must either add it to `always_load` in `app/config/config.php`;
```php
'always_load' => array(
	'packages'  => array(
		'orm',
		'auth',
		'message'
	),
),
```

or use ` Package::load()`
```php
Package::load("Message");
```

## Dependencies 
**FuelPHP 5.3 Framework**
**Packages**: ORM, Auth(SimpleAuth or equvalent)