<?php

namespace Suphle\Tests\Unit\Routing;

use PHPUnit\Framework\TestCase;
use Suphle\Routing\NamedRouteReader;
use Suphle\Routing\AttributeRouteManager;
use Suphle\Routing\PathPlaceholders;
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Routing\Attributes\HttpMethod;

class NamedRouteReaderTest extends TestCase
{
    private $routeManager;
    private $placeholders;
    private $reader;

    protected function setUp(): void
    {
        $this->routeManager = $this->createMock(AttributeRouteManager::class);
        $this->placeholders = $this->createMock(PathPlaceholders::class);
        
        $this->reader = new NamedRouteReader($this->routeManager, $this->placeholders);
    }

    public function test_can_expand_static_route()
    {
        $route = new RouteInfo('/about-us', HttpMethod::GET, 'Controller', 'method', [], null, 'about.page');
        
        $this->routeManager->method('getAllRoutes')->willReturn([$route]);

        $this->assertEquals('/about-us', $this->reader->expandRoute('about.page'));
    }

    public function test_can_interpolate_arguments_passed_directly()
    {
        $route = new RouteInfo('/users/{id}/edit/{section}', HttpMethod::GET, 'Controller', 'method', [], null, 'users.edit');
        
        $this->routeManager->method('getAllRoutes')->willReturn([$route]);

        $this->assertEquals(
            '/users/5/edit/profile', 
            $this->reader->expandRoute('users.edit', ['id' => 5, 'section' => 'profile'])
        );
    }

    public function test_can_fallback_to_path_placeholders_for_missing_arguments()
    {
        $route = new RouteInfo('/tenant/{tenant_id}/dashboard', HttpMethod::GET, 'Controller', 'method', [], null, 'tenant.dashboard');
        
        $this->routeManager->method('getAllRoutes')->willReturn([$route]);
        
        $this->placeholders->method('getSegmentValue')
            ->with('tenant_id')
            ->willReturn('acme-corp');

        $this->assertEquals(
            '/tenant/acme-corp/dashboard', 
            $this->reader->expandRoute('tenant.dashboard') // Didn't pass tenant_id explicitly
        );
    }

    public function test_throws_exception_if_route_missing()
    {
        $this->routeManager->method('getAllRoutes')->willReturn([]);
        
        $this->expectException(\RuntimeException::class);
        $this->reader->expandRoute('missing.route');
    }

    public function test_throws_exception_if_parameter_missing()
    {
        $route = new RouteInfo('/post/{slug}', HttpMethod::GET, 'Controller', 'method', [], null, 'post.show');
        $this->routeManager->method('getAllRoutes')->willReturn([$route]);
        
        $this->placeholders->method('getSegmentValue')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->reader->expandRoute('post.show'); // Neither passed nor in placeholders
    }
}
