<?php

namespace Tests\Unit;

use App\Services\StaffAccess;
use CodeIgniter\Test\CIUnitTestCase;

class StaffAccessTest extends CIUnitTestCase
{
    public function testAdministratorCanManageTeamAndStaffCanOnlyViewOrEditSelf(): void
    {
        $this->assertTrue(StaffAccess::canViewOrEdit(true, 1, 2));
        $this->assertTrue(StaffAccess::canCreateOrDelete(true));

        $this->assertTrue(StaffAccess::canViewOrEdit(false, 7, 7));
        $this->assertFalse(StaffAccess::canViewOrEdit(false, 7, 8));
        $this->assertFalse(StaffAccess::canViewOrEdit(false, 0, 0));
        $this->assertFalse(StaffAccess::canCreateOrDelete(false));
    }
}
