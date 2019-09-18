<?php

namespace TochkaEnv;

use Exception;
use RuntimeException;
use function define;
use function defined;
use function getenv;
use function putenv;

/**
 * Class TochkaEnv
 */
class TochkaEnv
{
    /**
     * @var array
     */
    private $preparedVars = [];

    /**
     * @var bool
     */
    private $isNeedToOverride = false;

    /**
     * @param string $filePath
     * @param string $fileName
     * @return TochkaEnv
     * @throws Exception
     */
    public static function create(string $filePath, string $fileName = '.env'): self
    {
        return new self($filePath, $fileName);
    }

    /**
     * TochkaEnv constructor.
     * @param string $filePath
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(string $filePath, string $fileName = '.env')
    {
        if (!isset($filePath)) {
            throw new RuntimeException('Filepath must be specified');
        }

        $this->preparedVars = (new Loader($filePath, $fileName))->getContent();
    }


    /**
     * @param bool $isNeedToOverride
     *
     * @return TochkaEnv
     */
    public function setIsNeedToOverride(bool $isNeedToOverride): self
    {
        $this->isNeedToOverride = $isNeedToOverride;

        return $this;
    }

    /**
     * @return bool
     */
    public function toAll(): bool
    {
        if (empty($this->preparedVars)) {
            return false;
        }
        $isSaved = false;
        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->isSaved($name, $value);
        }

        return $isSaved;
    }

    /**
     * @return bool
     */
    public function toEnvironment(): bool
    {
        if (empty($this->preparedVars)) {
            return false;
        }
        $isSaved = false;

        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->setEnvironment($name, $value, $this->isNeedToOverride);
        }

        return $isSaved;
    }

    /**
     * @return bool
     */
    public function toServer(): bool
    {
        if (empty($this->preparedVars)) {
            return false;
        }
        $isSaved = false;

        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->setServer($name, $value, $this->isNeedToOverride);
        }
        return $isSaved;
    }

    /**
     * @return bool
     */
    public function toEnv(): bool
    {
        if (empty($this->preparedVars)) {
            return false;
        }
        $isSaved = false;

        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->setEnv($name, $value, $this->isNeedToOverride);
        }
        return $isSaved;
    }

    /**
     * @return bool
     */
    public function toConst(): bool
    {
        if (empty($this->preparedVars)) {
            return false;
        }
        $isSaved = false;

        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->setConst($name, $value);
        }
        return $isSaved;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param bool $needToOverride
     * @return bool
     */
    private function setEnvironment(string $name, $value, bool $needToOverride = false): bool
    {
        if (getenv($name) && !$needToOverride) {
            return false;
        }
        return putenv($name . '=' . $value);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param bool $needToOverride
     * @return bool
     */
    private function setEnv(string $name, $value, bool $needToOverride = false): bool
    {
        if (isset($_ENV[$name]) && !$needToOverride) {
            return false;
        }
        unset($_ENV[$name]);
        $_ENV[$name] = $value;
        return isset($_ENV[$name]);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param bool $needToOverride
     * @return bool]
     */
    private function setServer(string $name, $value, bool $needToOverride = false): bool
    {
        if (isset($_SERVER[$name]) && !$needToOverride) {
            return false;
        }
        unset($_SERVER[$name]);
        $_SERVER[$name] = $value;
        return isset($_SERVER[$name]);
    }

    /**
     * @param string $name
     * @param $value
     * @return bool
     */
    private function setConst(string $name, $value): bool
    {
        if (defined($name)) {
            return false;
        }
        return define($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    private function isSaved($name, $value): bool
    {
        return $this->setEnvironment($name, $value, $this->isNeedToOverride)
            && $this->setServer($name, $value, $this->isNeedToOverride)
            && $this->setEnv($name, $value, $this->isNeedToOverride)
            && $this->setConst($name, $value);
    }
}
