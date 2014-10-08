<?php
namespace Kinopoisk2Imdb\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use Kinopoisk2Imdb\Config\Config;
use Kinopoisk2Imdb\Client;

/**
 * Class RunKinopoisk2Imdb
 * @package Kinopoisk2Imdb\Console
 */
class RunKinopoisk2Imdb extends Command
{
    /**
     * @var Client Container
     */
    private $client;

    /**
     * Initial setup
     */
    protected function configure()
    {
        $this
            ->setName('k2i:start')
            ->setDescription('Запустить Kinopoisk2IMDB')
            ->setHelp('Im help, im helping')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Путь к файлу xls экспортированному с Кинопоиска, который нужно импортировать на IMDB'
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Путь к json файлу с настройками (перезапишет все настройки указанные через CLI !!!)'
            )
            ->addOption(
                'auth',
                null,
                InputOption::VALUE_REQUIRED,
                'Ваша строка авторизации с IMDB'
            )
            ->addOption(
                'list',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID списка IMDB, в который будут добавлены фильмы'
            )
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Режим работы.'
                    . ' "all" - выставить рейтинг фильмам и добавить их в список,'
                    . ' "list" - только добавить фильмы в список,'
                    . ' "rating" - только выставить рейтинг фильмам',
                'all'
            )
            ->addOption(
                'compare',
                null,
                InputOption::VALUE_OPTIONAL,
                'Каким образом сравнивать названия фильмов из таблицы Кинопоиска, с найденными в IMDB.'
                    . ' "smart" - сравнить используя уникальный алгоритм,'
                    . ' "strict" - сравнить идентичны ли названия,'
                    . ' "by left" - сравнить находится ли название фильма из таблицы Кинопоиска, в начале названия с IMDB,'
                    . ' "is_in_string" - сравнить находится ли название фильма из таблицы Кинопоиска, в любой части названия с IMDB',
                'smart'
            )
            ->addOption(
                'query_format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Какой тип запроса использовать при поиске фильма в IMDB.'
                    . ' "xml" - Наилучший тип, работает с 90% точностью, т.к возвращает оригинальные названия фильмов,'
                    . ' "json" - Альтернативный тип, запрос обрабатывается быстрее, но работает с точностью >60%, т.к возвращает лишь локализованные (на английском) названия фильмов.',
                'xml'
            );
    }

    /**
     * Execute command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Считываем настройки из файла, если присутствует
        $this->readConfig($input);

        // Проверяем auth
        $this->authPrompt($input, $output);

        // Проверяем list
        $this->listPrompt($input, $output);

        // Устанавливаем настройки файла и запроса
        $this->client = new Client();
        $this->client->init(
            $input->getOption('auth'),
            [
                'mode' => $input->getOption('mode'),
                'list' => $input->getOption('list'),
                'compare' => $input->getOption('compare'),
                'query_format' => $input->getOption('query_format')
            ],
            $input->getArgument('file')
        );

        // Всего элементов считаем
        $total_elements = $this->client->getResourceManager()->arrays('count');

        // Считываем статус файла
        $status = $this->client->getResourceManager()->getSettings('status');

        // Выводим информацию о файле и спрашиваем пользователя о следующем действии
        $this->fileInfo($status, $total_elements, $input, $output);

        // Выполняем основной цикл с выводом в виде прогрессбара
        $this->mainProgressBarAction($total_elements, $output, function () {
            $this->client->submit($this->client->getResourceManager()->arrays('getLast'));
            $this->client->getResourceManager()->arrays('removeLast');
        });

        // Отображаем результаты обработки
        $this->displayResult($this->client->getErrors(), $output);
    }

    /**
     * @param $total
     * @param OutputInterface $output
     * @param callable $callback
     */
    public function mainProgressBarAction($total, $output, callable $callback)
    {
        // Перенос строки
        $output->writeln("\n");

        // Инициализируем прогресс бар
        $progress = new ProgressBar($output, $total);
        $progress->setFormat("<info>%message%\n Фильм %current% из %max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%</info>");
        $progress->setMessage('Процесс запущен');
        $progress->start();

        // Инициализируем цикл и выполняем
        $progress->setMessage('В процессе...');
        for ($i = 0; $i < $total; $i++) {
            // Задержка
            sleep(Config::DELAY_BETWEEN_REQUESTS);

            // Выполняем колбэк
            $callback();

            // Передвигаем прогресс бар
            $progress->advance();
        }

        // Завершаем прогресс бар
        $progress->setMessage('Процесс завершен');
        $progress->finish();

        // Перенос строки
        $output->writeln("\n");
    }

    /**
     * Prompt for user auth string
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function authPrompt($input, $output)
    {
        // Пустой auth недопустим
        if (!$input->getOption('auth')) {
            // Устанавливаем helper
            $helper = $this->getHelper('question');

            $question = new Question('<comment>Вы не указали вашу строку авторизации, пожайлуста введите ее.</comment>');
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Строка авторизации не может быть пустой');
                }

                return $value;
            });
            $question->setMaxAttempts(5);

            try {
                $input->setOption('auth', $helper->ask($input, $output, $question));
            } catch (\Exception $e) {
                $output->writeln('<bg=magenta;fg=white;options=underscore>Вы не указали строку авторизация, работа программы остановлена.</bg=magenta;fg=white;options=underscore>');
                exit(-1);
            }
        }
    }

    /**
     * Prompt for user watchlist
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function listPrompt($input, $output)
    {
        // Если режим включает в себя импорт списка и список не указан
        if (!$input->getOption('list') && ($input->getOption('mode') === 'all' || $input->getOption('mode') === 'list')) {

            // Устанавливаем helper
            $helper = $this->getHelper('question');

            $question = new Question('<comment>Вы не указали ID вашего IMDB списка, вы можете указать его или пропустить.</comment>');
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('ID списка не может быть пустым');
                }

                return $value;
            });
            $question->setMaxAttempts(3);

            try {
                $input->setOption('list', $helper->ask($input, $output, $question));
            } catch (\Exception $e) {
                $input->setOption('mode', 'rating');
                $output->writeln('<bg=yellow;fg=black;options=bold>Вы не указали ID вашего IMDB списка, будут импортированы только оценки.</bg=yellow;fg=black;options=bold>');
            }
        }
    }

    /* TODO. Добавить функцию обработки файла заново */
    /**
     * Info about file
     * @param string $status
     * @param int $total
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function fileInfo($status, $total, $input, $output)
    {
        if ($status === 'untouched') {
            $output->writeln('<info>Это новый файл. Схема данных была успешно сгенерирована.</info>');
            $question = '<info>Запустить обработку?</info>';
        } elseif ($status === 'completed') {
            $output->writeln('<info>Файл уже был полностью обработан.</info>');
            $question = '<info>Вы хотите снова обработать данный файл?</info>';
        } elseif ($status === 'broken') {
            $output->writeln('<info>Схему к этому файлу не удалось сгенерировать.</info>');
            $question = '<info>Вы хотите попробовать снова сгенерировать схему?</info>';
        } else {
            $output->writeln('<info>Файл уже был в обработке. Загружена существующая схема.</info>');
            $question = '<info>Продолжить обработку?</info>';
        }

        $output->writeln("<info>Всего {$total} фильмов в файле.</info>");

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($question, false);
        if (!$helper->ask($input, $output, $question)) {
            exit(-1);
        }
    }

    /**
     * Result info
     * @param array $error
     * @param OutputInterface $output
     */
    public function displayResult(array $error, $output)
    {
        if (!empty($error)) {
            $output->writeln('<error>При обработке произошли ошибки со следующими фильмами:</error>');
            foreach ($error as &$v) {
                $v['errors'] = implode(',', $v['errors']);
            }
            unset($v);

            $table = $this->getHelper('table');
            $table->setHeaders(['Название', 'Год', 'Рейтинг', 'Ошибка'])->setRows($error);
            $table->render($output);
        } else {
            $output->writeln('<info>Все фильмы были успешно обработаны. Ошибок не обнаружено.</info>');
        }
    }

    /**
     * Try to read the JSON data from specified file in the config option and set options on success
     * @param InputInterface $input
     * @throws \Exception
     */
    public function readConfig($input)
    {
        $file = $input->getOption('config');

        if ($file) {
            if (file_exists($file)) {
                $data = file_get_contents($file);
                if ($data) {
                    $data = json_decode($data, true);
                    if ($data !== null) {
                        foreach ($data as $k => $v) {
                            if (!empty($v)) {
                                $input->setOption($k, $v);
                            }
                        }
                    } else {
                        throw new \Exception('Строка JSON в файле настроек имеет неверный формат');
                    }
                } else {
                    throw new \Exception('Не удалось прочитать файл настроек');
                }
            } else {
                throw new \Exception('Файл настроек не существует в указанном пути');
            }
        }
    }
}
