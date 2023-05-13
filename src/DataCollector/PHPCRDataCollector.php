<?php

namespace Doctrine\Bundle\PHPCRBundle\DataCollector;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Jackalope\Query\Query;
use Jackalope\Transport\Logging\DebugStack;
use PHPCR\Query\QueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class PHPCRDataCollector extends DataCollector
{
    private ManagerRegistryInterface $registry;

    /**
     * @var string[]
     */
    private array $connections;

    /**
     * @var string[]
     */
    private array $managers;

    /**
     * @var DebugStack[]
     */
    private array $loggers = [];

    public function __construct(ManagerRegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->connections = $registry->getConnectionNames();
        $this->managers = $registry->getManagerNames();
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->collectInternal($request, $response);
    }

    /**
     * Adds the stack logger for a connection.
     */
    public function addLogger(string $name, DebugStack $logger): void
    {
        $this->loggers[$name] = $logger;
    }

    public function getManagers(): array
    {
        return $this->data['managers'];
    }

    public function getConnections(): array
    {
        return $this->data['connections'];
    }

    public function getCallCount(): int
    {
        return array_sum(array_map('count', $this->data['calls']));
    }

    public function getCalls(): array
    {
        return $this->data['calls'];
    }

    public function getTime(): int
    {
        $time = 0;
        foreach ($this->data['calls'] as $calls) {
            foreach ($calls as $call) {
                $time += $call['executionMS'];
            }
        }

        return $time;
    }

    public function getName(): string
    {
        return 'phpcr';
    }

    private function sanitizeCalls($calls)
    {
        foreach ($calls as $i => $call) {
            $calls[$i] = $this->sanitizeCall($call);
        }

        return $calls;
    }

    private function sanitizeCall($call)
    {
        $call['params'] = (array) $call['params'];
        foreach ($call['params'] as &$param) {
            $param = $this->sanitizeParam($param);
        }
        $call['env'] = (array) $call['env'];

        return $call;
    }

    /**
     * Sanitizes a param.
     *
     * The return value is an array with the sanitized value and a boolean
     * indicating if the original value was kept (allowing to use the sanitized
     * value to explain the call).
     *
     * @param mixed $var
     */
    private function sanitizeParam($var): array
    {
        if (\is_object($var)) {
            if ($var instanceof QueryInterface) {
                $query = [
                    'querystring' => $var->getStatement(),
                    'language' => $var->getLanguage(),
                ];
                if ($var instanceof Query) {
                    $query['limit'] = $var->getLimit();
                    $query['offset'] = $var->getOffset();
                }

                return $query;
            }

            return [sprintf('Object(%s)', \get_class($var)), false];
        }

        if (\is_array($var)) {
            $a = [];
            $original = true;
            foreach ($var as $k => $v) {
                list($value, $orig) = $this->sanitizeParam($v);
                $original = $original && $orig;
                $a[$k] = $value;
            }

            return [$a, $original];
        }

        if (\is_resource($var)) {
            return [sprintf('Resource(%s)', get_resource_type($var)), false];
        }

        return [$var, true];
    }

    protected function collectInternal(Request $request, Response $response, \Throwable $exception = null): void
    {
        $calls = [];
        foreach ($this->loggers as $name => $logger) {
            $calls[$name] = $this->sanitizeCalls($logger->calls);
        }

        $this->data = [
            'calls' => $calls,
            'connections' => $this->connections,
            'managers' => $this->managers,
        ];

        $documents = [];

        foreach ($this->registry->getManagers() as $name => $em) {
            $documents[$name] = [];
            /** @var ClassMetadataFactory $factory */
            $factory = $em->getMetadataFactory();

            /** @var ClassMetadata $class */
            foreach ($factory->getLoadedMetadata() as $class) {
                $documents[$name][] = $class->getName();
            }
        }

        $this->data['documents'] = $documents;
    }

    public function getDocuments()
    {
        return $this->data['documents'];
    }

    public function reset(): void
    {
        $this->data = [];

        foreach ($this->loggers as $logger) {
            $logger->calls = [];
            $logger->currentQuery = 0;
        }
    }
}
