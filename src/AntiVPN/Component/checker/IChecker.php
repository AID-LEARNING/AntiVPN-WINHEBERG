<?php

namespace AntiVPN\Component\checker;

interface IChecker
{

	public function parser(array $data, array $extraData = []): string;

	public function getName(): string;

	public function check(string $address, string $key): \Generator;
}