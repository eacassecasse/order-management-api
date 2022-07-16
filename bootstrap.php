<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use App\core\ConnectionFactory;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

