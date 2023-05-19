<?php

namespace App\Service;

use Qbhy\BaiduAIP\BaiduAIP;

class TextRecognitionService
{
    protected $client;

    public function __construct()
    {
        $this->client = new BaiduAIP();
    }

    public function recognize($filepath)
    {
        $image = file_get_contents($filepath);
        $result = $this->client->basicGeneral($image);
        return $result;
    }
}