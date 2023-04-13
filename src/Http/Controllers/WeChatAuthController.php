<?php



namespace Hehongyuanlove\WeChatAuth\Http\Controllers;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Illuminate\Support\Str;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use NomisCZ\OAuth2\Client\Provider\WeChat;
use NomisCZ\OAuth2\Client\Provider\WeChatResourceOwner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class WeChatAuthController implements RequestHandlerInterface
{
    /**
     * @var WeChatResponseFactory
     */
    protected $response;
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;
    /**
     * @var UrlGenerator
     */
    protected $url;
    /**
     * @param WeChatResponseFactory $response
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(WeChatResponseFactory $response, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->response = $response;
        $this->settings = $settings;
        $this->url = $url;
    }
    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(Request $request): ResponseInterface
    {
        $redirectUri = $this->url->to('forum')->route('auth.wechat');

        $provider = new WeChat([
            'appid' => $this->settings->get('hehongyuanlove-auth-wechat.app_id'),
            'secret' => $this->settings->get('hehongyuanlove-auth-wechat.app_secret'),
            'redirect_uri' => $redirectUri,
        ]);

        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();
        $code = array_get($queryParams, 'code');

        if (!$code) {

            $authUrl = $provider->getAuthorizationUrl();
            $session->put('oauth2state', $provider->getState());
            return new RedirectResponse($authUrl . '&display=popup');
        }

        $state = array_get($queryParams, 'state');

        if (!$state || $state !== $session->get('oauth2state')) {

            $session->remove('oauth2state');
            throw new Exception('Invalid state');
        }

        $token = $provider->getAccessToken('authorization_code', compact('code'));
        /** @var WeChatResourceOwner $user */
        $user = $provider->getResourceOwner($token);

        return $this->response->make(
            'wechat',
            $user->getId(),
            $request->getAttribute('actor'),
            function (Registration $registration) use ($user) {
                $username = $this->RandomUserName();
                $random_email = $username. "@xxxxx.cn";
                $nickname     = $this->UserNameMatch($user->getNickname()) . str::upper(str::random(4));

                $registration
                    ->provide("username", $username)
                    ->provide("nickname", $nickname)
                    ->provide("email", $random_email)
                    ->provide("is_email_confirmed", 1)
                    ->provide("password", $random_email)
                    ->setPayload($user->toArray());

                if ($user->getHeadImgUrl()) {
                    $registration->provideAvatar($user->getHeadImgUrl());
                }
            }
        );
    }

    public function UserNameMatch($str)
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u', $str, $result);
        return implode('', $result[0]);
    }

    public function RandomUserName()
    {
        $timestamp = date('YmdHis'); // 生成与当前时间相关的字符串
        $random = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz_-', 5)), 0, 10); // 生成长度为 10 的随机字符串
        return $random . $timestamp ; // 将两个字符串拼接起来
    }
}
