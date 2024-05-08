<?php

namespace AntiVPN\Task;

use AntiVPN\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;

class RequestURLTask extends AsyncTask
{



	public function __construct(
		private readonly string $url,
		\Closure $resolve,
		\Closure $reject,
		private readonly ?ThreadSafeArray $optionsCurl = null,
		private readonly ?ThreadSafeArray $headers = null
	)
	{
		$this->storeLocal("resolve", $resolve);
		$this->storeLocal("reject", $reject);
	}

	private function parserHeaders(array $headers): array
	{
		$header = [];
		foreach ($headers as $key => $value) {
			$header[] = $key . ": " . $value;
		}
		return $header;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(): void
	{
		$optionsCurl = $this->optionsCurl ?? new ThreadSafeArray();
		$headers = $this->headers ?? new ThreadSafeArray();
		try {
			$request = Internet::simpleCurl($this->url, $optionsCurl['timeout'] ?? 5, $this->parserHeaders(Utils::threadToArray($headers)), Utils::threadToArray($optionsCurl));
			$this->setResult($request);
		}catch (InternetException $exception){
			$this->setResult($exception);
		}
	}

	public function onCompletion(): void{
		$result = $this->getResult();
		$resolve = $this->fetchLocal("resolve");
		$reject = $this->fetchLocal("reject");
		($result instanceof InternetException) ? ($reject)($result) : ($resolve)($result);
	}
}