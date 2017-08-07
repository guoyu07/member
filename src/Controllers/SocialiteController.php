<?php
/**
 * This file is part of Notadd.
 *
 * @author TwilRoad <269044570@qq.com>
 * @copyright (c) 2017, notadd.com
 * @datetime 2017-08-06 21:07
 */
namespace Notadd\Member\Controllers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Str;
use Notadd\Foundation\Routing\Abstracts\Controller;
use Notadd\Member\Handlers\Socialite\AuthHandler;

/**
 * Class SocialiteController.
 */
class SocialiteController extends Controller
{
    /**
     * @param $driver
     *
     * @return \Notadd\Foundation\Passport\Responses\ApiResponse|\Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\RedirectResponse|\Zend\Diactoros\Response
     */
    public function auth($driver)
    {
        if ($this->request->expectsJson()) {
            return $this->container->make(AuthHandler::class)->withDriver($driver)->toResponse()->generateHttpResponse();
        } else {
            $socialite = $this->container->make('socialite')->with($driver);
            $socialite->withState(md5(Str::random(10) . time() . Str::random(10)));
            $socialite->withRedirectUrl($this->container->make(UrlGenerator::class)->to("socialite/{$driver}/token"));

            return $socialite->redirect();
        }
    }

    public function token($driver)
    {

    }
}
