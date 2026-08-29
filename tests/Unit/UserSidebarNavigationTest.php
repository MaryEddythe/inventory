<?php

namespace Tests\Unit;

use App\Models\SidebarItem;
use App\Models\User;
use Tests\TestCase;

class UserSidebarNavigationTest extends TestCase
{
    public function test_it_ignores_invalid_route_names_in_sidebar_items(): void
    {
        $user = new User();
        $item = new SidebarItem([
            'key' => 'attendance-holidays',
            'label' => 'Attendance Holidays',
            'route_name' => 'attendance-holidays.index',
            'route_pattern' => 'attendance-holidays.*',
        ]);

        $method = new \ReflectionMethod(User::class, 'mapSidebarItem');
        $method->setAccessible(true);

        $mapped = $method->invoke($user, $item, [999]);

        $this->assertNull($mapped);
    }
}
