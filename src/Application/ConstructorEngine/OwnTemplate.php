<?php

declare(strict_types=1);

namespace App\Application\ConstructorEngine;

use App\Application\ConstructorEngine\DTO\TemplateParts;
use App\Application\ValueObject\EngineConfigs;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OwnTemplate
{
    private Logger $logger;
    private Helper $helper;
    private HttpClientInterface $client;

    private EngineConfigs $configs;
    private TemplateParts $templateParts;

    public function __construct(
        string $pathToConfigs,
        HttpClientInterface $client,
        Logger $logger = null,
    ) {
        $this->client = $client;

        if ($logger !== null) {
            $this->logger = $logger;
        }

        $this->readEngineConfigs($pathToConfigs);

        $this->helper = new Helper();
    }


    public function own(): void
    {
        try {
            $templatesToDownload = $this->readTemplatePath();

            $this->logger = new Logger($this->configs->getLogDir(), $this->configs->getLogFileName());

            foreach ($templatesToDownload as $templateToDownload) {
                $this->templateParts = $this->helper->getTemplatePartsByLink($templateToDownload);
                $this->download();
            }
        } catch (\Throwable $exception) {
            $this->logger->log($exception->getMessage());
            throw $exception;
        }
    }


    /**
     * @param $pathToConfigs - путь до конфигурации
     *
     * @throws \Exception
     */
    private function readEngineConfigs($pathToConfigs): void
    {
        if (!file_exists($pathToConfigs)) {
            throw new \Exception("Не найден конфигурационный файл 'engineConfigs.php'");
        }
        $configs = require $pathToConfigs;

        $this->configs = EngineConfigs::createFromArray($configs);
    }


    /**
     * Устанавливаем переменные названия модуля и шаблона для скачивания
     */
    private function readTemplatePath(): array
    {
        $downloadAllModule = readline(
            <<<END
Выберите, что нужно скачать:
    1 - Весь модуль
    2 - Конкретный шаблон

---> 
END
        );

        $downloadAllModule = $downloadAllModule === '1';

        /**
         * Может вводиться как ссылка формата
         * http://localhost/previewTemplate/b2b/style1/index
         *
         * Так и полное название шаблона формата
         * b2b/style1/index
         */
        $userTemplateLink = readline(
            $downloadAllModule
                ? 'Введите ссылку на модуль, который хотите загрузить (через "-" или через "/"): '
                : 'Введите ссылку на шаблон, который хотите загрузить (через "/" указать тип электронной коммерции, название модуля, название шаблона): '
        );

        $isCorrect = $downloadAllModule
            ? preg_match('/^[a-zA-Z0-9]*\/[a-zA-Z0-9]*$/', $userTemplateLink)
            : preg_match('/^[a-zA-Z0-9]*\/[a-zA-Z0-9]*\/[a-zA-Z0-9]*$/', $userTemplateLink);

        if (!$isCorrect) {
            throw new \Exception(
                $downloadAllModule
                    ? 'Неправильное название модуля. Нужно указать тип электронной коммерции и название модуля через "/"
                    (например, b2b/style1)'
                    : 'Неправильное название шаблона. Нужно указать тип электронной коммерции, название модуля
                    и название шаблона через "/" (например, b2b/style1/menu)'
            );
        }

        return $downloadAllModule
            ? $this->getModuleTemplatesList($userTemplateLink)
            : [$userTemplateLink];
    }

    private function getModuleTemplatesList($fullModuleName): array
    {
        [$eCommerceType, $moduleName] = explode('/', $fullModuleName);
        $response = $this->client->request(
            'GET',
            "http://preview-nginx/get_module_template_names/$eCommerceType-$moduleName",
        );

        $fullTemplatesNames = [];
        foreach ($response->toArray() as $templateName) {
            $templateName = lcfirst($templateName);
            $fullTemplatesNames[] = "$eCommerceType/$moduleName/$templateName";
        }

        return $fullTemplatesNames;
    }

    private function getFullModuleAssetsDir(): ?string
    {
        $fullModuleName = $this->templateParts->getFullModuleName();
        $templateName = ucfirst($this->templateParts->getTemplateName());

        return "{$this->configs->getDownloadedTemplatesDir()}/$fullModuleName/$templateName/assets";
    }

    private function getFullModuleWebpackEntryDir(): ?string
    {
        $fullModuleAssetsDir = $this->getFullModuleAssetsDir();

        return "$fullModuleAssetsDir/webpack/front.js";
    }

    private function getFullTemplateSrcDir(): ?string
    {
        return isset($this->templateParts)
            ? ($this->templateParts->getECommerceType()
                . '/' . $this->templateParts->getModuleName()
                . '/' . ucfirst($this->templateParts->getTemplateName()))
            : null;
    }

    private function download(): void
    {
        $knownHosts = file_get_contents('/root/.ssh/known_hosts');

        //Избегаем проверки ssh ключа при подключении к репозиторию
        if ($knownHosts === '') {
            exec('ssh-keyscan -H preview-ssh-server >> ~/.ssh/known_hosts');
        }

        $this->copyTemplatesFromRepository();
        $this->addEntryToWebpackConfig();
        $this->copyTemplateFunctionalFromRepository();
        $this->copyMigrationsFromRepository();
        $this->copyRouteConfigsFromRepository();
        $this->copyDoctrineConfigsFromRepository();
    }

    /**
     * Скопировать шаблоны модуля из репозитория
     */
    private function copyTemplatesFromRepository(): void
    {
        $fullModuleName = $this->templateParts->getFullModuleName();
        $fullECommerceTemplatePath = "{$this->configs->getDownloadedTemplatesDir()}/$fullModuleName";
        $templateName = ucfirst($this->templateParts->getTemplateName());

        //Создаём директорию для шаблонов, если её нет
        exec("mkdir -p $fullECommerceTemplatePath");

        //Копируем шаблон модуля из репозитория
        exec('sshpass -p "123" scp -r '
            . "sshuser@preview-ssh-server:/app/templates/$fullModuleName/$templateName $fullECommerceTemplatePath"
        );
    }

    /**
     * Скачать файлы функционала модуля (папка src) из репозитория
     */
    private function copyTemplateFunctionalFromRepository(): void
    {
        $newFullModuleSrcDir = $this->getNewFullModuleSrcDir();

        //Создаём директорию для функционала модуля, если её нет
        $fullTemplateName = $this->getFullTemplateSrcDir();
        exec("mkdir -p {$this->configs->getModuleSrcDir()}/$fullTemplateName");

        //Копируем функционал модуля из репозитория
        exec('sshpass -p "123" scp -r '
            . "sshuser@preview-ssh-server:/app/src/Module/$fullTemplateName $newFullModuleSrcDir"
        );
    }

    /**
     * Скачать миграции шаблона из репозитория
     */
    private function copyMigrationsFromRepository(): void
    {
        $fullModuleName = $this->templateParts->getFullModuleName();
        $templateName = ucfirst($this->templateParts->getTemplateName());
        $migrationsDir = "{$this->configs->getRootDir()}/migrations/$fullModuleName";
        $remoteMigrationsDir = "/app/migrations/$fullModuleName/$templateName";

        if (!$this->isRemoteDirExists($remoteMigrationsDir)) {
            return;
        }

        //Создаём директорию для файлов конфигурации приложения (конфигурация контроллеров шаблонов модуля)
        exec("mkdir -p $migrationsDir");

        //Копируем конфигурации routes из репозитория
        exec('sshpass -p "123" scp -r '
            . "sshuser@preview-ssh-server:$remoteMigrationsDir $migrationsDir"
        );
    }

    /**
     * Скачать файлы конфигурации приложения из репозитория (директория config/routes)
     */
    private function copyRouteConfigsFromRepository(): void
    {
        $fullModuleName = $this->templateParts->getFullModuleName();
        $templateName = lcfirst($this->templateParts->getTemplateName());
        $routesConfigDir = "{$this->configs->getConfigDir()}/routes/modules/$fullModuleName";
        $remoteRouteFile = "/app/config/routes/modules/$fullModuleName/$templateName.yml";

        if (!$this->isRemoteFileExists($remoteRouteFile)) {
            return;
        }

        //Создаём директорию для файлов конфигурации приложения (конфигурация контроллеров шаблонов модуля)
        exec("mkdir -p $routesConfigDir");

        //Копируем конфигурации routes из репозитория
        exec('sshpass -p "123" scp -r '
            . "sshuser@preview-ssh-server:$remoteRouteFile $routesConfigDir"
        );
    }

    /**
     * Скачать файлы конфигурации приложения из репозитория (директория config)
     */
    private function copyDoctrineConfigsFromRepository(): void
    {
        //Создаём директорию для файлов конфигурации Doctrine
        exec("mkdir -p {$this->configs->getConfigDir()}/marketplaceEngine/doctrine");

        $this->createRouteConfigs();
        $this->createDoctrineParamsFile();
        $this->createDoctrineMigrationsParamsFile();
    }

    private function createRouteConfigs(): void
    {
        $remoteDoctrineParamsDir = "/app/config/marketplaceEngine/doctrine/{$this->getNewFullTemplateName()}";
        $doctrineParamsDir = "{$this->configs->getConfigDir()}/marketplaceEngine/doctrine/{$this->templateParts->getFullModuleName()}";

        if (!$this->isRemoteDirExists($remoteDoctrineParamsDir)) {
            return;
        }

        //Создаём директорию для файлов конфигурации Doctrine
        exec("mkdir -p $doctrineParamsDir");

        //Копируем конфигурации routes из репозитория
        exec('sshpass -p "123" scp -r '
            . "sshuser@preview-ssh-server:$remoteDoctrineParamsDir $doctrineParamsDir"
        );
    }

    private function createDoctrineParamsFile(): void
    {
        $defaultContent = <<<END
parameters:
    modules_mapping:
        default:
            is_bundle: false
            dir: '%kernel.project_dir%/src/Domain/Entity'
            prefix: 'App\Domain\Entity'
            alias: App\Application
END;

        $doctrineParamsFilePath = "{$this->configs->getConfigDir()}/marketplaceEngine/doctrine/doctrine_params.yml";
        $doctrineTemplateMappingFilePath =
            "{$this->configs->getConfigDir()}/marketplaceEngine/doctrine/{$this->getNewFullTemplateName()}/doctrine.yml";

        if (!is_file($doctrineParamsFilePath)) {
            file_put_contents($doctrineParamsFilePath, $defaultContent);
        }

        if (!is_file($doctrineTemplateMappingFilePath)) {
            return;
        }

        $doctrineParamsFileContent = file_get_contents($doctrineParamsFilePath);
        $doctrineTemplateMappingFileContent = file_get_contents($doctrineTemplateMappingFilePath);

        $separator = '\\\\';
        $moduleMappingName = $this->templateParts->getECommerceType() . $separator
            . $this->templateParts->getModuleName() . $separator
            . ucfirst($this->templateParts->getTemplateName());

        //Проверяем, существует ли в файле doctrine_params.yml параметр с таким названием (полным названием шаблона)
        $isExist = preg_match("/$moduleMappingName/", $doctrineParamsFileContent);

        if (!$isExist) {
            $newContent = $doctrineParamsFileContent . PHP_EOL . $doctrineTemplateMappingFileContent;
            file_put_contents($doctrineParamsFilePath, $newContent);
        }
    }

    private function createDoctrineMigrationsParamsFile(): void
    {
        $defaultContent = <<<END
parameters:
    migrations_paths:
        'default': '%kernel.project_dir%/migrations'
END;

        $doctrineParamsFilePath = "{$this->configs->getConfigDir()}/marketplaceEngine/doctrine/doctrine_migration_params.yml";
        $doctrineTemplateMappingFilePath =
            "{$this->configs->getConfigDir()}/marketplaceEngine/doctrine/{$this->getNewFullTemplateName()}/doctrine_migration.yml";

        if (!is_file($doctrineParamsFilePath)) {
            file_put_contents($doctrineParamsFilePath, $defaultContent);
        }

        if (!is_file($doctrineTemplateMappingFilePath)) {
            return;
        }

        $doctrineParamsFileContent = file_get_contents($doctrineParamsFilePath);
        $doctrineTemplateMappingFileContent = file_get_contents($doctrineTemplateMappingFilePath);

        $separator = '\\\\';
        $moduleMappingName = $this->templateParts->getECommerceType() . $separator
            . $this->templateParts->getModuleName() . $separator
            . ucfirst($this->templateParts->getTemplateName());

        //Проверяем, существует ли в файле doctrine_migration_params.yml параметр с таким названием (полным названием шаблона)
        $isExist = preg_match("/$moduleMappingName/", $doctrineParamsFileContent);

        if (!$isExist) {
            $newContent = $doctrineParamsFileContent . PHP_EOL . $doctrineTemplateMappingFileContent;
            file_put_contents($doctrineParamsFilePath, $newContent);
        }
    }

    private function isRemoteFileExists(string $filePath): bool
    {
        $isExists = exec(
            "sshpass -p \"123\" ssh sshuser@preview-ssh-server test -f \
            $filePath && echo \"yes\" || echo \"no\""
        );

        if (!($isExists === 'yes')) {
            $this->logger->log("В репозитории нет файла $filePath");
        }

        return $isExists === 'yes';
    }

    private function isRemoteDirExists(string $dirPath): bool
    {
        $isExists = exec(
            "sshpass -p \"123\" ssh sshuser@preview-ssh-server test -d \
            $dirPath && echo \"yes\" || echo \"no\""
        );

        if (!($isExists === 'yes')) {
            $this->logger->log("В репозитории нет директории $dirPath");
        }

        return $isExists === 'yes';
    }

    private function getNewFullTemplateName(): string
    {
        return $this->templateParts->getFullModuleName() . '/' . $this->templateParts->getTemplateName();
    }

    /**
     * Полное название папки с функционалом модуля
     */
    private function getNewFullModuleSrcDir(): string
    {
        $fullModuleName = $this->templateParts->getFullModuleName();

        return "{$this->configs->getModuleSrcDir()}/$fullModuleName";
    }

    private function addEntryToWebpackConfig(): void
    {
        $webpackConfigFileContent = file_get_contents($this->configs->getWebpackConfigFileFullPath());
        preg_match('/Encore\n((?:.|\s)*?);\n*module\.exports/', $webpackConfigFileContent, $matches);

        $encoreContent = $matches[1];
        preg_match_all('/\.addEntry\(.*?\)/', $encoreContent, $matches);

        $existingEntries = $matches[0];
//        print_r($existingEntries);

        $newEntryName = $this->helper->getEntryNameForWebpack($this->templateParts);
        $newEntrySrc = $this->getFullModuleWebpackEntryDir();

        $newLastEntry = ".addEntry('$newEntryName', '$newEntrySrc')";

        $existingEntriesCount = count($existingEntries);

        if ($existingEntriesCount === 0) {
            $newEncoreContent = $encoreContent . $newLastEntry . PHP_EOL;
            $newWebpackConfigFileContent = str_replace(
                $encoreContent,
                $newEncoreContent,
                $webpackConfigFileContent
            );
        } elseif (!in_array($newLastEntry, $existingEntries, true)) { //TODO проверять на наличие такого же названия entry, а не всей строки
            $lastEntry = $existingEntries[$existingEntriesCount - 1];
            $newLastTwoEntry = $lastEntry . PHP_EOL . $newLastEntry;

            $newWebpackConfigFileContent = str_replace(
                $lastEntry,
                $newLastTwoEntry,
                $webpackConfigFileContent
            );
        } else {
            return;
        }

        file_put_contents($this->configs->getWebpackConfigFileFullPath(), $newWebpackConfigFileContent);
    }
}