<?php
namespace SlimDash\Core;

abstract class SlimDashController
{
    protected $request;
    protected $response;
    protected $args;
    protected $container;

    /**
     * base controller
     * @param $request   the request object
     * @param $response  the response object
     * @param $args      the route args
     * @param $container the container
     */
    public function __construct($request, $response, $args, $container)
    {
        $this->request   = $request;
        $this->response  = $response;
        $this->args      = $args;
        $this->container = $container;
    }

    /**
     * Allow for dependency injection defined in container.
     * @param  $property property name
     * @return the       object on the $container if found
     */
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }

    /**
     * Shortcut method for rendering a view.
     * @param  string $name        view name
     * @param  array  $args        view params
     * @return the    controller
     */
    public function render($name, array $args = [])
    {
        if ($this->router) {
            $args['router'] = $this->router;
        }

        // generate the html
        $html = $this->renderer->render($this->response, $name . '.' . $this->settings['renderer']['ext'], $args);

        return $this;
    }

    /**
     * get a body parameter
     * @param  string $param name of the parameter
     * @return the    data
     */
    public function param($param)
    {
        return $this->request->getParam($param);
    }

    /**
     * get a query parameter
     * @param  string $param name of the parameter
     * @return the    data
     */
    public function queryParam($param)
    {
        return $this->request->getQueryParam($param);
    }

    /**
     * get tenant with current host with pattern
     * lowercase of: www.(tenantCode).blah.blah.com
     * @return string parsed tenant
     */
    private function getTenantByHost()
    {
        $uri   = $this->request->getUri();
        $host  = strtolower($uri->getHost());
        $host  = str_replace('www.', '', $host);
        $hosts = explode('.', $host);
        if (count($hosts) > 1) {
            return $hosts[0];
        }
        return '';
    }

    /**
     * get the tenant code
     * use APP_TENANT environment variable for single tenant
     * fallback to querystring of tenantCode
     * fallback to hostname
     * @return string the sanitized tenant code
     */
    public function tenantCode()
    {
        // use configuration to get single tenant setup
        $tenantCode = getenv('APP_TENANT');
        if (!isset($tenantCode)) {
            $tenantCode = $this->queryParam('tenantCode');
            if (!isset($tenantCode)) {
                $tenantCode = $this->getTenantByHost();
            }
        }
        // replace all non-alphanumeric to be underscore
        $tenant = preg_replace("[^a-z0-9_]", "_", $tenantCode);
        // underscore are later handled by individual storage
        return $tenant;
    }

    /**
     * Get all  possible client IPs
     * @return array all client IP data found in header
     */
    public function getIPs()
    {
        $ips = [];
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            $ip = $this->request->getHeader($key);
            if (isset($ip)) {
                $ips[$key] = $ip;
            }
        }
    }
}
