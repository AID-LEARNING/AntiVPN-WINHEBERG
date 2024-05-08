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

namespace AntiVPN\utils;

use AntiVPN\Main;

use AntiVPN\Event\PlayerBlockedEvent;

use pocketmine\Server;

use pocketmine\player\Player;

final class AntiVPNAPI 
{
	
	public static function getDefaultProcess() : callable 
	{
		return function (Player $player, bool $isSafe) : void
		{
			$ip = $player->getNetworkSession()->getIp();
			if (Main::getInstance()->isCacheEnabled())
				Main::getInstance()->addCachedValue($ip, $isSafe);
			if (!$isSafe)
			{

				$event = new PlayerBlockedEvent($player);
				$event->call();
				$username = $player->getName();
				$player->kick('IP proxy/vpn detected', null, Main::getInstance()->getKickScreenMessage($username));
				$message = Main::getInstance()->getAdminAlertMessage($username);
				foreach (Server::getInstance()->getOnlinePlayers() as $all)
				{
					if ($all->hasPermission('antivpn.alert.receive'))
						$all->sendMessage($message);
				}
			}
		};
	}
}