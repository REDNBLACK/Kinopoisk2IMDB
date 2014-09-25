<?php
namespace Kinopoisk2Imdb\Tool;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
//use Symfony\Component\Console\Question\ChoiceQuestion;
use Kinopoisk2Imdb\Config\Config;
use Kinopoisk2Imdb\Client;

/**
 * Class Kinopoisk2Imdb
 * @package Kinopoisk2Imdb\Console
 */
class Kinopoisk2Imdb extends Command
{
    /**
     * @var Client
     */
    private $client;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('k2i:start')
            ->setDescription('Run Kinopoisk2Imdb')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Path to exported Kinopoisk xls file'
            )
            ->addOption(
                'auth',
                null,
                InputOption::VALUE_REQUIRED,
                'Your IMDB auth string'
            )
            ->addOption(
                'list',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of the list in which movies will be added'
            )
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Mode of working.'
                    . ' By Default "all" - will rate movies and add them to watchlist,'
                    . ' "list" - will just add movies to watchlist,'
                    . ' "rating" - will just rate movies',
                'all'
            )
            ->addOption(
                'compare',
                null,
                InputOption::VALUE_OPTIONAL,
                'How to detect that found movie titles are the same.'
                    . 'By Default "smart" - will compare titles with unique algorythms,'
                    . ' "strict" - will compare if titles are identifical'
                    . ' "by left" - will compare if title from the table is inside found title starting from the left'
                    . ' "is_in_string" - will compare if title from the table is inside found title anywhere in string',
                'smart'
            )
            ->addOption(
                'query_format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which query format to use while parsing IMDB.'
                    . 'By Default "xml" - it is correct in 90% cases,'
                    . ' "json" - not so good, correct in just 70% cases',
                'xml'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Проверяем auth
        $this->authPrompt($input, $output);

        // Проверяем list
        $this->listPrompt($input, $output);

        // Устанавливаем настройки файла и запроса
        $this->client = new Client();
        $this->client->init($input->getOption('auth'), $input->getArgument('file'));

        // Всего элементов считаем
        $total_elements = $this->client->getResourceManager()->countTotalRows();

        if ($total_elements > 0) {
            // Инициализируем прогресс бар и выполняем
            $progress = $this->getHelper('progress');
            $progress->start($output, $total_elements);
            $i = 0;

            while ($i++ < $total_elements) {
                sleep(Config::DELAY_BETWEEN_REQUESTS);

                $options = [
                    'mode' => $input->getOption('mode'),
                    'list' => $input->getOption('list'),
                    'compare' => $input->getOption('compare'),
                    'query_format' => $input->getOption('query_format')
                ];

                $this->client->submit($this->client->getResourceManager()->getOneRow(), $options);
                $this->client->getResourceManager()->removeOneRow();

                // advances the progress bar 1 unit
                $progress->advance();
            }

            $progress->finish();

            // Отображаем ошибки если есть
            $this->displayErrorTable($this->client->getErrors(), $output);
        } else {
            $output->writeln('Файл пустой');
        }
    }

    /**
     * @param $input
     * @param $output
     * @return bool
     */
    public function authPrompt($input, $output)
    {
        // Пустой auth недопустим
        if (!$input->getOption('auth')) {
            // Устанавливаем helper
            $helper = $this->getHelper('question');

            $question = new Question('Вы не указали вашу строку авторизации, пожайлуста введите ее.');
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Строка автоизации не можеть быть пустой');
                }

                return $value;
            });
            $question->setMaxAttempts(5);

            $input->setOption('auth', $helper->ask($input, $output, $question));
        }

        return false;
    }

    /**
     * @param $input
     * @param $output
     */
    public function listPrompt($input, $output)
    {
        // Если режим включает в себя импорт списка и список не указан
        if (!$input->getOption('list') && ($input->getOption('mode') === 'all' || $input->getOption('mode') === 'list')) {
            // Устанавливаем helper
            $helper = $this->getHelper('question');

            $question = new Question('Вы не указали ID вашего IMDB списка, вы можете указать его или пропустить.', 'null');
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('ID списка не может быть пустым');
                }

                return $value;
            });
            $question->setMaxAttempts(2);

            $input->setOption('list', $helper->ask($input, $output, $question));
            $output->writeln('Вы не указали ID вашего IMDB списка, будут импортированы только оценки.');
        }
    }

    /**
     * @param $error
     * @param $output
     */
    public function displayErrorTable($error, $output)
    {
        if (!empty($error)) {
            $output->writeln('При обработке произошли ошибки со следующими фильмами:');
            foreach ($error as &$v) {
                $v['errors'] = implode(',', $v['errors']);
            }
            unset($v);

            $table = $this->getHelper('table');
            $table->setHeaders(['Название', 'Год', 'Рейтинг', 'Ошибки'])->setRows($error);
            $table->render($output);
        } else {
            $output->writeln('Все фильмы были успешно обработаны. Ошибок не обнаружено.');
        }
    }
}
