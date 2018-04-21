<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace AM\Exchanges\Traits;

use \AM\Crossrates\BloombergBot;

/**
 * Exchange methods trait
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */
trait Exchange
{
    public $fiat = ["EUR", "RUB", "KZT", "BYN", "UAH", "CNY", "GEL"];

    /**
     * Check currency in real currencies list
     *
     * @param string $name Name of currency
     *
     * @return bool Check result
     */

    private function _isNotCrypto(string $name): bool
    {
        if (in_array($name, $this->fiat) === true) {
            return true;
        } else {
            return false;
        } //end if

    } //end _isNotCrypto()


    /**
     * Sort tickers array
     *
     * @param array  $tickers  Link to tickers array
     * @param string $sorttype Sorting type
     *
     * @return void
     */

    private function _sortTickers(array &$tickers, string $sorttype = "baseVolume")
    {
        uasort($tickers, function ($a, $b) use ($sorttype) {
            if ($a[$sorttype] === $b[$sorttype]) {
                return 0;
            } else {
                return (($a[$sorttype] >= $b[$sorttype]) ? -1 : 1);
            } //end if

        });
    } //end _sortTickers()


    /**
     * Get html output
     *
     * @param array  $tickers        Tickers array from API poloniex
     * @param string $first          Short name of first currency
     * @param int    $roundamendment Round amendment of exchange
     *
     * @return string HTML output
     */

    private function _getHTMLOutput(array $tickers, string $first, int $roundamendment = 1): string
    {
        $html = "";

        foreach ($tickers as $pair => $ticker) {
            $exploded = explode("_", $pair);
            if (mb_strtoupper($first) === mb_strtoupper($exploded[0]) || mb_strtoupper($exploded[0]) === "XBT" && mb_strtoupper($first) === "BTC") {
                $change = round(($ticker["percentChange"] * $roundamendment), 2);
                if ($change >= 0) {
                    $class = 'class="u"';
                } else {
                    $class = 'class="d"';
                }

                $html .= '<tr><td>' . $exploded[1] . '</td><td>' . $this->_fixExponent($ticker["last"]) . '</td><td>' . $this->_round($ticker["baseVolume"]) . '</td><td ' . $class . '>' .
                    (($change >= 0) ? (($change !== 0) ? '+' : '') .
                        $this->_round($change) : $this->_round($change)) .
                    '</td><td>' . $this->_getName($exploded[1]) . '</td></tr>';
            } //end if
        }

        return $html;
    } //end _getHTMLOutput()


    /**
     * Get JSON output
     *
     * @param array  $tickers        Tickers array from API poloniex
     * @param string $first          Short name of first currency
     * @param int    $roundamendment Round amendment of exchange
     *
     * @return string JSON output
     */

    private function _getJSONOutput(array $tickers, string $first, int $roundamendment = 1): string
    {
        $array = [];

        foreach ($tickers as $pair => $ticker) {
            $exploded = explode("_", $pair);

            if (mb_strtoupper($first) === mb_strtoupper($exploded[0]) || mb_strtoupper($exploded[0]) === "XBT" && mb_strtoupper($first) === "BTC") {
                $newpair         = $first . "_" . $exploded[1];
                $array[$newpair] = [];
                $change          = round(($ticker["percentChange"] * $roundamendment), 2);

                if ($change >= 0) {
                    $array[$newpair]["change_tag"] = "up";
                } else {
                    $array[$newpair]["change_tag"] = "down";
                }

                $array[$newpair]["change"]   = (($change >= 0) ? (($change !== 0) ? '+' : '') .
                    $this->_round($change) : $this->_round($change));
                $array[$newpair]["name"]     = $this->_getName($exploded[1]);
                $array[$newpair]["last"]     = $ticker["last"];
                $array[$newpair]["volume"]   = $ticker["baseVolume"];
                $array[$newpair]["currency"] = $exploded[1];
            } //end if
        }

        return json_encode($array);
    } //end _getJSONOutput()


    /**
     * Get currencies crossrate
     *
     * @param string $first  First currency
     * @param string $second Second currency
     *
     * @return float Crossrate
     */

    private function _getRate(string $first, string $second): float
    {
        $bot  = new BloombergBot();
        $rate = $bot->get($first, $second);

        return $rate["srtaight"]["value"];
    } //end _getRate()


    /**
     * Recalculate currency last rate in new currency
     *
     * @param array  $tickers        All currencies tickers
     * @param string $currency       Name of new currency
     * @param string $newcurrency    New currency name
     * @param float  $rate           New currency rate amendment
     * @param string $cryptocurrency Name of current cryptocurrency
     *
     * @return array Recalculated currencies tickers
     */

    private function _recalculate(
        array $tickers,
        string $currency,
        string $newcurrency = "",
        float $rate = 1,
        string $cryptocurrency = ""
    ): array {
        $crypto = "BTC";

        if ($cryptocurrency !== "") {
            $crypto = $cryptocurrency;
        } //end if

        if (isset($tickers[$currency . "_" . $crypto]) === false) {
            $crypto = "XBT";
        } //end if

        $amendment = ($tickers[$currency . "_" . $crypto]["last"] * $rate);
        $new       = [];

        foreach ($tickers as $pair => $ticker) {
            $exploded = explode("_", $pair);

            if ($exploded[0] === $crypto || $exploded[0] === $currency) {
                $newname = $currency;
                if ($newcurrency !== "") {
                    $newname = $newcurrency;
                } //end if

                $new[$newname . "_" . $exploded[1]] = $ticker;
                $value                              = $ticker["last"];

                if ($currency . "_" . $exploded[1] !== $pair) {
                    $value = $ticker["last"] * $amendment;
                } else {
                    $value = $ticker["last"] * $rate;
                } //end if

                $new[$newname . "_" . $exploded[1]]["last"] = $this->_round($value);
            } //end if

        } //end foreach

        return $new;
    } //end _recalculate()


    /**
     * Fix exponent
     *
     * @param string $value Float value
     *
     * @return float or string
     */

    private function _fixExponent(string $value)
    {
        if (preg_match("/(?P<value>[0-9.]+)E-(?P<degree>[0-9]{1,2})/ui", $value, $result) > 0) {
            $string = "0.";
            for ($i = 1; $i < $result["degree"]; $i++) {
                $string .= "0";
            } //end for

            $string .= str_replace(".", "", round($result["value"], 4));
            $value  = (string) $string;

            return (string) $value;
        } else {
            return rtrim(rtrim($value, "0"), ".");
        } //end if

    } //end _fixExponent()


    /**
     * Round value for best view
     *
     * @param float $value Price of currency
     *
     * @return string Formated value
     */

    private function _round(float $value): string
    {
        if ($value > 1) {
            $value = round($value, 2);
        } else {
            $value = round($value, 4);
        } //end if

        $expl = explode(".", $value);
        if (isset($expl[1]) === true) {
            if (strlen($expl[1]) === 1) {
                $value .= 0;
            } //end if

        } else {
            $value .= ".00";
        } //end if

        return (string) $value;
    } //end _round()


    /**
     * Get coin name
     *
     * @param string $name Coin alias
     *
     * @return string Name of coin
     */

    private function _getName(string $name): string
    {
        $data  = file_get_contents(__DIR__ . "/../data/names.json");
        $names = json_decode($data, true);

        return ((isset($names[mb_strtoupper($name)]["name"]) === false) ? 'unknown' : $names[mb_strtoupper($name)]["name"]);
    } //end _getName()


}


?>
