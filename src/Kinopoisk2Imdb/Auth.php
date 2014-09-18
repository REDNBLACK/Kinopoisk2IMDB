<?php
namespace Kinopoisk2Imdb;

class Auth
{
    /**
     * @param $login
     * @param $password
     * @param $captcha
     * @return mixed
     */
    public function loginWithImdbAccount($login, $password, $captcha)
    {
        /*        При логине на сайт через аккаунт IMDB */
        //        49e6c:7201                        // Неизвестная хуйта, которая меняется при каждом логине
        //        login:rb@gmail.com                // Email
        //        password:123456                   // Пароль
        //        captcha_answer:Anthony Hopkins    // Ответ на капчу

        $url = 'https://secure.imdb.com/oauth/login?origurl=http://www.imdb.com/&show_imdb_panel=1';
        $post_data = [
            '49e6c'          => 'xxx',     // Неизвестная хуйта, которая меняется при каждом логине
            'login'          => $login,    // Email или ID
            'password'       => $password, // Пароль
            'captcha_answer' => $captcha   // Ответ на капчу
        ];

        return $this->fetchUrlByCurl($url, 'POST', $post_data);
    }
} 
