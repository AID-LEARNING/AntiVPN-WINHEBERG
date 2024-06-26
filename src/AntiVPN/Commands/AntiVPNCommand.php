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

namespace AntiVPN\Commands;

use AntiVPN\Main;

use AntiVPN\utils\AntiVPNAPI;

use AntiVPN\Form\{WhiteListMainForm, ConfirmAddWhiteListForm, RemovePlayerForm, AddPlayerForm};

use pocketmine\Server;

use pocketmine\player\Player;

use pocketmine\command\{Command, CommandSender};

final class AntiVPNCommand extends Command 
{
	
	const COMMAND_PREFIX = '§6Anti§cVPN§r  ';
	
	/** @var Main **/
	private Main $manager;
	
	/** @var String **/
	private String $wlUsage = "§8---====(§6Anti§cVPN§f WhiteList§8)====---\n§8>  §f/{command_label} {argument_label} add <player_name> §7To add whitelisted player.\n§8>  §f/{command_label} {argument_label} remove <player_name>§7 To remove whitelisted player.\n§8>  §f/{command_label} {argument_label} list §7To see the whitelist.";
	
	public function __construct() 
	{
		$this->manager = Main::getInstance();
		parent::__construct 
		(
			'antivpn',
			'AntiVPN manager command',
			"§8---====(§6Anti§cVPN§8)====---\n§8>  §f/{command_label} wl §7To manage AntiVPN whitelist.",
			['avpn', 'antvpn', 'antiproxy']
		);
		$this->setPermission('antivpn.command');
	}
	
	public function execute(CommandSender $p, String $label, array $args) 
	{
		if ($this->testPermission($p))
		{
			if (isset($args[0]) && trim($args[0]) != '')
			{
				switch (strtolower($args[0]))
				{
					case 'wl':
					case 'white-list':
					case 'whitelist':
						if ($this->testPermission($p, 'antivpn.command.whitelist'))
						{
							if (isset($args[1]) && trim($args[1]) != '')
							{
								switch (strtolower($args[1]))
								{
									case 'add':
									case 'set':
									case 'new':
										if (isset($args[2]) && trim($args[2]) != '')
										{
											$target = Server::getInstance()->getPlayerExact($name = $args[2]);
											if ($target instanceof Player) 
											{
												$name = $target->getName();
											}
											
											if ($p instanceof Player) 
											{
												(new ConfirmAddWhiteListForm($name))->sendToPlayer($p);
											} else {
												$this->manager->addWhitelisted($name);
												$p->sendMessage(self::COMMAND_PREFIX . '§7Player §f' . $name . ' §7added to whitelist §a§lSuceffully§r§7.');
											}
											
										} else if ($p instanceof Player) {
											(new AddPlayerForm($p))->sendToPlayer($p);
										} else {
											$p->sendMessage(self::COMMAND_PREFIX . "§7To add whitelisted player use: §f/{$label} {$args[0]} {$args[1]} <player_name>");
										}
									break;
									case 'remove':
									case 'delete':
									case 'unset':
										if (isset($args[2]) && trim($args[2]) != '')
										{
											$user = $args[2];
											if ($this->manager->isWhitelisted($user))
											{
												$user = strtolower($user);
												$this->manager->getWhiteList()->remove($user);
												$p->sendMessage("§7User {$args[2]} §7removed §a§lSuceffully§r§7.");
											} else {
												$p->sendMessage(self::COMMAND_PREFIX . "§7Player §f{$user} §7is not whitelisted!");
											}
										} else if ($p instanceof Player) {
											if (count($this->manager->getWhiteList()->getAll()) > 0)
											{
												(new RemovePlayerForm)->sendToPlayer($p);
											} else {
												$p->sendMessage(self::COMMAND_PREFIX . '§7Theres no players added in WhiteList.');
											}
										} else {
											$p->sendMessage(self::COMMAND_PREFIX . "§7To remove player use: §f/{$label} {$args[0]} {$args[1]} <player_name>");
										}
									break;
									case 'list':
									case 'all':
										Main::sendWhiteList($p);
									break;
									default:
										if ($p instanceof Player) 
										{
											(new WhiteListMainForm())->sendToPlayer($p);
										} else {
											$this->showWhitelistUsageTo($p, $label, $args[0]);
										}
									break;
								}
							} else if ($p instanceof Player) {
								(new WhiteListMainForm())->sendToPlayer($p);
							} else {
								$this->showWhitelistUsageTo($p, $label, $args[0]);
							}
						}
					break;
					default:
						$this->showUsageTo($p, $label);
					break;
				}
			} else {
				$this->showUsageTo($p, $label);
			}
		}
	}
	
	public function showUsageTo(CommandSender $p, String $label) : void 
	{
		$p->sendMessage(str_replace('{command_label}', $label, $this->getUsage()));
	}
	
	public function showWhitelistUsageTo(CommandSender $player, String $commandLabel, String $whitelistLabel = 'wl') : void 
	{
		$usage = str_replace
		(
			['{command_label}', '{argument_label}'],
			[$commandLabel, $whitelistLabel],
			$this->wlUsage
		);
		$player->sendMessage($usage);
	}
	
}
