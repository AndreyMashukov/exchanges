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
	 *
	 * @return void
	 */

	protected function setCache(string $url, string $result)
	    {
		$cachedir = EXCHANGE_CACHE_DIR . "/cache";
		if (file_exists($cachedir) === false)
		    {
			mkdir($cachedir);
		    } //end if

		file_put_contents($cachedir . "/" . md5($url), $result);
	    } //end setCache()


	/**
	 * Get cache data
	 *
	 * @param string $url Request URL
	 *
	 * @return string Last good request result
	 */

	protected function getCache(string $url):string
	    {
		$cachedir = EXCHANGE_CACHE_DIR . "/cache";
		$cache    = $cachedir . "/" . md5($url);

		if (file_exists($cache) === true)
		    {
			return file_get_contents($cache);
		    }
		else
		    {
			return "";
		    } //end if

	    } //end getCache()


	/**
	 * Get last request time difference
	 *
	 * @param string $key Unique key of service
	 *
	 * @return int Time difference
	 */

	protected function getTimeDifference(string $key):int
	    {
		$now  = new DateTime("now", new DateTimezone("UTC"));
		$last = EXCHANGE_CACHE_DIR . "/" . md5("last_" . $key) . ".txt";

		if (file_exists($last) === false)
		    {
			$tosave = new DateTime("now", new DateTimezone("UTC"));;
			$tosave->sub(new DateInterval('PT21S'));
			file_put_contents($last, $tosave->format("d.m.Y H:i:s"));
		    } //end if

		$lasttime = new DateTime(file_get_contents($last), new DateTimezone("UTC"));
		return ($now->getTimestamp() - $lasttime->getTimestamp());
	    } //end getTimeDifference()


	/**
	 * Make HTTP Request
	 *
	 * @param string $url     Request URL
	 * @param string $key     Unique key of service
	 * @param array  $request Request parameters
	 * @param array  $headers Request headers
	 * @param bool   $cache   Cache using flag
	 *
	 * @return string Request result
	 */

	protected function makeRequest(string $url, string $key = "", array $request = [], array $headers = [], bool $cache = false):string
	    {
		$http   = new HTTPclient($url, $request, $headers);
		$result = $http->get();

		if ($cache !== false)
		    {
			$now  = new DateTime("now", new DateTimezone("UTC"));
			$last = EXCHANGE_CACHE_DIR . "/" . md5("last_" . $key) . ".txt";

			if ($http->lastcode() !== 200)
			    {
				$now->add(new DateInterval('PT600S'));
				$result = $this->getCache($url);
			    }
			else
			    {
				$this->setCache($url, $result);
			    } //end if

			file_put_contents($last, $now->format("d.m.Y H:i:s"));

		    } // end if

		return $result;
	    } //end makeRequest()


    } //end class

?>
