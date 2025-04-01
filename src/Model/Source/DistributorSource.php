<?php

namespace DigitalMarketingFramework\Core\Model\Source;

use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldListDefinition;

abstract class DistributorSource implements DistributorSourceInterface
{
    public function __construct(
        protected string $type,
        protected string $identifier,
        protected string $hash,
        protected string $name,
        protected string $configurationDocument,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfigurationDocument(): string
    {
        return $this->configurationDocument;
    }

    public function setConfigurationDocument(string $configurationDocument): void
    {
        $this->configurationDocument = $configurationDocument;
    }

    public function getFieldListDefinition(): FieldListDefinition
    {
        return new FieldListDefinition('distributor.in.defaults.' . $this->getIdentifier());
    }
}
