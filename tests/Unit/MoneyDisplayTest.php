<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class MoneyDisplayTest extends CIUnitTestCase
{
    public function testStoredDecimalIsFormattedWithoutFloatConversion(): void
    {
        $this->assertSame('1,234,567.90', pos_money('1234567.90'));
        $this->assertSame('0.05', pos_money('0.05'));
        $this->assertSame('100.00', pos_money('100'));
    }
}
