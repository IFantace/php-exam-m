<?php

namespace exam;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderProcessor
{
    /**
     * biller 參數描述
     *
     * @var BillerInterface
     */
    private $biller;

    /**
     * 建構子
     *
     * @param BillerInterface $biller
     */
    public function __construct(BillerInterface $biller)
    {
        $this->biller = $biller;
    }

    /**
     * process function 描述
     *
     * @param Order $order
     *
     * @return void
     *
     * @throws \Exception 例外描述
     */
    public function process(Order $order)
    {
        $recent = $this->getRecentOrderCount($order);

        if ($recent > 0) {
            throw new \Exception('Duplicate order likely.');
        }

        $this->biller->bill($order->account->id, $order->amount);

        DB::table('orders')->insert(array(
            'account'    => $order->account->id,
            'amount'     => $order->amount,
            'created_at' => Carbon::now()
        ));
    }

    /**
     * getRecentOrderCount function 描述
     *
     * @param Order $order
     *
     * @return int
     */
    private function getRecentOrderCount(Order $order)
    {
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
            ->where('account', $order->account->id)
            ->where('created_at', '>=', $timestamp)
            ->count();
    }
}
