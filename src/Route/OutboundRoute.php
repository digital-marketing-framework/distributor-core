<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\Context\ContextAwareInterface;
use DigitalMarketingFramework\Core\Context\ContextAwareTrait;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareTrait;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareTrait;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Integration\IntegrationInfo;
use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\DataMapperGroupReferenceSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\DataPrivacyPermissionSelectionSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\InheritableBooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\RestrictedTermsSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Plugin\DataProcessor\ConditionSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareTrait;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\IntegrationPlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\RenderingDefinition\Icon;
use DigitalMarketingFramework\TemplateEngineTwig\TemplateEngine\TwigTemplateEngine;

abstract class OutboundRoute extends IntegrationPlugin implements OutboundRouteInterface, DataProcessorAwareInterface, ContextAwareInterface, DataPrivacyManagerAwareInterface, TemplateEngineAwareInterface
{
    use DataProcessorAwareTrait;
    use ContextAwareTrait;
    use DataPrivacyManagerAwareTrait;
    use TemplateEngineAwareTrait;

    public const KEY_ENABLE_DATA_PROVIDERS = 'enableDataProviders';

    public const MESSAGE_GATE_FAILED = 'Gate not passed for route "%s" with ID %s.';

    public const MESSAGE_DATA_EMPTY = 'No data generated for route "%s" with ID %s.';

    public const MESSAGE_NO_DATA_MAPPER_GROUP_DEFINED = 'No data mapper group defined in route "%s" with ID %s.';

