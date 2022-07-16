<?php
require '../vendor/autoload.php';

use App\domain\model\Storage;
use App\domain\repository\StorageRepository;
use App\domain\service\StorageService;

use App\api\model\StorageInputModel;

$url = "http://127.0.0.1:8000/?description[]=~sw~ab&description[]=~ew~ted&lowest_price=~gt~280&opmodeor=true";

$curl = curl_init();

