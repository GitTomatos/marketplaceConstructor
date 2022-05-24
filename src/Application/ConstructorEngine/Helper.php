<?php

declare(strict_types=1);

namespace App\Application\ConstructorEngine;

use App\Application\ConstructorEngine\DTO\TemplateParts;

final class Helper
{
    /**
     * Получить название entry для конфигурационного файла webpack
     */
    public function getEntryNameForWebpack(TemplateParts $templateParts): string
    {
        return $templateParts->getECommerceType()
            . '-' . $templateParts->getModuleName()
            . '-' . $templateParts->getTemplateName();
    }

    /**
     * Получаем:
     *  - название типа коммерции
     *  - название модуля
     *  - название шаблона
     * по ссылке на этот шаблон в репозитории шаблонов
     *
     * @param $templateLink - строка типа {...}/b2b/style1/menu
     */
    public function getTemplatePartsByLink(string $templateLink): TemplateParts
    {
        $pathToTemplateParts = explode('/', $templateLink);

        $templatePartsCount = count($pathToTemplateParts);
        if ($templatePartsCount < 3) {
            echo "Неправильное полное название шаблона!";
            exit(1);
        }

        $eCommerceType = $pathToTemplateParts[$templatePartsCount - 3];
        $moduleName = $pathToTemplateParts[$templatePartsCount - 2];
        $templateName = $pathToTemplateParts[$templatePartsCount - 1];

        return new TemplateParts(
            $eCommerceType,
            $moduleName,
            $templateName,
        );
    }
}