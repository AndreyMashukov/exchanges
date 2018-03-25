<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \AM\Exchanges\Responder;

/**
 * Responder for API poloniex
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class PoloniexResponder extends Responder
    {

	/**
	 * Get tickers data
	 *
	 * @param string $name Name of ticker
	 *
	 * @return array Ticker data
	 */

	protected function getTickerData($name = "ALL"):array
	    {
		$url = POLONIEX_URL . "/public?command=returnTicker";

		if (defined("EXCHANGE_CACHE_DIR") === true && POLONIEX_CACHE_ON === true)
		    {
			if ($this->getTimeDifference("poloniex") > POLONIEX_REQUESTS_LIMIT || $this->getCache($url) === "")
			    {
				$result = json_decode($this->makeRequest($url, "poloniex", [], [], true), true);
			    }
			else
			    {
				$result = json_decode($this->getCache($url), true);
			    } //end if

		    }
		else
		    {
			$result = json_decode($this->makeRequest($url, "poloniex"), true);
		    } //end if

		if ($name === "ALL")
		    {
			return $result;
		    }
		else
		    {
			$name = strtoupper($name);
			if (isset($result[$name]))
			    {
				return $result[$name];
			    }
			else
			    {
				return array();
			    } //end if

		    } //end if

	    } //end getTickerData()


	/**
	 * Get crypto currency exchange rate
	 *
	 * @param string   $pair     Crypto currency pair
	 *
	 * @return array BTC rate ticker
	 */

	protected function getCryptoRate(string $pair):array
	    {
		if ($pair === "ALL")
		    {
			$pair = "USDT_BTC";
		    } //end if

		$ticker = $this->getTickerData($pair);
		$key    = str_replace("USDT", "USD", $pair);

		return [
		    $key => $ticker,
		];
	    } //end getCryptoRate()


    } //end class

?>
