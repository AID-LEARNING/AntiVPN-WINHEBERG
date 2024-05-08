<?php

namespace AntiVPN\Component;

use AntiVPN\Component\checker\CheckerIphubInfo;
use AntiVPN\Component\checker\CheckerVpnApiIO;
use AntiVPN\Component\checker\IChecker;
use AntiVPN\Main;
use AntiVPN\Task\RequestURLTask;
use AntiVPN\utils\Utils;
use SOFe\AwaitGenerator\Await;
use Symfony\Component\Filesystem\Path;

class AntiVPNManager
{
	private static AntiVPNManager $instance;

	private array $keys = [];


	/**
	 * @var IChecker[]
	 */
	private array $checkers = [];

	public function __construct(
		private Main $plugin
	)
	{
		foreach ($this->plugin->getConfig()->get('keys') as $key => $value){
			$this->keys[$key] = $value;
		}
		$this->initDefaultChecker();
		self::$instance = $this;
	}

	private function initDefaultChecker(): void
	{
		$this->registerChecker(new CheckerVpnApiIO());
		$this->registerChecker(new CheckerIphubInfo());
	}

	/**
	 * @throws \Exception
	 */
	public function registerChecker(IChecker $checker, bool $overwrite = false): void
	{
		if (!$overwrite && isset($this->checkers[$checker->getName()])) {
			throw new \Exception("impossible register checker {$checker->getName()} because it already exists");
		}
		$this->checkers[$checker->getName()] = $checker;
	}

	public function asyncRequest(string $url, array $headers, array $options): \Generator
	{
		return Await::promise(function ($resolve, $reject) use ($url, $headers, $options) {
			$this->plugin->getServer()->getAsyncPool()->submitTask(new RequestURLTask(
				$url,
				$resolve,
				$reject,
				Utils::arrayToThread($options),
				Utils::arrayToThread($headers)
			));
		});
	}

	public function checkAllCheckers(string $address): \Generator
	{
		return Await::all(array_map(fn(IChecker $checker) => $checker->check($address, $this->keys[$checker->getName()]), $this->checkers));
	}

	/**
	 * @return AntiVPNManager
	 */
	public static function getInstance(): AntiVPNManager
	{
		return self::$instance;
	}

}