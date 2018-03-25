<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace Test;

use \PHPUnit\Framework\TestCase;
use \AM\Exchanges\BittrexResponder as Responder;
use \Logics\Tests\InternalWebServer;

/**
 * Bittrex Responder test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */

class BittrexResponderTest extends TestCase
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
		$this->host       = $this->remotepath . "/datasets/bittrex/api/v1";

		define("BLOOMBERG_URL", $this->remotepath . "/BloombergHTTPResponder.php");
		define("BITTREX_URL", $this->host);
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
		$responder = new Responder();
		$html      = $responder->getTicker("ALL");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_default.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_default.html");
		$this->assertEquals($expected, $html);

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "ETH");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eth.html", $html);

		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eth.html");
		$this->assertEquals($expected, $html);
	    } //end testShouldReturnValidHtmlBlock()


	/**
	 * Should return crypto-currency rates in other real currency
	 *
	 * @return void
	 */

	public function testShouldReturnCryptoCurrencyRatesInOtherRealCurrency()
	    {

		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "USDT");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_usdt.html", $html);

		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_usdt.html");
		$this->assertEquals($expected, $html);
	    } //end testShouldReturnCryptoCurrencyRatesInOtherRealCurrency()


	/**
	 * Should return exchange rates of any currencies
	 *
	 * @return void
	 */

	public function testShouldReturnExchangeRatesOfAnyCurrencies()
	    {
		$responder = new Responder();
		$html      = $responder->getTicker("ALL", "EUR");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur.html", $html);
		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur.html");

		$this->assertEquals($expected, $html);
	    } //end testShouldReturnExchangeRatesOfAnyCurrencies()


	/**
	 * Should allow to get JSON with tickers data
	 *
	 * @return void
	 */

	public function testShouldAllowToGetJsonWithTickersData()
	    {
		$responder = new Responder();
		$json      = $responder->getTicker("ALL", "EUR", "json");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur.json", $json);
		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur.json");

		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_BTC", "EUR", "json");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur_btc.json", $json);

		$expected = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur_btc.json");
		$this->assertEquals($expected, $json);

		$json     = $responder->getTicker("USDT_LTC", "USDT", "json");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur_ltc.json", $json);
		$expected = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur_ltc.json");
		$this->assertEquals($expected, $json);
	    } //end testShouldAllowToGetJsonWithTickersData()


	/**
	 * Should save often requests data
	 *
	 * @return void
	 */

	public function testShouldSaveOftenRequestsData()
	    {
		define("EXCHANGE_CACHE_DIR", __DIR__ . "/cache");
		define("BITTREX_CACHE_ON", true);
		define("BITTREX_REQUESTS_LIMIT", 20);

		$responder = new Responder();
		$json      = $responder->getTicker("ALL", "EUR", "json");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur.json", $json);
		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur.json");

		$this->assertEquals($expected, $json);

		$json      = $responder->getTicker("ALL", "EUR", "json");
//file_put_contents(__DIR__ . "/datasets/bittrex/expected_eur.json", $json);
		$expected  = file_get_contents(__DIR__ . "/datasets/bittrex/expected_eur.json");

		$this->assertEquals($expected, $json);
	    } //end testShouldSaveOftenRequestsData()


    } //end class


?>
