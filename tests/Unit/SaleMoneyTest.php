<?php

namespace Tests\Unit;

use App\Services\SaleService;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

class SaleMoneyTest extends CIUnitTestCase
{
    public function testDecimalPriceIsConvertedToCentsWithoutFloatingPointDrift(): void
    {
        $service = new SaleService(db_connect());
        $toCents = new ReflectionMethod($service, 'toCents');
        $toCents->setAccessible(true);
        $fromCents = new ReflectionMethod($service, 'fromCents');
        $fromCents->setAccessible(true);

        $this->assertSame(999, $toCents->invoke($service, '9.99'));
        $this->assertSame('29.97', $fromCents->invoke($service, 999 * 3));
    }
}
