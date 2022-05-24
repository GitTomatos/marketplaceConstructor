<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

final class EngineConfigs
{

    /**
     * Путь до директории с шаблонами
     */
    private string $rootDir;
    private string $logDir;
    private string $logFileName;
    private string $downloadedTemplatesDir;
    private string $moduleSrcDir;
    private string $webpackConfigFileFullPath;
    private string $webpackAssetsDir;
    private string $configDir;

    /**
     * //     * @param string $templatesConfigFilePath - Путь до директории с настройками шаблонов
     *
     * @throws \Exception
     */
    public function __construct(
        string $rootDir,
        string $logDir,
        string $logFileName,
        string $downloadedTemplatesDir,
        string $moduleSrcDir,
        string $webpackConfigFileFullPath,
        string $webpackAssetsDir,
        string $configDir,
    ) {
        $this->setRootDir($rootDir)
            ->setLogDir($logDir)
            ->setLogFileName($logFileName)
            ->setDownloadedTemplatesDir($downloadedTemplatesDir)
            ->setModuleSrcDir($moduleSrcDir)
            ->setWebpackConfigFileFullPath($webpackConfigFileFullPath)
            ->setWebpackAssetsDir($webpackAssetsDir)
            ->setConfigDir($configDir);
    }


    /**
     * Создание объекта из массива
     *
     * @param array $arrayConfigs - настройки движка
     *
     * @throws \Exception
     */
    public static function createFromArray(array $arrayConfigs): self
    {
        self::validateArrayParams($arrayConfigs);

        $logDir = $arrayConfigs['logDir'];
        $logFileName = $arrayConfigs['logFileName'];
        $rootDir = $arrayConfigs['rootDir'];
        $downloadedTemplatesDir = $arrayConfigs['downloadedTemplatesDir'];
        $moduleSrcDir = $arrayConfigs['moduleSrcDir'];
        $webpackAssetsDir = $arrayConfigs['webpackAssetsDir'];
        $configDir = $arrayConfigs['configDir'];
        $webpackConfigFileFullPath = $arrayConfigs['webpackConfigFileFullPath'];

        return new self(
            rootDir: $rootDir,
            logDir: $logDir,
            logFileName: $logFileName,
            downloadedTemplatesDir: $downloadedTemplatesDir,
            moduleSrcDir: $moduleSrcDir,
            webpackConfigFileFullPath: $webpackConfigFileFullPath,
            webpackAssetsDir: $webpackAssetsDir,
            configDir: $configDir,
        );
    }

    /**
     * Проверка того, что указаны обязательны параметры движка
     *
     * @param array $arrayConfigs - настройки движка
     *
     * @throws \Exception
     */
    private static function validateArrayParams(array $arrayConfigs): void
    {
        if (!array_key_exists('logDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'logDir'"
            );
        }

        if (!array_key_exists('logFileName', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'logFileName'"
            );
        }

        if (!array_key_exists('rootDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'rootDir'"
            );
        }

        if (!array_key_exists('downloadedTemplatesDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'downloadedTemplatesDir'"
            );
        }

        if (!array_key_exists('moduleSrcDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'moduleSrcDir'"
            );
        }

        if (!array_key_exists('webpackAssetsDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'webpackAssetsDir'"
            );
        }

        if (!array_key_exists('webpackConfigFileFullPath', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'webpackConfigFileFullPath'"
            );
        }

        if (!array_key_exists('configDir', $arrayConfigs)) {
            throw new \Exception(
                "В конфигурационном файле не указана переменная 'configDir'"
            );
        }
    }

    private function createDir($dirName): void
    {
        exec("mkdir -p $dirName");
    }


    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        if (!$rootDir = realpath($rootDir)) {
            throw new \Exception('Корневая директория указана неверно');
        }
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getLogDir(): string
    {
        return $this->logDir;
    }

    public function setLogDir(string $logDir): self
    {
        $this->logDir = $logDir;

        return $this;
    }

    public function getLogFileName(): string
    {
        return $this->logFileName;
    }

    public function setLogFileName(string $logFileName): self
    {
        $this->logFileName = $logFileName;

        return $this;
    }

    public function getDownloadedTemplatesDir(): string
    {
        return $this->downloadedTemplatesDir;
    }

    public function setDownloadedTemplatesDir(string $downloadedTemplatesDir): self
    {
        if (!is_dir($downloadedTemplatesDir)) {
            $this->createDir($downloadedTemplatesDir);
        }
        $this->downloadedTemplatesDir = realpath($downloadedTemplatesDir);

        return $this;
    }

    public function getModuleSrcDir(): string
    {
        return $this->moduleSrcDir;
    }

    public function setModuleSrcDir(string $moduleSrcDir): self
    {
        if (!is_dir($moduleSrcDir)) {
            $this->createDir($moduleSrcDir);
        }
        $this->moduleSrcDir = realpath($moduleSrcDir);

        return $this;
    }

    public function getWebpackConfigFileFullPath(): string
    {
        return $this->webpackConfigFileFullPath;
    }

    public function setWebpackConfigFileFullPath(string $webpackConfigFileFullPath): self
    {
        $this->webpackConfigFileFullPath = realpath($webpackConfigFileFullPath);

        return $this;
    }

    public function getWebpackAssetsDir(): string
    {
        return $this->webpackAssetsDir;
    }

    public function setWebpackAssetsDir(string $webpackAssetsDir): self
    {
        if (!is_dir($webpackAssetsDir)) {
            $this->createDir($webpackAssetsDir);
        }
        $this->webpackAssetsDir = realpath($webpackAssetsDir);

        return $this;
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    public function setConfigDir(string $configDir): self
    {
        if (!is_dir($configDir)) {
            $this->createDir($configDir);
        }
        $this->configDir = realpath($configDir);

        return $this;
    }
}