    public const MESSAGE_NO_DATA_MAPPER_GROUP_CONFIG_FOUND = 'No data mapper group configuration found for group ID "%s" in outbound route "%s" with ID %s.';

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
        protected string $routeId,
        ?IntegrationInfo $integrationInfo = null,
    ) {
        parent::__construct(
            $keyword,
            $integrationInfo ?? static::getDefaultIntegrationInfo(),
            $submission->getConfiguration(),
            $registry
        );
        $this->configuration = $this->submission->getConfiguration()->getOutboundRouteConfiguration($this->integrationInfo->getName(), $this->routeId);
    }

    abstract public static function getDefaultIntegrationInfo(): IntegrationInfo;

    public function getIntegrationInfo(): IntegrationInfo
    {
        return $this->integrationInfo;
    }

    public function buildData(): DataInterface
    {
        $dataMapperGroupId = $this->getConfig(static::KEY_DATA);
        if ($dataMapperGroupId === '') {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_NO_DATA_MAPPER_GROUP_DEFINED, $this->getKeyword(), $this->routeId));
        }

        $dataMapperGroupConfig = $this->submission->getConfiguration()->getDataMapperGroupConfiguration($dataMapperGroupId);
        if ($dataMapperGroupConfig === null) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_NO_DATA_MAPPER_GROUP_CONFIG_FOUND, $dataMapperGroupId, $this->getKeyword(), $this->routeId));
        }

        $context = $this->dataProcessor->createContext(
            $this->submission->getData(),
            $this->submission->getConfiguration()
        );

        return $this->dataProcessor->processDataMapperGroup($dataMapperGroupConfig, $context);
    }

    protected function getDataProcessorContext(): DataProcessorContextInterface
    {
        return $this->dataProcessor->createContext(
            $this->submission->getData(),
            $this->submission->getConfiguration()
        );
    }

    public function canRetryOnFail(): bool
    {
        return true;
    }

    public function allowed(): bool
    {
        $permission = $this->getConfig(static::KEY_REQUIRED_PERMISSION);

        return $this->dataPrivacyManager->getPermission($permission);
    }

    public function processGate(): bool
    {
        if (!$this->enabled()) {
            return false;
        }

        if (!$this->allowed()) {
            return false;
        }

        $gate = $this->getConfig(static::KEY_GATE);
        if (empty($gate)) {
            return true;
        }

        return $this->dataProcessor->processCondition(
            $this->getConfig(static::KEY_GATE),
            $this->getDataProcessorContext()
        );
    }

    public function getRouteId(): string
    {
        return $this->routeId;
    }

    public function enabled(): bool
    {
        return (bool)$this->getConfig(static::KEY_ENABLED);
    }

    public function async(): ?bool
    {
        return InheritableBooleanSchema::convert($this->getConfig(DistributorConfigurationInterface::KEY_ASYNC));
    }

    public function enableStorage(): ?bool
    {
        return InheritableBooleanSchema::convert($this->getConfig(DistributorConfigurationInterface::KEY_ENABLE_STORAGE));
    }

    public function getEnabledDataProviders(): array
    {
        $config = $this->getConfig(static::KEY_ENABLE_DATA_PROVIDERS);

        return RestrictedTermsSchema::getAllowedTerms($config);
    }

    public function addContext(WriteableContextInterface $context): void
    {
    }

    public function process(): bool
    {
        if (!$this->processGate()) {
            $this->logger->debug(sprintf(static::MESSAGE_GATE_FAILED, $this->getKeyword(), $this->routeId));

            return false;
        }

        $data = $this->buildData();

        if (GeneralUtility::isEmpty($data)) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_DATA_EMPTY, $this->getKeyword(), $this->routeId));
        }

        $dataDispatcher = $this->getDispatcher();
        $dataDispatcher->send($data->toArray());

        return true;
    }

    /**
     * @return array<string>
     */
    protected function getPreviewTemplateNameCandidates(): array
    {
        return [
            sprintf('preview/outbound-route/%s.html.twig', GeneralUtility::camelCaseToDashed($this->getKeyword())),
            'preview/outbound-route/default.html.twig',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    protected function getPreviewData(): array
    {
        $viewData = [
            'outboundRoute' => $this,
            'keyword' => $this->getKeyword(),
            'class' => static::class,
            'skipped' => false,
            'enabled' => true,
            'allowed' => true,
            'dataDispatcherPreview' => '',
            'error' => '',
            'formData' => $this->submission->getData()->toArray(),
            'formContext' => $this->submission->getContext()->toArray(),
        ];

        try {
            if (!$this->processGate()) {
                $viewData['skipped'] = true;
                $viewData['enabled'] = $this->enabled();
                $viewData['allowed'] = $this->allowed();
            } else {
                $data = $this->buildData();

                if (GeneralUtility::isEmpty($data)) {
                    throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_DATA_EMPTY, $this->getKeyword(), $this->routeId));
                }

                $dataDispatcher = $this->getDispatcher();
                $viewData['dataDispatcherPreview'] = $dataDispatcher->preview($data->toArray());
            }
        } catch (DigitalMarketingFrameworkException $e) {
            $viewData['error'] = $e->getMessage();
        }

        return $viewData;
    }

    public function preview(): string
    {
        $viewData = $this->getPreviewData();

        $templateNameCandidates = $this->getPreviewTemplateNameCandidates();

        $config = [
            TwigTemplateEngine::KEY_TEMPLATE => '',
            TwigTemplateEngine::KEY_TEMPLATE_NAME => $templateNameCandidates,
        ];

        return $this->templateEngine->render($config, $viewData);
    }

    abstract protected function getDispatcher(): DataDispatcherInterface;

    /**
     * TODO to be used for auto-generation of data mapper field configuration
     */
    public static function getDefaultPassthroughFields(): bool
    {
        return false;
    }

    public static function getDefaultFields(): array
    {
        return [];
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->getRenderingDefinition()->setIcon(Icon::OUTBOUND_ROUTE);
        $schema->getRenderingDefinition()->setNavigationItem(false);

        $enabledProperty = $schema->addProperty(static::KEY_ENABLED, new BooleanSchema(static::DEFAULT_ENABLED));
        $enabledProperty->setWeight(10);

        $requiredPermissionSchema = new CustomSchema(DataPrivacyPermissionSelectionSchema::TYPE);
        $requiredPermissionProperty = $schema->addProperty(static::KEY_REQUIRED_PERMISSION, $requiredPermissionSchema);
        $requiredPermissionProperty->setWeight(15);

        $gateSchema = new CustomSchema(ConditionSchema::TYPE);
        $gateSchema->getRenderingDefinition()->setLabel('Gate');
        $gateProperty = $schema->addProperty(static::KEY_GATE, $gateSchema);
        $gateProperty->setWeight(20);

        $asyncSchema = new InheritableBooleanSchema();
        $asyncSchema->getRenderingDefinition()->addReference(
            sprintf(
                '/%s/%s/%s/%s',
                ConfigurationInterface::KEY_INTEGRATIONS,
                ConfigurationInterface::KEY_GENERAL_INTEGRATION,
                DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES,
                DistributorConfigurationInterface::KEY_ASYNC
            ),
            label: 'Original Value ({.})'
        );
        $asyncSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(DistributorConfigurationInterface::KEY_ASYNC, $asyncSchema);

        $enableStorageSchema = new InheritableBooleanSchema();
        $enableStorageSchema->getRenderingDefinition()->addReference(
            sprintf(
                '/%s/%s/%s/%s',
                ConfigurationInterface::KEY_INTEGRATIONS,
                ConfigurationInterface::KEY_GENERAL_INTEGRATION,
                DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES,
                DistributorConfigurationInterface::KEY_ENABLE_STORAGE
            ),
            label: 'Original Value ({.})'
        );
        $enableStorageSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(DistributorConfigurationInterface::KEY_ENABLE_STORAGE, $enableStorageSchema);

        $enableDataProviders = new RestrictedTermsSchema('/dataProcessing/dataProviders/*');
        $enableDataProviders->getTypeSchema()->getRenderingDefinition()->setLabel('Enable Data Providers');
        $enableDataProviders->getRenderingDefinition()->setSkipHeader(true);
        $enableDataProviders->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_ENABLE_DATA_PROVIDERS, $enableDataProviders);

        $dataSchema = new CustomSchema(DataMapperGroupReferenceSchema::TYPE);
        $schema->addProperty(static::KEY_DATA, $dataSchema);

        return $schema;
    }
}
