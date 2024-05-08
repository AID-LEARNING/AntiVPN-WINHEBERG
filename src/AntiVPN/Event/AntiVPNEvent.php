<?php

declare (strict_types = 1);

/*
  
  Rajador Developer

  ▒█▀▀█ ░█▀▀█ ░░░▒█ ░█▀▀█ ▒█▀▀▄ ▒█▀▀▀█ ▒█▀▀█ 
  ▒█▄▄▀ ▒█▄▄█ ░▄░▒█ ▒█▄▄█ ▒█░▒█ ▒█░░▒█ ▒█▄▄▀ 
  ▒█░▒█ ▒█░▒█ ▒█▄▄█ ▒█░▒█ ▒█▄▄▀ ▒█▄▄▄█ ▒█░▒█

  GitHub: https://github.com/RajadorDev

  Discord: rajadortv


*/

namespace AntiVPN\Event;

use AntiVPN\Main;

use pocketmine\player\Player;

use pocketmine\event\player\PlayerEvent;

abstract class AntiVPNEvent extends PlayerEvent 
{
	
	/**
	 * @param Player $player
 	**/
	public function __construct(Player $player) 
	{
		$this->player = $player;
	}
	
	public function getManager() : Main
	{
		return Main::getInstance();
	}
	
	public function getIp() : String 
	{
		return $this->getPlayer()->getNetworkSession()->getIp();
	}
	
}
