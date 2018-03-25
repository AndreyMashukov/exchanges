<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace Test;

/**
 * Bitfinex API emulator
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */


if (strlen($_GET["symbols"]) < 50)
    {
	echo file_get_contents(__DIR__ . "/data/" . str_replace(",", "", $_GET["symbols"]) . ".json");
    }
else
    {
	echo file_get_contents(__DIR__ . "/data/" . md5(str_replace(",", "", $_GET["symbols"])) . ".json");
    }

?>