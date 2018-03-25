<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace Test;

/**
 * Binance API emulator
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

if (isset($_GET["symbol"]) === true)
    {
	echo file_get_contents(__DIR__ . "/data/" . $_GET["symbol"] . ".json");
    }
else
    {
	echo file_get_contents(__DIR__ . "/data/ALL.json");
    }


?>