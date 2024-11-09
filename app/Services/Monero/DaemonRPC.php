<?php

namespace App\Services\Monero;

class DaemonRPC extends BaseRPC
{
    public function getInfo()
    {
        return $this->request('get_info');
    }

    public function getHeight()
    {
        $info = $this->getInfo();
        return $info['height'];
    }
}
