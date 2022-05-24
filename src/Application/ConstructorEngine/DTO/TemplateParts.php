<?php

declare(strict_types=1);

namespace App\Application\ConstructorEngine\DTO;

final class TemplateParts
{
    public function __construct(
        private string $eCommerceType,
        private string $moduleName,
        private string $templateName,
    ) {
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getECommerceType(): string
    {
        return $this->eCommerceType;
    }

    public function getFullModuleName(): string
    {
        return $this->eCommerceType . '/' . $this->moduleName;
    }

    public function getFullTemplateFileName(): string
    {
        return $this->eCommerceType . '/' . $this->moduleName . '/' . $this->templateName . '.twig';
    }
}