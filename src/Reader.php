<?php

namespace TochkaEnv;

/**
 * Class Reader
 * @package TochkaEnv
 */
class Reader
{
    /**
     * @var string
     */
    private $filePath = '';

    /**
     * @var string
     */
    private $fileName = '';

    /**
     * @var array
     */
    private $content = [];

    /**
     * Reader constructor.
     * @param string $filePath
     * @param string $fileName
     * @throws \Exception
     */
    public function __construct(string $filePath, string $fileName = '.env')
    {
        if (!\file_exists($filePath . '/' . $fileName)) {
            throw new \Exception('cant read a file');
        }
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fillContent();
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return self
     */
    public function fillContent(): self
    {
        $fileContent = \file_get_contents($this->filePath . '/' . $this->fileName);
        $fileContent = \explode("\n", $fileContent);
        if (empty($fileContent)) {
            return $this;
        }

        foreach ($fileContent as $item) {
            if (empty($item)) {
                continue;
            }

            list($name, $value) = \explode('=', $item);

            $name = $this->sanitize($name);
            if (empty($name)) {
                continue;
            }
            $value = $this->sanitize($value);
            $value = $this->sanitize($value, '"\'');

            $this->content[$name] = $this->checkForBool($value);
        }
        return $this;
    }

    /**
     * @param mixed $val
     * @return mixed
     */
    private function checkForBool($val)
    {
        switch (\strtolower($val)) {
            case 'false':
                return false;
            case 'true':
                return true;
            default:
                return $val;
        }
    }

    /**
     * @param mixed $val
     * @param string $charList
     * @return string
     */
    private function sanitize($val, $charList = ''): string
    {
        if (empty($charList)) {
            return \trim($val);
        }
        return \trim($val, $charList);
    }
}