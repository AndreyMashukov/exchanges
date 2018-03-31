<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

use \AM\Exchanges\Responder;

/**
 * Responder for API bitstamp
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class BitstampResponder extends Responder
    {

    /**
     * BitstampResponder constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

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
			$tickers = ["xrpbtc", "ltcbtc", "ethbtc", "bchbtc"];
			$answer  = [];

			foreach ($tickers as $ticker)
			    {
				$new = $this->getTickerData($ticker);

				$answer = array_merge($new, $answer);
			    } //end foreach

			return $answer;
		    }
		else
		    {
			$url = BITSTAMP_URL . "/api/v2/ticker/" . $name;
		    } //end if

		if (defined("EXCHANGE_CACHE_DIR") === true && BITSTAMP_CACHE_ON === true) {
            $result = $this->makeRequest($url, [], [], true, BITSTAMP_REQUESTS_LIMIT);
        }
		else
		    {
                $result = $this->makeRequest($url);
		    } //end if

		return $this->_convertAnswer(json_decode($result, true), $name);
	    } //end getTickerData()


	/**
	 * Convert http API answer
	 *
	 * @param array  $ticker Json answer from API
	 * @param string $symbol Ticker name
	 *
	 * @return array Converted answer
	 */

	private function _convertAnswer(array $ticker, string $symbol):array
	    {
		$new = [];

		$convertdata = [
		    "volume" => "baseVolume",
		];

		if (preg_match("/(?P<currency>[A-Z]{3})(?P<second_currency>[A-Z]{3})/ui", $symbol, $result) > 0)
		    {
			$name       = strtoupper($result["second_currency"] . "_" . $result["currency"]);
			$new[$name] = $ticker;

			foreach ($convertdata as $from => $to)
			    {
				$new[$name][$to] = $new[$name][$from];
				unset($new[$name][$from]);
			    } //end foreach

			$new[$name]["symbol"] = $name;
			$change               = ($ticker["last"] - $ticker["vwap"]);
			$new[$name]["percentChange"] = (($change * 100) / $ticker["vwap"]);
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
			$pair = "btcusd";
		    }
		else
		    {
			$expl = explode("_", $pair);
			$pair = strtolower($expl[1] . $expl[0]);
		    } //end if

		$pair   = str_replace("usdt", "usd", $pair);
		$ticker = $this->getTickerData($pair);

		return $ticker;
	    } //end _getCryptoRate()


    } //end class

?>
