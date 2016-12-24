<?php

namespace Phormium\Tests\Integration;

use Phormium\Orm;
use Phormium\Query\Aggregate;
use Phormium\Tests\Models\Trade;

/**
 * @group integration
 * @group aggregate
 */
class AggregateTest extends DbTest
{
    public function testAggregates()
    {
        $tradedate = date('Y-m-d');
        $count = 10;

        $qs = Trade::objects()->filter('tradedate', '=', $tradedate);

        // Delete any existing trades for today
        $qs->delete();

        // Create trades with random prices and quantitities
        $prices = [];
        $quantities = [];

        foreach(range(1, $count) as $tradeno) {
            $price = rand(100, 100000) / 100;;
            $quantity = rand(1, 10000);

            $t = new Trade();
            $t->merge(compact('tradedate', 'tradeno', 'price', 'quantity'));
            $t->insert();

            $prices[] = $price;
            $quantities[] = $quantity;
        }

        // Calculate expected values
        $avgPrice = array_sum($prices) / count($prices);
        $maxPrice = max($prices);
        $minPrice = min($prices);
        $sumPrice = array_sum($prices);

        $avgQuantity = array_sum($quantities) / count($quantities);
        $maxQuantity = max($quantities);
        $minQuantity = min($quantities);
        $sumQuantity = array_sum($quantities);

        $this->assertSame($count, $qs->count());

        $this->assertEquals($avgPrice, $qs->avg('price'));
        $this->assertEquals($minPrice, $qs->min('price'));
        $this->assertEquals($avgPrice, $qs->avg('price'));
        $this->assertEquals($sumPrice, $qs->sum('price'));

        $this->assertEquals($avgQuantity, $qs->avg('quantity'));
        $this->assertEquals($minQuantity, $qs->min('quantity'));
        $this->assertEquals($avgQuantity, $qs->avg('quantity'));
        $this->assertEquals($sumQuantity, $qs->sum('quantity'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error forming aggregate query. Column [xxx] does not exist in table [trade].
     */
    public function testInvalidColumn()
    {
        Trade::objects()->avg('xxx');
    }
}
