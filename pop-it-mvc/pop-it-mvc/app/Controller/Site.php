<?php
namespace Controller;

use Model\Post;
use Model\User;
use Src\View;
use Src\Request;
use Src\Validator\Validator;
use function Collect\collection;

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

    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {

            $data = collection($request->all())->toArray();

            $validator = new Validator($data, [
                'name' => ['required'],
                'login' => ['required', 'unique:users,login'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if ($validator->fails()) {
                return new View('site.signup', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)
                ]);
            }

            if (User::create($data)) {
                app()->route->redirect('/login');
                // ДОБАВИТЬ ЭТУ СТРОКУ - возврат пустой строки
                return '';
            }
        }

        return new View('site.signup');
    }
}