<?php

namespace config;


use Dotenv\Dotenv;
use PDO;


class Config
{
    private static $instance;
    private $postgresDb;
    private $postgresUser;
    private $postgresPassword;
    private $postgresHost;
    private $postgresPort;
    private $db;

    private $rootPath;
    private $uploadPath;
    private $uploadUrl = 'http://localhost:8080/uploads/';

    private function __construct()
    {

        // Raíz del proyecto (donde están .env y composer.json): dos niveles por encima de src/config/
        $this->rootPath = dirname(__DIR__, 2) . '/';
        $this->uploadPath = $this->rootPath . 'public/uploads/';

        $dotenv = Dotenv::createImmutable($this->rootPath);
        $dotenv->load();

        // Cargar las variables de entorno y almacenarlas en las propiedades.
        $this->postgresDb = $_ENV['POSTGRES_DB'] ?? 'default_db';
        $this->postgresUser = $_ENV['POSTGRES_USER'] ?? 'default_user';
        $this->postgresPassword = $_ENV['POSTGRES_PASSWORD'] ?? 'default_password';
        $this->postgresHost = $_ENV['POSTGRES_HOST'] ?? 'localhost';
        $this->postgresPort = $_ENV['POSTGRES_PORT'] ?? '5432';
        $this->db = new PDO("pgsql:host={$this->postgresHost};port={$this->postgresPort};dbname={$this->postgresDb}", $this->postgresUser, $this->postgresPassword);
    }

    public static function getInstance(): Config
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }


    // Magic methods for get and set
    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

}
