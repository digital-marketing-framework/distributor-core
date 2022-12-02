<?php

namespace DigitalMarketingFramework\Distributer\Core\Service;

use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

interface RelayInterface
{
    public function process(SubmissionDataSetInterface $submission): void;
}
