<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContext;
use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\GeneralEvaluation;
use DigitalMarketingFramework\Core\Helper\ConfigurationTrait;
use DigitalMarketingFramework\Core\Plugin\Plugin;
use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

abstract class DataProvider extends Plugin implements DataProviderInterface
{
    use ConfigurationTrait;

    const KEY_ENABLED = 'enabled';
    const DEFAULT_ENABLED = false;

    const KEY_MUST_EXIST = 'mustExist';
    const DEFAULT_MUST_EXIST = false;

    const KEY_MUST_BE_EMPTY= 'mustBeEmpty';
    const DEFAULT_MUST_BE_EMPTY = true;

    public function __construct(
        string $keyword, 
        protected RegistryInterface $registry
    ) {
        parent::__construct($keyword);
    }

    abstract protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void;
    abstract protected function process(SubmissionDataSetInterface $submission): void;

    protected function proceed(SubmissionDataSetInterface $submission): bool
    {
        $context = new ConfigurationResolverContext(
            $submission->getData(),
            [
                'configuration' => $submission->getConfiguration(),
            ]
        );
        /** @var GeneralEvaluation $evaluation */
        $evaluation = $this->registry->getEvaluation(
            'general',
            $this->getConfig(static::KEY_ENABLED),
            $context
        );
        return $evaluation->eval();
    }

    protected function addRequestVariableToContext(SubmissionDataSetInterface $submission, RequestInterface $request, string $variableName): bool
    {
        $variableValue = $request->getRequestVariable($variableName);
        if (!GeneralUtility::isEmpty($variableValue)) {
            $submission->getContext()->setRequestVariable($variableName, $variableValue);
            return true;
        }
        return false;
    }

    protected function getRequestVariableFromContext(SubmissionDataSetInterface $submission, string $variableName)
    {
        return $submission->getContext()->getRequestVariable($variableName);
    }

    protected function addCookieToContext(SubmissionDataSetInterface $submission, RequestInterface $request, string $cookieName, $default = null): bool
    {
        $cookieValue = $request->getCookies()[$cookieName] ?? $default;
        if ($cookieValue !== null) {
            $submission->getContext()->setCookie($cookieName, $cookieValue);
            return true;
        }
        return false;
    }

    protected function getCookieFromContext(SubmissionDataSetInterface $submission, string $cookieName, $default = null)
    {
        return $submission->getContext()->getCookie($cookieName, $default);
    }

    protected function appendToField(SubmissionDataSetInterface $submission, $key, $value, $glue = "\n"): bool
    {
        $data = $submission->getData();
        if (
            $this->getConfig(static::KEY_MUST_EXIST)
            && !$data->fieldExists($key)
        ) {
            return false;
        }

        if ($data->fieldEmpty($key)) {
            $data[$key] = $value;
        } else {
            $data[$key] .= $glue . $value;
        }

        return true;
    }

    protected function setField(SubmissionDataSetInterface $submission, $key, $value): bool
    {
        $data = $submission->getData();
        if (
            $this->getConfig(static::KEY_MUST_EXIST)
            && !$data->fieldExists($key)
        ) {
            return false;
        }
        if (
            $this->getConfig(static::KEY_MUST_BE_EMPTY)
            && $data->fieldExists($key)
            && !$data->fieldEmpty($key)
        ) {
            return false;
        }
        $data[$key] = $value;
        return true;
    }

    protected function appendToFieldFromContext(SubmissionDataSetInterface $submission, $key, $field = null, $glue = "\n"): bool
    {
        $value = $submission->getContext()[$key] ?? null;
        if ($value !== null) {
            return $this->appendToField($submission, $field ?: $key, $value, $glue);
        }
        return false;
    }

    protected function setFieldFromContext(SubmissionDataSetInterface $submission, $key, $field = null): bool
    {
        $value = $submission->getContext()[$key] ?? null;
        if ($value !== null) {
            return $this->setField($submission, $field ?: $key, $value);
        }
        return false;
    }

    protected function appendToFieldFromCookie(SubmissionDataSetInterface $submission, $cookieName, $field = null, $glue = "\n"): bool
    {
        $value = $this->getCookieFromContext($submission, $cookieName);
        if ($value !== null) {
            return $this->appendToField($submission, $field ?: $cookieName, $value, $glue);
        }
        return false;
    }

    protected function setFieldFromCookie(SubmissionDataSetInterface $submission, $cookieName, $field = null): bool
    {
        $value = $this->getCookieFromContext($submission, $cookieName);
        if ($value !== null) {
            return $this->setField($submission, $field ?: $cookieName, $value);
        }
        return false;
    }

    protected function appendToFieldFromRequestVariable(SubmissionDataSetInterface $submission, $variableName, $field = null, $glue = "\n"): bool
    {
        $value = $this->getRequestVariableFromContext($submission, $variableName);
        if ($value !== null) {
            return $this->appendToField($submission, $field ?: $variableName, $value, $glue);
        }
        return false;
    }

    protected function setFieldFromRequestVariable(SubmissionDataSetInterface $submission, $variableName, $field = null): bool
    {
        $value = $this->getRequestVariableFromContext($submission, $variableName);
        if ($value !== null) {
            return $this->setField($submission, $field ?: $variableName, $value);
        }
        return false;
    }

    public function addData(SubmissionDataSetInterface $submission): void
    {
        $this->configuration = $submission->getConfiguration()->getDataProviderConfiguration($this->getKeyword());
        if ($this->proceed($submission)) {
            $this->process($submission);
        }
    }

    protected function addToContext(SubmissionDataSetInterface $submission, $key, $value)
    {
        $submission->getContext()[$key] = $value;
    }

    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $this->configuration = $submission->getConfiguration()->getDataProviderConfiguration($this->getKeyword());
        if ($this->proceed($submission)) {
            $this->processContext($submission, $request);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ENABLED => static::DEFAULT_ENABLED,
            static::KEY_MUST_EXIST => static::DEFAULT_MUST_EXIST,
            static::KEY_MUST_BE_EMPTY => static::DEFAULT_MUST_BE_EMPTY,
        ];
    }
}
