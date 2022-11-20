<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\Plugin\PluginInterface;
use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

interface DataProviderInterface extends PluginInterface
{
    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request): void;
    public function addData(SubmissionDataSetInterface $submission): void;

    public static function getDefaultConfiguration(): array;
}
