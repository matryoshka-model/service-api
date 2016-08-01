<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Matryoshka\Service\Api\Client\HttpApi;

/**
 * Class HttpApiAbstractServiceFactory
 */
class HttpApiAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'matryoshka-service-api';

    /**
     * @var array
     */
    protected $config;

    /**
     * Determine if we can create a service with name
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        if (isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Create service with name
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return HttpApi
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container)[$requestedName];

        $httpClient = isset($config['http_client']) && $container->has($config['http_client']) ?
        $container->get($config['http_client']) : null;

        $baseRequest = isset($config['base_request']) && $container->has($config['base_request']) ?
        $container->get($config['base_request']) : null;

        $api = new HttpApi($httpClient, $baseRequest);

        // Array of int code valid
        if (isset($config['valid_status_code']) && is_array($config['valid_status_code'])) {
            $api->setValidStatusCodes($config['valid_status_code']);
        }
        // string json/xml
        if (isset($config['request_format'])) {
            $api->setRequestFormat($config['request_format']);
        }
        // Profiler
        if (isset($config['profiler']) && $container->has($config['profiler'])) {
            $api->setProfiler($container->get($config['profiler']));
        }

        return $api;
    }

    /**
     * Get api service configuration, if any
     *
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $config = $container->get('Config');
        if (!isset($config[$this->configKey]) || !is_array($config[$this->configKey])) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}