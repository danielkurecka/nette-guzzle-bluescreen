<?php

declare(strict_types=1);

namespace Daku\Nette\Guzzle;

use GuzzleHttp\Exception\RequestException;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Psr\Http\Message\MessageInterface;

class GuzzleBlueScreenExtension extends CompilerExtension
{

	/** @var int The maximum body length that will be logged, if exceeded it will be truncted. */
	public static $maxBodyLength = 100 * 1024;

	public static $prettyPrint = true;


	public function loadConfiguration()
	{
		$config = $this->validateConfig([
			'maxBodyLength' => self::$maxBodyLength,
			'prettyPrint' => self::$prettyPrint
		]);

		self::$prettyPrint = $config['prettyPrint'];
		self::$maxBodyLength = $config['maxBodyLength'];
	}


	public function afterCompile(ClassType $class)
	{
		$initialize = $class->getMethod('initialize');
		$initialize->addBody("Tracy\Debugger::getBlueScreen()->addPanel(['" . self::class . "', 'renderException']);");
	}


	public static function renderException(?\Throwable $e)
	{
		if ($e instanceof RequestException) {
			$request = $e->getRequest();
			$response = $e->getResponse();
			$panel = '<p><b data-tracy-ref="^+" class="tracy-toggle">Request headers:</b></p>' . self::formatHeaders($request);
			$panel .= '<p><b data-tracy-ref="^+" class="tracy-toggle">Reqest body:</b></p>' . self::formatBody($request);
			$panel .= '<p><b data-tracy-ref="^+" class="tracy-toggle">Response headers:</b></p>' . self::formatHeaders($response);
			$panel .= '<p><b data-tracy-ref="^+" class="tracy-toggle">Response body:</b></p>' . self::formatBody($response);
			return ['tab' => 'Guzzle', 'panel' => $panel];
		}
		return null;
	}


	public static function formatHeaders(?MessageInterface $message)
	{
		$return = '<pre class="code">';
		if ($message === null) {
			$return .= '<i>unknown</i>';

		} else {
			foreach ($message->getHeaders() as $name => $values) {
				foreach ($values as $value) {
					$return .= "$name: $value\n";
				}
			}
		}
		$return .= '</pre>';
		return $return;
	}


	public static function formatBody(?MessageInterface $message)
	{
		$truncated = false;
		if ($message === null) {
			$content = '<i>unknown</i>';

		} else {
			$body = $message->getBody();

			if ($body->getSize() === 0) {
				$content = '<i>empty</i>';

			} else {
				$body->rewind();

				if ($body->getSize() > self::$maxBodyLength) {
					$content = $body->read(self::$maxBodyLength);
					$truncated = true;
				} else {
					$content = $body->getContents();
				}

				if (preg_match('/[^\pL\pM\pN\pP\pS\pZ\n\r\t]/', $content)) {
					$content = '<i>unprintable content</i>';

				} elseif (self::$prettyPrint && strpos($message->getHeaderLine('Content-Type'), 'application/json') !== false) {
					$content = htmlspecialchars(self::prettyPrintJson($content));

				} else {
					$content = htmlspecialchars($content);
				}
			}
		}

		return '<pre class="code">' . $content . ($truncated ? "\n<i>(truncated...)</i>" : '') . '</pre>';
	}


	// source: https://stackoverflow.com/a/9776726
	public static function prettyPrintJson(string $json)
	{
		$result = '';
		$level = 0;
		$inQuotes = false;
		$inEscape = false;
		$endsLineLevel = null;
		$length = strlen($json);

		for ($i = 0; $i < $length; $i++) {
			$char = $json[$i];
			$newLineLevel = null;
			$post = '';
			if ($endsLineLevel !== null) {
				$newLineLevel = $endsLineLevel;
				$endsLineLevel = null;
			}
			if ($inEscape) {
				$inEscape = false;
			} else {
				if ($char === '"') {
					$inQuotes = !$inQuotes;
				} else {
					if (!$inQuotes) {
						switch ($char) {
							case '}':
							case ']':
								$level--;
								$endsLineLevel = null;
								$newLineLevel = $level;
								break;

							case '{':
							case '[':
								$level++;
							case ',':
								$endsLineLevel = $level;
								break;

							case ':':
								$post = ' ';
								break;

							case ' ':
							case "\t":
							case "\n":
							case "\r":
								$char = '';
								$endsLineLevel = $newLineLevel;
								$newLineLevel = null;
								break;
						}
					} else {
						if ($char === '\\') {
							$inEscape = true;
						}
					}
				}
			}
			if ($newLineLevel !== null) {
				$result .= "\n" . str_repeat("    ", $newLineLevel);
			}
			$result .= $char . $post;
		}

		return $result;
	}

}
