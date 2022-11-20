<?php

namespace DigitalMarketingFramework\Distributer\Core\Route;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;
use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

interface RouteInterface extends PluginInterface
{
    public function getPassCount(SubmissionDataSetInterface $submission): int;

    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function processPass(SubmissionDataSetInterface $submission, int $pass): bool;

    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request, int $pass): void;

    public static function getDefaultConfiguration(): array;
}
