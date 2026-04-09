<?php
namespace Controller;

use Model\Post;
use Model\User;
use Src\View;
use Src\Request;
use Src\Validator\Validator;  // ДОБАВИТЬ ЭТУ СТРОКУ

class Site
{
    public function index(Request $request): string
    {
        $posts = Post::all();
        return (new View())->render('site.post', ['posts' => $posts]);
    }

    public function hello(Request $request): string
    {
        return new View('site.hello', ['message' => 'hello working']);
    }

    // ИЗМЕНЕННЫЙ МЕТОД signup С ВАЛИДАЦИЕЙ
    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {

            // СОЗДАЕМ ВАЛИДАТОР С ПРАВИЛАМИ
            $validator = new Validator($request->all(), [
                'name' => ['required'],
                'login' => ['required', 'unique:users,login'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            // ЕСЛИ ВАЛИДАЦИЯ НЕ ПРОШЛА - ПОКАЗЫВАЕМ ОШИБКИ
            if ($validator->fails()) {
                return new View('site.signup', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)
                ]);
            }

            // ЕСЛИ ВАЛИДАЦИЯ ПРОШЛА - СОЗДАЕМ ПОЛЬЗОВАТЕЛЯ
            if (User::create($request->all())) {
                app()->route->redirect('/login');
                return '';
            }
        }

        return new View('site.signup');
    }
}