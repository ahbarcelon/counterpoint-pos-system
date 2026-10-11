<?php

namespace Tests\Unit;

use App\Database\Seeds\PcStoreSampleSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

class PcStoreSampleSeederTest extends CIUnitTestCase
{
    public function testShowcaseDefinesTwentyDistinctRecordsPerDimension(): void
    {
        $seeder = new ReflectionClass(PcStoreSampleSeeder::class);
        foreach (['PRODUCTS', 'CUSTOMERS', 'STAFF'] as $name) {
            $rows = $seeder->getConstant($name);
            $this->assertCount(20, $rows);
            $labels = $name === 'PRODUCTS' ? array_column($rows, 0) : $rows;
            $this->assertCount(20, array_unique($labels));
        }
    }
}
