<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * These executable checks are intended for a configured MySQL test database.
 * The full CRUD and concurrency matrix remains in docs/TESTING_CHECKLIST.md
 * because it needs a running browser session and two database connections.
 */
class SecurityAndWorkflowTest extends CIUnitTestCase
{
    public function testTestSuiteIsConfiguredForTheTestingEnvironment(): void
    {
        $this->assertSame('testing', ENVIRONMENT);
        $this->assertDirectoryExists(WRITEPATH);
    }
}
