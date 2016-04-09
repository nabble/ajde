<?php

class Ajde_Config
{
    /**
     * @var Ajde_Config_Repository
     */
    private $repository;

    /**
     * TODO
     */
    public function __construct()
    {
        $this->repository = new Ajde_Config_Repository(CONFIG_DIR);

        if ($this->repository->get("security.secret") === 'RANDOMSTRING') {
            Ajde_Dump::warn('Using unsafe secret: your app is insecure. See class Config_Application');
        }
    }

    /**
     * TODO
     *
     * @return Config
     */
    public static function getInstance()
    {
        static $instance;

        return $instance === null ? $instance = new self : $instance;
    }

    /**
     * TODO
     *
     * @param string $param
     * @return mixed
     * @throws Ajde_Exception
     */
    public static function get($param)
    {
        $instance = self::getInstance();

        return $instance->repository->get($param);
    }

    /**
     * TODO
     *
     * @param string $param
     * @param mixed  $value
     */
    public static function set($param, $value)
    {
        $instance = self::getInstance();

        $instance->repository->set($param, $value);
    }
}
