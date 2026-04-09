<?php

use PHPUnit\Framework\TestCase;
use Model\User;
use Src\Auth\Auth;

class SiteTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www';

        $GLOBALS['app'] = new Src\Application(new Src\Settings([
            'app' => include $_SERVER['DOCUMENT_ROOT'] . '/pop-it-mvc/config/app.php',
            'db' => include $_SERVER['DOCUMENT_ROOT'] . '/pop-it-mvc/config/db.php',
            'path' => include $_SERVER['DOCUMENT_ROOT'] . '/pop-it-mvc/config/path.php',
        ]));

        if (!function_exists('app')) {
            function app() {
                return $GLOBALS['app'];
            }
        }
    }

    /**
     * @dataProvider additionProvider
     * @runInSeparateProcess
     */
    public function testSignup(string $httpMethod, array $userData, string $message): void
    {
        if ($userData['login'] === 'login is busy') {
            $user = User::get()->first();
            if ($user) {
                $userData['login'] = $user->login;
            }
        }

        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())->method('all')->willReturn($userData);
        $request->method = $httpMethod;

        $result = (new \Controller\Site())->signup($request);

        if (!empty($result)) {
            $message = '/' . preg_quote($message, '/') . '/';
            $this->expectOutputRegex($message);
            return;
        }

        $user = User::where('login', $userData['login'])->first();
        $this->assertTrue((bool)$user);

        if ($user) {
            $user->delete();
        }

        $this->assertContains($message, xdebug_get_headers());
    }

    public function additionProvider(): array
    {
        return [
            [
                'GET',
                ['name' => '', 'login' => '', 'password' => ''],
                '<h3></h3>'
            ],
            [
                'POST',
                ['name' => '', 'login' => '', 'password' => ''],
                '<h3>{"name":["Поле name пусто"],"login":["Поле login пусто"],"password":["Поле password пусто"]}</h3>'
            ],
            [
                'POST',
                ['name' => 'admin', 'login' => 'login is busy', 'password' => 'admin'],
                '<h3>{"login":["Поле login должно быть уникально"]}</h3>'
            ],
            [
                'POST',
                ['name' => 'admin', 'login' => md5(time()), 'password' => 'admin'],
                'Location: /pop-it-mvc/login'
            ],
        ];
    }

    /**
     * @dataProvider loginProvider
     * @runInSeparateProcess
     */
    public function testLogin(string $httpMethod, array $credentials, string $expected): void
    {
        $request = $this->createMock(\Src\Request::class);
        $request->expects($this->any())->method('all')->willReturn($credentials);
        $request->method = $httpMethod;

        $result = (new \Controller\Site())->login($request);

        if (!empty($result)) {
            $expected = '/' . preg_quote($expected, '/') . '/';
            $this->expectOutputRegex($expected);
            return;
        }

        $this->assertContains($expected, xdebug_get_headers());
    }

    public function loginProvider(): array
    {
        return [
            [
                'GET',
                ['login' => '', 'password' => ''],
                '<h3></h3>'
            ],
            [
                'POST',
                ['login' => '', 'password' => ''],
                '<h3>Неправильные логин или пароль</h3>'
            ],
            [
                'POST',
                ['login' => 'wrong_user', 'password' => 'wrong_pass'],
                '<h3>Неправильные логин или пароль</h3>'
            ],
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout(): void
    {
        Auth::logout();
        $this->assertFalse(Auth::check());
    }
}