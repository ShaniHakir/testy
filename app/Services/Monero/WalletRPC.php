<?php

namespace App\Services\Monero;

class WalletRPC extends BaseRPC
{
    public function createWallet(array $params)
    {
        return $this->request('create_wallet', $params);
    }

    public function openWallet(array $params)
    {
        return $this->request('open_wallet', $params);
    }

    public function getAddress()
    {
        return $this->request('get_address', ['account_index' => 0]);
    }

    public function createAddress(array $params = [])
    {
        // For subaddresses, we need to use create_address
        return $this->request('create_address', [
            'account_index' => 0,
            'label' => $params['label'] ?? ''
        ]);
    }

    public function queryKey(array $params)
    {
        return $this->request('query_key', $params);
    }

    public function getBalance()
    {
        return $this->request('get_balance', ['account_index' => 0]);
    }

    public function transfer(array $params)
    {
        $params['account_index'] = 0;
        return $this->request('transfer', $params);
    }

    public function getTransfers(array $params)
    {
        // Ensure we're getting both pool and regular transactions
        $params = array_merge([
            'account_index' => 0,
            'in' => true,
            'pool' => true,
            'pending' => true,
            'failed' => false,
            'filter_by_height' => false
        ], $params);

        return $this->request('get_transfers', $params);
    }

    public function getTransferByTxid(array $params)
    {
        $params['account_index'] = 0;
        return $this->request('get_transfer_by_txid', $params);
    }

    public function getAddressIndex(array $params)
    {
        return $this->request('get_address_index', $params);
    }
}
