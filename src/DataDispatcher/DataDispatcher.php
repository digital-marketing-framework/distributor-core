<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareTrait;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;
use DigitalMarketingFramework\TemplateEngineTwig\TemplateEngine\TwigTemplateEngine;

abstract class DataDispatcher extends Plugin implements DataDispatcherInterface, TemplateEngineAwareInterface
{
    use TemplateEngineAwareTrait;

    /**
     * @return array<string>
     */
    protected function getPreviewTemplateNameCandidates(): array
    {
        return [
            sprintf('preview/data-dispatcher/%s.html.twig', GeneralUtility::camelCaseToDashed($this->getKeyword())),
            'preview/data-dispatcher/default.html.twig',
        ];
    }

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,string|ValueInterface>
     */
    protected function transformDataForPreview(array $data): array
    {
        return $data;
    }

    public function getPreviewData(array $data): array
    {
        return [
            'keyword' => $this->getKeyword(),
            'class' => static::class,
            'config' => [],
            'data' => $this->transformDataForPreview($data),
        ];
    }

    public function preview(array $data): string
    {
        $viewData = $this->getPreviewData($data);
        $templateNameCandidates = $this->getPreviewTemplateNameCandidates();

        $config = [
            TwigTemplateEngine::KEY_TEMPLATE => '',
            TwigTemplateEngine::KEY_TEMPLATE_NAME => $templateNameCandidates,
        ];

        return $this->templateEngine->render($config, $viewData);
    }
}
