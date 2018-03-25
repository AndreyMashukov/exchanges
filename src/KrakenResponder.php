<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \AM\Exchanges\Responder;

/**
 * Responder for API kraken
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class KrakenResponder extends Responder
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
		if ($name === "ALL")
		    {
			$tickers = [
			    "bchxbt",
			    "dashxbt",
			    "eosxbt",
			    "etcxbt",
			    "ethxbt",
			    "gnoxbt",
			    "icnxbt",
			    "ltcxbt",
			    "mlnxbt",
			    "repxbt",
			    "xdgxbt",
			    "xlmxbt",
			    "xmrxbt",
			    "xrpxbt",
			    "zecxbt"
			];

			$name = implode(",", $tickers);
			$url  = KRAKEN_URL . "/0/public/Ticker?pair=" . $name;
		    }
		else
		    {
			$url = KRAKEN_URL . "/0/public/Ticker?pair=" . $name;
		    } //end if

		if (defined("EXCHANGE_CACHE_DIR") === true && KRAKEN_CACHE_ON === true)
		    {
			if ($this->getTimeDifference("kraken" . $name) > KRAKEN_REQUESTS_LIMIT || $this->getCache($url) === "")
			    {
				$result = $this->makeRequest($url, "kraken" . $name, [], [], true);
			    }
			else
			    {
				$result = $this->getCache($url);
			    } //end if

		    }
		else
		    {
			$result = $this->makeRequest($url, "kraken" . $name);
		    } //end if

		return $this->_convertAnswer(json_decode($result, true));
	    } //end getTickerData()


	/**
	 * Convert http API answer
	 *
	 * @param array  $ticker Json answer from API
	 *
	 * @return array Converted answer
	 */

	private function _convertAnswer(array $ticker):array
	    {
		$new = [];

		foreach ($ticker["result"] as $key => $value)
		    {
			if (preg_match("/^(X|Z){1}(?P<currency>[A-Z]+)(X|Z){1}(?P<second_currency>(XBT|USD))$/ui", $key, $result) > 0)
			    {
				$name       = strtoupper($result["second_currency"] . "_" . $result["currency"]);
				$change     = ($value["c"][0] - $value["o"]);
				$new[$name] = [
				    "last"          => $value["c"][0],
				    "baseVolume"    => $value["v"][0],
				    "percentChange" => (($change * 100) / $value["o"]),
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
			$pair = "xbtusd";
		    }
		else
		    {
			$pairs = [
			    "USDT_BTC" => "xbtusd",
			    "USDT_LTC" => "ltcusd",
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
