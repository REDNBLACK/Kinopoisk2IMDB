<?php
namespace Kinopoisk2Imdb\Tool;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kinopoisk2Imdb\Filesystem;
use Kinopoisk2Imdb\Parser;
use Kinopoisk2Imdb\Request;
use Kinopoisk2Imdb\Generator;
use Kinopoisk2Imdb\ResourceManager;
use Kinopoisk2Imdb\Config\Config;

class Kinopoisk2Imdb extends Command
{

    /**
     * @var array
     */
    public $errors;

    /**
     * @var string
     */
    public $file;

    /**
     * @var Filesystem
     */
    public $fs;

    /**
     * @var Parser
     */
    public $parser;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Generator
     */
    public $generator;

    /**
     * @var ResourceManager
     */
    public $resourceManager;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->errors = [];

        $this->fs = new Filesystem();

        $this->parser = new Parser();

        set_time_limit(Config::SCRIPT_EXECUTION_LIMIT);
    }

    /**
     *
     */
    public function __destruct()
    {
        $file = $this->fs->setFile($this->file)->getFile();
        $data = array_merge($this->getResourceManager()->getAllRows(), $this->getErrors());

        $this->fs->setData($data)
            ->addSettingsArray(['filesize' => filesize($file)])
            ->encodeJson()
            ->writeToFile()
        ;
    }

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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Устанавливаем настройки файла и запроса
        $this->request = new Request($input->getOption('auth'));
        $this->file = $input->getArgument('file');

        // Проверяем новый ли это файл и устанавливаем менеджер ресурсов
        if ($this->isNewFile($input->getArgument('file'))) {
            $this->generator = new Generator($input->getArgument('file'));
            $this->generator->init();

            $this->setResourceManager($this->generator->newFileName);
        }

        // Всего элементов считаем
        $total_elements = $this->getResourceManager()->countTotalRows();

        if ($total_elements > 0) {
            // Инициализируем прогресс бар и выполняем
            $progress = $this->getHelper('progress');
            $progress->start($output, $total_elements);
            $i = 0;
            while ($i++ < $total_elements) {
                sleep(Config::DELAY_BETWEEN_REQUESTS);

                $this->submit($this->resourceManager->getOneRow(), $input);
                $this->resourceManager->removeOneRow();

                // advances the progress bar 1 unit
                $progress->advance();
            }
            $progress->finish();
        } else {
            $output->write('Файл пустой');
        }
    }

    /**
     * @param $movie_info
     * @param InputInterface $options
     * @return bool
     */
    public function submit(array $movie_info, $options)
    {
        $response = [];
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie(
                $movie_info[Config::MOVIE_TITLE], $movie_info[Config::MOVIE_YEAR], $options->getOption('query_format')
            ),
            $options->getOption('compare'),
            $options->getOption('query_format')
        );

        if ($movie_id !== false) {
            if ($options->getOption('list') !== null
                && ($options->getOption('mode') === Config::MODE_ALL
                || $options->getOption('mode') === Config::MODE_LIST_ONLY)
            ) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $options->getOption('list'));
            }
            if ($options->getOption('mode') === Config::MODE_ALL
                || $options->getOption('mode') === Config::MODE_RATING_ONLY
            ) {
                $movie_auth = $this->parser->parseMovieAuthString(
                    $this->request->openMoviePage($movie_id)
                );

                $response[] = $this->request->changeMovieRating(
                    $movie_id, $movie_info[Config::MOVIE_RATING], $movie_auth
                );
            }

            $validated_response = $this->validateResponse($response);
            if ($validated_response !== true) {
                $this->setErrors($movie_info, ['network_problem' => $validated_response]);

                return false;
            }

            return true;
        } else {
            $this->setErrors($movie_info, ['title_not_found' => 1]);

            return false;
        }
    }

    /**
     * @param $file
     * @return bool
     */
    public function isNewFile($file)
    {
        $old_file_name = $this->fs->setFile($file)->getFile();

        $path_parts = pathinfo($old_file_name);
        $new_file_name = $path_parts['filename'] . Config::DEFAULT_NEW_FILE_EXT;
        $this->fs->setFile($new_file_name);

        if ($this->fs->isFileExists()) {
            $this->setResourceManager($new_file_name);

            $new_file_size = $this->getResourceManager()->getSettings('filesize');
            if (is_int($new_file_size) && $new_file_size !== filesize($old_file_name)) {
                unset($this->resourceManager);
                return true;
            }

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $response
     * @return array
     */
    public function validateResponse(array $response)
    {
        if (empty($response)) {
            return 'empty';
        }

        foreach ($response as $v) {
            $json = $this->fs->setData($v)->decodeJson()->getData();
            if ($json['status'] != 200) {
                return $json['status'];
            }
        }

        return true;
    }

    /**
     * @param $file
     */
    public function setResourceManager($file)
    {
        $this->resourceManager = new ResourceManager($file);
        $this->resourceManager->init();
    }

    /**
     * @return ResourceManager
     */
    public function getResourceManager()
    {
        return $this->resourceManager;
    }

    /**
     * @param array $data
     * @param array $error
     */
    public function setErrors(array $data, array $error)
    {
        $this->errors[] = array_merge($data, ['errors' => $error]);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
