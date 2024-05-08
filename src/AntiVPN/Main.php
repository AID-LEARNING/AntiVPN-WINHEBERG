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

namespace AntiVPN;

use AntiVPN\Component\AntiVPNManager;
use AntiVPN\Exception\PlayerHasVPNException;
use AntiVPN\Exception\StatusResponseException;
use AntiVPN\utils\AntiVPNAPI;

use AntiVPN\Task\CheckTask;

use AntiVPN\Event\StartCheckEvent;

use AntiVPN\Commands\AntiVPNCommand;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use pocketmine\player\Player;
use pocketmine\utils\InternetException;
use SOFe\AwaitGenerator\Await;

final class Main extends PluginBase
{
	
	/** @var Config **/
	private Config $whiteList;
	
	/** @var Config | null **/
	private ?Config $cache = null;
	
	/** @var Main **/
	private static Main $instance;
	
	/** @var bool **/
	private bool $hasCacheEnabled = true;
	
	/** @var String[] **/
	private array $inProcess = [];
	
	public static function getInstance() : self 
	{
		return self::$instance;
	}
	
	public function onLoad() : void 
	{
		self::$instance = $this;
		@$this->saveResource('config.yml');
		new AntiVPNManager($this);
	}
	
	public function onEnable() : void 
	{
		$this->getLogger()->info('Loading preferences...');
		$this->loadPreferences();
		$this->getLogger()->info('Loading WhiteList...');
		$this->initWhiteList();
		$this->getLogger()->info('Loading command...');
		$this->initCommand();
		$this->getLogger()->info('Loading Listener...');
		(new EventsListener($this));
	}
	
	public function onDisable() : void 
	{
		if ($this->isCacheEnabled())
		{
			$this->getCacheList()->save();
		}
		if ($this->whiteList instanceof Config) 
		{
			$this->whiteList->save();
		}
	}
	private function loadPreferences() : void 
	{
		$hasCacheEnabled = $this->getConfigValue('enable-cache') == 'true';
		$this->hasCacheEnabled = $hasCacheEnabled;
		if ($hasCacheEnabled)
		{
			$dir = $this->getDataFolder() . 'cache.json';
			$this->cache = new Config($dir, Config::JSON);
			$this->getLogger()->info('Cache enabled.');
		} else {
			$this->getLogger()->info('Cache disabled.');
		}
	}
	
	private function initWhiteList() : void 
	{
		$this->whiteList = new Config($this->getDataFolder() .  'whitelist.txt', Config::ENUM);
		$this->getLogger()->info('WhiteList loaded suceffully.');
		if (($c = count($this->whiteList->getAll())) <= 0)
		{
			$this->getLogger()->info('You can add players that will be ignored by this system using: /antivpn wl add <player_name>');
		} else {
			$this->getLogger()->info('Theres ' . $c . ' players whitelisted.');
		}
	}
	
	private function initCommand() : void 
	{
		$this->getServer()->getCommandMap()->register('antivpn', new AntiVPNCommand());
	}
	
	public function getConfigValue(String $id, mixed $default = null) : mixed 
	{
		if ($this->getConfig()->exists($id))
		{
			return $this->getConfig()->get($id);
		}
		$this->getLogger()->alert('Config with id ' . $id . ' not found!');
		return $default;
	}
	
	public function isCacheEnabled() : bool 
	{
		return $this->hasCacheEnabled;
	}
	
	public function getCacheList() : ? Config 
	{
		return $this->cache;
	}
	
	public function getWhiteList() : Config 
	{
		return $this->whiteList;
	}
	
	public function isWhiteListed(Player | String $player) : bool 
	{
		if ($player instanceof Player)
		{
			$player = $player->getName();
		}
		return $this->whiteList->exists($player, true);
	}
	
	public function addWhitelisted(String | Player $player) : void 
	{
		if ($player instanceof Player)
		{
			$player = $player->getName();
		}
		$this->whiteList->set(strtolower($player));
	}
	
	public function inCache(Player $player) : bool 
	{
		$address = $player->getNetworkSession()->getIp();
		return $this->getCacheList()->exists($address);
	}
	
	public function getCacheValue(Player $player) : bool 
	{
		if ($this->inCache($player))
		{
			$adr = $player->getNetworkSession()->getIp();
			return $this->getCacheList()->get($adr, false) == 'true';
		}
		return false;
	}
	
	public function addCachedValue(String $ip, bool $result) : void 
	{
		$this->getCacheList()->set($ip, $result);
	}

	public function getKickScreenMessage(String $playerName) : String 
	{
		$message = (string) $this->getConfigValue('kick-screen-message', '§cYou can\'t use vpn here!');
		return str_replace('{player}', $playerName, $message);
	}
	
	public function getAdminAlertMessage(String $playerName) : String 
	{
		$message = (string) $this->getConfigValue('alert-admin-message', '');
		return str_replace('{player}', $playerName, $message);
	}
	
	public function addInProcess(String $ip) : void 
	{
		if (!in_array($ip, $this->inProcess))
		{
			$this->inProcess[] = $ip;
		}
	}
	
	public function inProcess(String $ip) : bool 
	{
		return in_array($ip, $this->inProcess);
	}
	
	public function removeFromProcess(String $ip) : void 
	{
		if (in_array($ip, $this->inProcess))
		{
			$id = array_search($ip, $this->inProcess);
			unset($this->inProcess[$id]);
		}
	}
	
	public function startCheck(Player $player, callable $callbackCheck) : bool
	{
		
		if ($this->inProcess($ip = $player->getNetworkSession()->getIp())) {
			$this->getLogger()->debug('tring to check ip ' . $ip . ' but this ip already is checking by the system.');
			return false;
		}
		
		$ev = new StartCheckEvent($player);
		$ev->call();
		if (!$ev->isCancelled())
		{
			Await::g2c(AntiVPNManager::getInstance()->checkAllCheckers($player->getNetworkSession()->getIp()), /**
 * @var (\Exception|void)[] $resolves
			 * **/function (array $resolves) use ($player, $callbackCheck) {
				foreach ($resolves as $key => $resolve){
					if ($resolve instanceof \Exception)
						Main::getInstance()->getLogger()->warning("[$key] " . $resolve->getMessage());
				}
				$callbackCheck($player, true);
			}, [
				PlayerHasVPNException::class => function () use($player, $callbackCheck) {
					$callbackCheck($player, false);
				}
			]);
			//$this->getServer()->getAsyncPool()->submitTask(new CheckTask($ip, strtolower($player->getName()), $this->getKey(), $callbackCheck));
			return true;
		}
		return false;
	}
	
	public static function sendWhiteList(Player $player) : void 
	{
		$list = Main::getInstance()->getWhiteList()->getAll(true);
		if (count($list) > 0)
		{
			$list = implode('§r, ', $list);
			$player->sendMessage(AntiVPNCommand::COMMAND_PREFIX . '§7Whitelisted players: §f' . $list);
		} else {
			$player->sendMessage(AntiVPNCommand::COMMAND_PREFIX . '§7WhiteList is empty!');
		}
	}
	
}