<?php

namespace AlaTest\Test\Unit;

use AlaTest\Solution;
use PHPUnit\Framework\TestCase;

class SolutionTest extends TestCase
{
    private $ala;

    protected function setUp(): void
    {
        $this->ala = new Solution();
    }

    public function testFilter()
    {
        $reflector = new \ReflectionMethod(Solution::class, 'filter');
        $filter = $reflector->getClosure($this->ala);

        $phone = '+46-73-212345';
        $fphone = call_user_func($filter, $phone);
        $this->assertSame('4673212345', $fphone);

        $phone = '+44';
        $fphone = call_user_func($filter, $phone);
        $this->assertSame('44', $fphone);

    }

    public function testLoadPriceDataFromFile()
    {
        $priceData = $this->ala->loadPriceDataFromFile('./data.txt');
        $this->assertCount(12, $priceData);
        $this->assertCount(2, $priceData[46]);
        $this->assertArrayNotHasKey('47', $priceData);
    }


    public function testFindOutAllItem()
    {
        $this->ala->loadPriceDataFromFile('./data.txt');
        $reflector = new \ReflectionMethod(Solution::class, 'findOutAllItem');
        $method = $reflector->getClosure($this->ala);

        $return = call_user_func($method, '4673212345');
        $this->assertCount(4, $return);
        $this->assertCount(2, $return['46']);
        $this->assertCount(1, $return['46732']);

        $return = call_user_func($method, '4573212345');
        $this->assertCount(1, $return);

    }

    public function testGetOperatorPricesBy()
    {
        $this->ala->loadPriceDataFromFile('./data.txt');
        $reflector = new \ReflectionMethod(Solution::class, 'getOperatorPricesBy');
        $transform = $reflector->getClosure($this->ala);

        $return = call_user_func($transform, '4673212345');
        $this->assertCount(2, $return);

        $return = call_user_func($transform, '4573212345');
        $this->assertCount(1, $return);

        $return = call_user_func($transform, '1603212345');
        $this->assertCount(3, $return);

    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCheapest()
    {
        $this->ala->loadPriceDataFromFile('./data.txt');

        $return = $this->ala->cheapest('4673212345');
        $this->assertEquals('Operator B', $return['operator']);
        $this->assertEquals(467, $return['patten']);
        $this->assertEquals(1.0, $return['price']);

        $return = $this->ala->cheapest('4573212345');
        $this->assertEquals('Operator C', $return['operator']);
        $this->assertEquals(0.5, $return['price']);

        $return = $this->ala->cheapest('2945212345');
        $this->assertEmpty($return);

    }

    public function testCheapestEmpty()
    {
        $this->ala->loadPriceDataFromFile('./data.txt');
        $return = $this->ala->cheapest('4723457887');
        $this->assertEmpty($return);
    }

    public function testOutput()
    {
        $phone = '123456';
        $data = array('patten'=>'aaaa','operator'=>'bbbb','price'=>3);
        $str = $this->ala->output($phone,$data);
        $this->assertContains(
            'Phone number <123456> matches <aaaa> in <bbbb> Operator and the price is $3.00/minute',
            $str
        );
    }


}