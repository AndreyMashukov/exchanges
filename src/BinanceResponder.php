<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \AM\Exchanges\Responder;

/**
 * Responder for API binance
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class BinanceResponder extends Responder
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
			$url = BINANCE_URL . "/api/v1/ticker/24hr";
		    }
		else
		    {
			$url = BINANCE_URL . "/api/v1/ticker/24hr?symbol=" . $name;
		    } //end if

		if (defined("EXCHANGE_CACHE_DIR") === true && BINANCE_CACHE_ON === true)
		    {
			if ($this->getTimeDifference("binance" . $name) > BINANCE_REQUESTS_LIMIT || $this->getCache($url) === "")
			    {
				$result = $this->makeRequest($url, "binance" . $name, [], [], true);
			    }
			else
			    {
				$result = $this->getCache($url);
			    } //end if

		    }
		else
		    {
			$result = $this->makeRequest($url, "binance" . $name);
		    } //end if


		return $this->_convertAnswer(json_decode($result, true));
	    } //end getTickerData()


	/**
	 * Convert http API answer
	 *
	 * @param array $answer Json answer from API
	 *
	 * @return array Converted answer
	 */

	private function _convertAnswer(array $answer):array
	    {
		$new = [];

		$convertdata = [
		    "lastPrice"          => "last",
		    "priceChangePercent" => "percentChange",
		    "quoteVolume"        => "baseVolume",
		];

		if (isset($answer["symbol"]) === false)
		    {
			foreach ($answer as $ticker)
			    {
				if (preg_match("/(?P<currency>[A-Z]+)(?P<second_currency>[A-Z]{3})/ui", $ticker["symbol"], $result) > 0)
				    {
					$name       = $result["second_currency"] . "_" . $result["currency"];
					$new[$name] = $ticker;

					foreach ($convertdata as $from => $to)
					    {
						$new[$name][$to] = $new[$name][$from];
						unset($new[$name][$from]);
					    } //end foreach

					$new[$name]["symbol"] = $name;
				    } //end if

			    } //end foreach

		    }
		else
		    {

			$ticker = $answer;

			if (preg_match("/(?P<second_currency>[A-Z]{3})(?P<currency>[A-Z]+)/ui", $ticker["symbol"], $result) > 0)
			    {
				$name       = $result["currency"] . "_" . $result["second_currency"];
				$new[$name] = $ticker;

				foreach ($convertdata as $from => $to)
				    {
					$new[$name][$to] = $new[$name][$from];
					unset($new[$name][$from]);
				    } //end foreach

				$new[$name]["symbol"] = $name;
			    } //end if

		    } //end if

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
		$symbol = $pair;

		if ($pair === "ALL")
		    {
			$symbol = "USDT_BTC";
			$pair   = "BTCUSDT";
		    }
		else
		    {
			$expl   = explode("_", $pair);
			$symbol = $pair;
			$pair   = $expl[1] . $expl[0];
		    }

		$ticker = $this->getTickerData($pair);
		$key    = str_replace("USDT", "USD", $symbol);

		return [
		    $key => $ticker[$symbol],
		];
	    } //end getCryptoRate()


    } //end class

?>