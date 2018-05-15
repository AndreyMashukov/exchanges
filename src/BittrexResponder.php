<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges;

/**
 * Responder for API bittrex
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

class BittrexResponder extends Responder
    {

    /**
     * BittrexResponder constructor.
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
			$url = BITTREX_URL . "/public/getmarketsummaries";
		    }
		else
		    {
			$url = BITTREX_URL . "/public/getmarketsummary?market=" . strtolower($name);
		    } //end if

            if (defined('BITTREX_CACHE_ON') === true && BITTREX_CACHE_ON === true) {
                $result = $this->makeRequest($url, [], [], true, BITTREX_REQUESTS_LIMIT);
		    }
		else
		    {
                $result = $this->makeRequest($url);
		    } //end if

		return $this->_convertAnswer(json_decode($result, true));
	    } //end getTickerData()


	/**
	 * Convert http API answer
	 *
	 * @param array  $answer Json answer from API
	 *
	 * @return array Converted answer
	 */

	private function _convertAnswer(array $answer):array
	    {
		$new = [];

		$convertdata = [
		    "Last"   => "last",
		    "Volume" => "baseVolume",
		];

		foreach ($answer["result"] as $ticker)
		    {
			if (preg_match("/(?P<currency>[A-Z]+)-(?P<second_currency>[A-Z]+)/ui", $ticker["MarketName"], $result) > 0)
			    {
				$name       = $result["currency"] . "_" . $result["second_currency"];
				$new[$name] = $ticker;

				foreach ($convertdata as $from => $to)
				    {
					$new[$name][$to] = $new[$name][$from];
					unset($new[$name][$from]);
				    } //end foreach

				$new[$name]["symbol"] = $name;
				$change               = ($new[$name]["last"] - $ticker["PrevDay"]);
				$new[$name]["percentChange"] = (($change * 100) / $ticker["PrevDay"]);
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
		$symbol = $pair;

		if ($pair === "ALL")
		    {
			$symbol = "USDT_BTC";
			$pair   = "USDT-BTC";
		    }
		else
		    {
			$expl   = explode("_", $pair);
			$symbol = $pair;
			$pair   = $expl[0] . "-" . $expl[1];
		    } //end if

		$ticker = $this->getTickerData($pair);
		$key    = str_replace("USDT", "USD", $symbol);

		return [
		    $key => $ticker[$symbol],
		];
	    } //end getCryptoRate()


    } //end class

?>
