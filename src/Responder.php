<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \DateTime;
use \DateTimezone;
use \DateInterval;
use \Logics\Foundation\HTTP\HTTPclient;
use \AM\Exchanges\Traits\Exchange;

/**
 * Responder structure
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

abstract class Responder
    {

	use Exchange;

    /**
     * @var \Memcached
     */
    private $memcached = null;

    /**
     * Responder constructor.
     */
    public function __construct()
    {
        if (
            defined('BINANCE_CACHE_ON') === true && BINANCE_CACHE_ON === true ||
            defined('POLONIEX_CACHE_ON') === true && POLONIEX_CACHE_ON === true ||
            defined('KRAKEN_CACHE_ON') === true && KRAKEN_CACHE_ON === true ||
            defined('BITSTAMP_CACHE_ON') === true && BITSTAMP_CACHE_ON === true ||
            defined('BITFINEX_CACHE_ON') === true && BITFINEX_CACHE_ON === true ||
            defined('BITTREX_CACHE_ON') === true && BITTREX_CACHE_ON === true
        ) {
            $this->memcached = new \Memcached();
            $this->memcached->addServer('127.0.0.1', 11211);
        }
    }

    /**
	 * Get tickers data
	 *
	 * @param string $name Name of ticker
	 *
	 * @return array Ticker data
	 */

	abstract protected function getTickerData($name = "ALL"):array;

	/**
	 * Get crypto currency exchange rate
	 *
	 * @param string   $pair     Crypto currency pair
	 *
	 * @return array BTC rate ticker
	 */

	abstract protected function getCryptoRate(string $pair):array;

	/**
	 * Get ticker data
	 *
	 * @param string $name           Name of ticker pairs
	 * @param string $first          First currency in pair
	 * @param string $outputformat   Format of output
	 * @param int    $roundamendment Amendment for change value round
	 *
	 * @return string Ticker data
	 */

	public function getTicker(string $name, string $first = "BTC", string $outputformat = "html", int $roundamendment = 1):string
	    {
		$tickers = [];

		$pairdata       = explode("_", $name);
		$cryptocurrency = "";

		if (isset($pairdata[1]) === true)
		    {
			$cryptocurrency = $pairdata[1];
		    } //end if

		if ($name === "ALL")
		    {
			$tickers = $this->getTickerData($name);
		    } //end if

		if ($first === "USDT")
		    {
			$cryptoticker = $this->getCryptoRate($name);
			$first     = "USD";
			$tickers   = $this->_recalculate(
			    array_merge($tickers, $cryptoticker),
			    $first,
			    "",
			    1,
			    $cryptocurrency
			);
		    }
		else if ($this->_isNotCrypto($first) === true)
		    {
			$cryptoticker = $this->getCryptoRate($name);
			$tickers      = $this->_recalculate(
			    array_merge($tickers, $cryptoticker),
			    "USD",
			    $first,
			    $this->_getRate("USD", $first),
			    $cryptocurrency
			);
		    } //end if

		$this->_sortTickers($tickers);

		switch ($outputformat)
		    {
			case "html":
			    return $this->_getHTMLOutput($tickers, $first, $roundamendment);
			    break;
			case "json":
			    return $this->_getJSONOutput($tickers, $first, $roundamendment);
			    break;
			default:
			    return "";
		    } //end switch

	    } //end getTicker()


	/**
	 * Set cache data
	 *
	 * @param string $url    Request URL
	 * @param string $result Request result
     * @param int $expire Expire time
	 *
	 * @return void
	 */

    protected function setCache(string $url, string $result, int $expire)
	    {
            $this->memcached->set(md5($url), $result, $expire);
	    } //end setCache()


	/**
	 * Get cache data
	 *
	 * @param string $url Request URL
	 *
     * @return string|bool Last request result
	 */

    protected function getCache(string $url)
	    {
            return $this->memcached->get(md5($url));
	    } //end getCache()


	/**
	 * Make HTTP Request
	 *
	 * @param string $url     Request URL
	 * @param array  $request Request parameters
	 * @param array  $headers Request headers
	 * @param bool   $cache   Cache using flag
     * @param int $expire Cache expire time
	 *
	 * @return string Request result
	 */

    protected function makeRequest(
        string $url,
        array $request = [],
        array $headers = [],
        bool $cache = false,
        int $expire = 10
    ): string
    {
        $result = false;

        if (true === $cache) {
            $result = $this->getCache($url);
        }

        if ($result === false) {
            $http = new HTTPclient($url, $request, $headers);
            $result = $http->get();
            if (true === $cache) {
                $this->setCache($url, $result, $expire);
            }
        }

        return $result;
	    } //end makeRequest()


    } //end class

?>
