<?php
/**
 * This file is part of Notadd.
 * @author TwilRoad <269044570@qq.com>
 * @copyright (c) 2016, iBenchu.org
 * @datetime 2016-10-14 13:56
 */
namespace Notadd\Member\Listeners;
use Notadd\Foundation\Routing\Abstracts\RouteRegistrar as AbstractRouteRegistrar;
use Notadd\Member\Controllers\MemberController;
/**
 * Class RouteRegistrar
 * @package Notadd\Member\Listeners
 */
class RouteRegistrar extends AbstractRouteRegistrar {
    /**
     * @return void
     */
    public function handle() {
        $this->router->group(['middleware' => 'web', 'prefix' => 'member'], function() {
            $this->router->resource('/', MemberController::class);
        });
    }
}