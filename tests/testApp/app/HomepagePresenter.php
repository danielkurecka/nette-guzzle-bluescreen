<?php

declare(strict_types=1);

namespace Daku\Nette\Guzzle\Tests\TestApp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Nette\Application\UI\Presenter;

class HomepagePresenter extends Presenter
{

	public function renderDefault()
	{
		$client = new Client;
		$request = new Request('GET', 'http://httpbin.org/json');
		$response = $client->send($request);
		throw BadResponseException::create($request, $response);
	}

}
