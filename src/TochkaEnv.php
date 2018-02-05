<?php

namespace TochkaEnv;

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
     * TochkaEnv constructor.
     * @param string $filePath
     * @param string $fileName
     * @throws \Exception
     */
    public function __construct(string $filePath, string $fileName = '.env')
    {
        $this->preparedVars = (new Loader($filePath, $fileName))->getContent();
    }


    /**
     * @param bool $isNeedToOverride
     * @return TochkaEnv
     */
    public function setIsNeedToOverride(bool $isNeedToOverride): self
    {
        $this->isNeedToOverride = $isNeedToOverride;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function setAll(): bool
    {
        if (empty($this->preparedVars)) {
            throw new \Exception('Nothing todo');
        }
        $isSaved = false;
        foreach ($this->preparedVars as $name => $value) {
            $isSaved = $this->setEnvironment($name, $value, $this->isNeedToOverride)
                && $this->setServer($name, $value, $this->isNeedToOverride)
                && $this->setEnv($name, $value, $this->isNeedToOverride);
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
        if (\getenv($name) && !$needToOverride) {
            return false;
        }
        return putenv("$name=$value");
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
}