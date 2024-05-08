<?php

namespace AntiVPN\Component\checker;

use AntiVPN\Component\AntiVPNManager;
use AntiVPN\Exception\PlayerHasVPNException;
use AntiVPN\Exception\StatusResponseException;
use Generator;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use SOFe\AwaitGenerator\Await;

class CheckerIphubInfo implements IChecker
{

	private const URL= "https://v2.api.iphub.info/ip/";

	public function parser(array $data, array $extraData = []): string
	{
		$address = $data['address'];
		return self::URL . $address;
	}

	public function getName(): string
	{
		return 'iphub.info';
	}

	public function check(string $address, string $key): Generator
	{
		return Await::promise(function ($resolve, $reject) use ($address, $key) {
			Await::f2c(/**
			 * @throws StatusResponseException
			 * @throws PlayerHasVPNException
			 */ function () use ($address, $key): Generator {
				$result = yield from AntiVPNManager::getInstance()->asyncRequest($this->parser([
					'address' => $address
				]), [
					"Accept" => "application/json",
					'x-key' => $key
				], [
					CURLOPT_SSL_VERIFYSTATUS => false,
				]);
				if ($result instanceof InternetException)
					return $result;
				if ($result->getCode() !== 200){
					return new StatusResponseException("no response from server");
				}
				$json = json_decode($result->getBody(), true);
				if (json_last_error() !== JSON_ERROR_NONE){
					return new StatusResponseException("invalid json");
				}
				if ($json['block'] !== 0)
					throw new PlayerHasVPNException("$address is VPN/Proxy ip");
			}, $resolve, $reject);
		});
	}
}