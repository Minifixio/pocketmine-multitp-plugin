<?php

namespace Minifixio\multitp\utils;

use pocketmine\Server;

/**
 * Utility methods for my plugin
 */
class PluginUtils{
	
	/**
	 * Log on the server console
	 */
	public static function logOnConsole($message){
		$logger = Server::getInstance()->getLogger();
		$logger->info("[MultiTP] " . $message);
	}
}



