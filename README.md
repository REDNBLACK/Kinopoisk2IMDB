Kinopoisk2IMDB
=========

Простая программа импортирует оценки и добавляет в ваш список IMDB фильмы из файла *.xls, экспортированного с Кинопоиска

P.S
------------
По просьбе недовольных Кинопоиском пользователей, скоро будут добавлены новые функции, улучшены существующие и создан отдельный исполняемый .phar файл.
В данном обновлении добавлен режим работы `html`, предзначен специально для импорта российских фильмов, для другого его использовать не рекомендуется.

Требования для корректной работы
------------

* PHP >= 5.4
* CURL и php5-curl
* (Временно) Composer

Установка
------------

Скачать проект, через консоль зайти в его папку, затем запустить:
  
    composer install

Как использовать
----------

#### 1. Экспортируйте фильмы из личного кабинета в Кинопоиске.

- Профиль: ваш никнейм -> Оценки -> экспорт в MS Excel
    
либо
    
- Мой кинопоиск -> Фильмы -> экспорт в MS Excel
    
#### 2. Авторизуйтесь на IMDB и сохраните значение cookie-строки под названием id

- Chrome
 - Откройте меню -> Дополнительные инструменты -> Инструменты разработчика -> Resources -> Cookies -> www.imdb.com
 
- Firefox
 - Настройки -> Приватность -> "удалить отдельные куки" -> imdb.com

#### 3. (Только если хотите добавить фильмы в список) Зайдите в нужный список на IMDB и сохраните его id в адресной строке браузера
    
    
- Выглядит примерно так: ls1234567
    
#### 4. Запустите программу через консоль с нужными параметрами

    cd "папка программы/src"
    
- Вариант 1. Интерактивный режим. После запуска вам будет предложено ввести вашу строку авторизации на IMDB и (можно пропустить) ID списка для добавления.

        php application.php "полный путь к *.xls файлу"

- Вариант 2. Либо можно указать эти параметры напрямую

        php application.php "полный путь к *.xls файлу" --auth="Cookie строка авторизации с IMDB" --list="(Опционально) ID списка для добавления"

- Вариант 3. Указать путь к файлу с конфигурацией

        php application.php "полный путь к *.xls файлу" --config="Путь к файлу *.json с настройками"


 - Содержимое файла с настройками

            {
                "auth": "Cookie строка авторизации с IMDB",
            
                "list": "ID списка для добавления"
            }

 
Доступные опции
----------

Можно указывать как при запуске, так и через файл настроек *.json

- `mode` - Режим работы программы

 - (По умолчанию) `all` - выставить рейтинг фильмам и добавить их в список.
 - `list` - только добавить фильмы в список.
 - `rating` - только выставить рейтинг фильмам.

- `compare` - каким способом сравнивать названия фильмов из таблицы Кинопоиска с найденными в IMDB.

 - (По умолчанию) `smart` - сравнить используя gуникальный алгоритм.
 - `strict` - сравнить, идентичны ли названия.
 - `by_left` - сравнить, находится ли название фильма из таблицы Кинопоиска, в начале названия с IMDB.
 - `is_in_string` - сравнить, находится ли название фильма из таблицы Кинопоиска, в любой части названия с IMDB.
 
- `query_format` - какой тип запроса использовать при поиске фильма в IMDB

 - (По умолчанию) `xml` - Наиболее точный тип, работает с 80% точностью, т.к возвращает оригинальные названия фильмов.
 - `json` - Альтернативный тип - запрос обрабатывается быстрее, но работает с точностью >60%, т.к возвращает лишь локализованные (на английском) названия фильмов.
 - `html` - Еще один тип, самый медленный, малая точность, рекомендуется использовать только для импорта не импортировавшихся с первого раза российских фильмов.
 - `mixed` - Самый медленный, но надежный, процент точности около 90%, совмещает в себе все предыдущие типы, последовательно переключаясь с одного на другой, если фильм не удалось найти.
    Рекомендуется использовать только если не удалось импортировать все фильмы с помощью предыдущих.
