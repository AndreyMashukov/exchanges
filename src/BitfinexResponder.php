<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \AM\Exchanges\Responder;

/**
 * Responder for API bitfinex
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class BitfinexResponder extends Responder
    {


	/**
	 * Cached request
	 *
	 * @param string $url Request URL
	 * @param string $key Cache key
	 *
	 * @return string Request result
	 */

	private function _cachedRequest(string $url, string $key):string
	    {
		if (defined("EXCHANGE_CACHE_DIR") === true && BITFINEX_CACHE_ON === true)
		    {
			if ($this->getTimeDifference($key) > BITFINEX_REQUESTS_LIMIT || $this->getCache($url) === "")
			    {
				$result = $this->makeRequest($url, $key, [], [], true);
			    }
			else
			    {
				$result = $this->getCache($url);
			    } //end if

		    }
		else
		    {
			$result = $this->makeRequest($url, $key);
		    } //end if

		return $result;
	    } //end _cachedRequest()


	/**
	 * Get tickers data
	 *
	 * @param string $name Name of ticker
	 *
	 * @return array Ticker data
	 */

	protected function getTickerData($name = "ALL"):array
	    {
		if ($name === "ALL")
		    {
			$url = BITFINEX_URL . "/v1/symbols";

			$result  = $this->_cachedRequest($url, "bitfinex" . $name);
			$tickers = json_decode($result, true);
			$name    = "";

			foreach ($tickers as $ticker)
			    {
				if (preg_match("/^[a-z]+btc$/ui", $ticker) > 0)
				    {
					$name .= ",t" . strtoupper($ticker);
				    } //end if

			    } //end foreach

			$url = BITFINEX_URL . "/v2/tickers?symbols=" . ltrim($name, ",");
		    }
		else
		    {
			$url = BITFINEX_URL . "/v2/tickers?symbols=t" . $name;
		    } //end if

		$result = $this->_cachedRequest($url, "bitfinex" . $name);

		return $this->_convertAnswer(json_decode($result, true));
	    } //end getTickerData()


	/**
	 * Convert http API answer
	 *
	 * [0] => Array
	 * (
	 *     [0] => tLTCBTC          SYMBOL
	 *     [1] => 0.016773         BID
	 *     [2] => 686.72667638     BID_SIZE
	 *     [3] => 0.016783         ASK
	 *     [4] => 747.92804025     ASK_SIZE
	 *     [5] => 0.000825         DAILY_CHANGE
	 *     [6] => 0.0516           DAILY_CHANGE_PERC
	 *     [7] => 0.016825         LAST_PRICE
	 *     [8] => 118481.89970819  VOLUME
	 *     [9] => 0.017205         HIGH
	 *     [10] => 0.014931        LOW
	 * )
	 *
	 * @param array $ticker Json answer from API
	 *
	 * @return array Converted answer
	 */

	private function _convertAnswer(array $ticker):array
	    {
		$new = [];

		foreach ($ticker as $value)
		    {
			if (preg_match("/^t(?P<currency>[A-Z]+)(?P<second_currency>(BTC|USD))$/ui", $value[0], $result) > 0)
			    {
				$name       = strtoupper($result["second_currency"] . "_" . $result["currency"]);
				$new[$name] = [
				    "last"          => $value[7],
				    "baseVolume"    => $value[8],
				    "percentChange" => $value[6],
				];
			    } //end if

		    } //end foreach

		return $new;
	    } //end _convertAnswer()


	/**
	 * Get crypto currency exchange rate
	 *
	 * @param Poloniex $poloniex Poloniex API client
	 * @param string   $pair     Crypto currency pair
	 *
	 * @return array BTC rate ticker
	 */

	protected function getCryptoRate(string $pair):array
	    {
		if ($pair === "ALL")
		    {
			$pair = "BTCUSD";
		    }
		else
		    {
			$pairs = [
			    "USDT_BTC" => "BTCUSD",
			    "USDT_LTC" => "LTCUSD",
			];

			if (isset($pairs[$pair]) === true)
			    {
				$pair = $pairs[$pair];
			    } //end if

		    } //end if

		$ticker = $this->getTickerData($pair);

		return $ticker;
	    } //end _getCryptoRate()


    } //end class

?>
