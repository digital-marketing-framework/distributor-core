<?php

namespace DigitalMarketingFramework\Core\Model\Source;

use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldListDefinition;

interface DistributorSourceInterface
{
    public function getType(): string;
    public function getIdentifier(): string;
    public function getHash(): string;
    public function getName(): string;
    public function getConfigurationDocument(): string;
    public function getFieldListDefinition(): FieldListDefinition;
}
