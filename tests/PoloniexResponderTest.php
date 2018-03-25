<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace Test;

use \PHPUnit\Framework\TestCase;
use \AM\Exchanges\PoloniexResponder as Responder;
use \Logics\Tests\InternalWebServer;

/**
 * Poloniex Responder test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */

class PoloniexResponderTest extends TestCase
    {

	use InternalWebServer;

	/**
	 * Name folder which should be removed after tests
	 *
	 * @var string
	 */
	protected $remotepath;

	/**
	 * Testing host
	 *
	 * @var string
	 */
	protected $host;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		mkdir(__DIR__ . "/cache");

		$this->remotepath = $this->webserverURL();
		$this->host       = $this->remotepath . "/datasets/poloniex";
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		exec("rm -rf " . __DIR__ . "/cache");

		unset($this->remotepath);
	    } //end tearDown()


	/**
	 * Should return valid HTML block
	 *
	 * @return void
	 */

	public function testShouldReturnValidHtmlBlock()
	    {
		define("POLONIEX_URL", $this->host);

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "BTC", "html", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_default.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_default.html");
		$this->assertEquals($expected, $html);

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "ETH", "html", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eth.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eth.html");

		$this->assertEquals($expected, $html);
	    } //end testShouldReturnValidHtmlBlock()


	/**
	 * Should not contains dot on the end of value
	 *
	 * @return void
	 */

	public function testShouldNotContainsDotOnTheEndOfValue()
	    {
		define("POLONIEX_URL", $this->host);
		define("BLOOMBERG_URL", $this->remotepath . "/BloombergHTTPResponder.php");

		$responder = new Responder();
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur.json");

		$json = $responder->getTicker("ALL", "EUR", "json", 100);
		foreach (json_decode($json, true) as $ticker)
		    {
			$this->assertRegExp("/^[0-9]+\.?[0-9]{0,10}$/ui", $ticker["last"]);
		    } //end foreach

	    } //end testShouldNotContainsDotOnTheEndOfValue()


	/**
	 * Should return crypto-currency rates in other real currency
	 *
	 * @return void
	 */

	public function testShouldReturnCryptoCurrencyRatesInOtherRealCurrency()
	    {
		define("POLONIEX_URL", $this->host);

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "USDT", "html", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_usdt.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_usdt.html");

		$this->assertEquals($expected, $html);
	    } //end testShouldReturnCryptoCurrencyRatesInOtherRealCurrency()


	/**
	 * Should return exchange rates of any currencies
	 *
	 * @return void
	 */

	public function testShouldReturnExchangeRatesOfAnyCurrencies()
	    {
		define("POLONIEX_URL", $this->host);
		define("BLOOMBERG_URL", $this->remotepath . "/BloombergHTTPResponder.php");

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "EUR", "html", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur.html");

		$this->assertEquals($expected, $html);
	    } //end testShouldReturnExchangeRatesOfAnyCurrencies()


	/**
	 * Should allow to get JSON with tickers data
	 *
	 * @return void
	 */

	public function testShouldAllowToGetJsonWithTickersData()
	    {
		define("POLONIEX_URL", $this->host);
		define("BLOOMBERG_URL", $this->remotepath . "/BloombergHTTPResponder.php");

		$responder = new Responder();

		$json = $responder->getTicker("ALL", "EUR", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur.json", $json);
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur.json");
		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_BTC", "EUR", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur_btc.json", $json);
		$expected = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur_btc.json");
		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_LTC", "USDT", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur_ltc.json", $json);
		$expected = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur_ltc.json");
		$this->assertEquals($expected, $json);
	    } //end testShouldAllowToGetJsonWithTickersData()


	/**
	 * Should save often requests data
	 *
	 * @return void
	 */

	public function testShouldSaveOftenRequestsData()
	    {
		define("POLONIEX_URL", $this->host);
		define("EXCHANGE_CACHE_DIR", __DIR__ . "/cache");
		define("POLONIEX_CACHE_ON", true);
		define("POLONIEX_REQUESTS_LIMIT", 20);
		define("BLOOMBERG_URL", $this->remotepath . "/BloombergHTTPResponder.php");

		$responder = new Responder();
		$json = $responder->getTicker("ALL", "BTC", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_btc.json", $json);

		$responder = new Responder();
		$expected  = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur.json");
		$json = $responder->getTicker("ALL", "EUR", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur.json", $json);
		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_BTC", "EUR", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur_btc.json", $json);
		$expected = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur_btc.json");
		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_LTC", "USDT", "json", 100);
//file_put_contents(__DIR__ . "/datasets/poloniex/expected_eur_ltc.json", $json);
		$expected = file_get_contents(__DIR__ . "/datasets/poloniex/expected_eur_ltc.json");
		$this->assertEquals($expected, $json);
	    } //end testShouldSaveOftenRequestsData()


    } //end class


?>
