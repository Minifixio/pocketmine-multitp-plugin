<?php
/**
 * This plugin allows to set random positions and when a player touch a sponge block, he is teleport to a random positions.
 *  
 * The default teleporting block is the sponge block ( why ?  for fun !  :). But you can change it at line 108.
 *
 * You can follow me on Twitter ( @Minifixio ) and suscribe to my youtube channel : Minifixio.
 *
 * I do not think that you will read this notes in the code but you can leave a comment in PocketMine for my first plugin !
 */

namespace Minifixio\randomtp;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\event\Listener;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;

use Minifixio\randomtp\utils\PluginUtils;

class RandomTP extends PluginBase implements Listener {
	
	public $positions = array();
	
	public $randomConfig;

	/**
	* @return Position a random position based on current position array
	*/
	public function getRandomLocation(){
		$numPos = mt_rand(1, count($this->positions));
		return $this->positions[$numPos - 1];
	}
	
	/**
	 * Add a new position and save it in the positions.yml
	 * @param Position position to add
	 */
	public function addNewLocation(Position $location){
		array_push($this->positions, $location);
		$this->randomConfig->set(count($this->positions), [$location->getX(), $location->getY(), $location->getZ(), $location->getLevel()->getName()]);
		$this->randomConfig->save();
	}
	
    /**
     * @return boolean
     */
	public function getNumberOfPositions(){
		return count($this->positions);	
	}
	
	/**
	 * Plugin initialization
	 */
	public function onEnable(){
		//Load Plugin
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		PluginUtils::logOnConsole(">> Load RandomTP plugin by Minifixio ...");
		
		//Create data folder (for positions.yml)
		@mkdir($this->getDataFolder());
		
		//Create and load the positions configuration file
		$this->randomConfig = new Config($this->getDataFolder()."positions.yml", Config::YAML, array());
		
		//Load positions
		$this->positions = $this->parsePositions($this->randomConfig->getAll());
	}
	
	/**
     * Create positions based on the positions.yml configuration file
	 */
	public function parsePositions(array $w) {
		$ret = [];
		$index = 0;
		foreach ($w as $n => $data) {
			$this->getServer()->loadLevel($data[3]);
			if(($level = $this->getServer()->getLevelByName($data[3])) === null) $this->getLogger()->error($data[3] . " is not loaded. Position " . $n . " is disabled.");
			else{
				$ret[$index] = new Position($data[0], $data[1], $data[2], $level);
			}
			$index++;
		}
		return $ret;
	}
	
	/**
     * Reset all positions
	 */
	public function resetPositions() {
		$keys = $this->randomConfig->getAll(true);
		foreach ($keys as $key){
			$this->randomConfig->remove($key);
		}
		$this->randomConfig->save();
		unset($this->positions);
		$this->positions = array();
	}
	
	public function playerBlockTouch(PlayerInteractEvent $event){
		
		//Check block's id (= 19) > sponge block
		if($event->getBlock()->getID() == 19){
			if(count($this->positions) == 0){
				
				//If there are no positions set :
				$event->getPlayer()->sendMessage("[RandomTP] Sorry, there is no teleport position yet set");
			}
			else{
				
				//Teleport sender to a random position
				$event->getPlayer()->teleport($this->getRandomLocation());
				$event->getPlayer()->sendMessage("Teleporting...");
			}
		}
	}
	
/**
=+=+=+=+=+=+=+=

>>>>> COMMANDS

=+=+=+=+=+=+=+=
**/
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			
			//rtpset command to set a random spawn position
			case "rtpset":
				$this->addNewLocation($sender->getLocation());
				$sender->sendMessage("[RandomTP] A new teleportation target was added at your position !");
				$sender->sendMessage("[RandomTP] There are : " . $this->getNumberOfPositions() . " teleportation targets.");
				return true;
				
			//rtpreset command to reset all random spawn positions
			case "rtpreset":
				$this->resetPositions();
				$sender->sendMessage("[RandomTP] All random spawn positions have been removed!");
				return true;
				
			//rtpinfo to have all informations about the plugin :)
			case "rtpinfo":
				$sender->sendMessage("[RandomTP] This plugin was created by Minifixio. I've got a Youtube channel, you can subscribe :)");
				$sender->sendMessage("[RandomTP] Commands : /rtpset ( to set a random spawn position )  and  /rtpreset ( to reset all random spawn positions.");
				$sender->sendMessage("[RandomTP] The default teleporting block is the sponge block, but you can change this in the code of the plugin.");
				$sender->sendMessage("[RandomTP] Perhaps there will be an update to modify the block of teleportation.");
                return true;
				$sender->sendMessage("[RandomTP] If you have suggestions or bugs, please report this to me !");
				
			default:
				return false;
		}
	}	
}



