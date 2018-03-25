<?php

/**
 * PHP version 7.1
 *
 * @package AM\Exchanges
 */

namespace Test;

/**
 * Kraken API emulator
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 */

echo file_get_contents(__DIR__ . "/data/" . str_replace(",", "", $_GET["pair"]) . ".json");


?>