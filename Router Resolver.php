<?php

class Solution
{
    private $route;
    private $url;

    private $isValid = true;

    public $response = [];

    public function __construct($route, $url)
    {
        $this->route = $route;
        $this->url = $url;

        $this->response = parse_url($url);
    }

    private function validate()
    {
        if (!isset($this->response['scheme'])) {
            $this->isValid = false;
        }

        return $this;
    }

    private function parseParameters()
    {
        if (!$this->isValid) {
            return $this;
        }

        $hostAndParameters = explode('/', $this->response['path']);
        $routeParameters = explode('/', $this->route);

        $this->response['parameters'] = [];

        if (count($routeParameters) > 1) {
            for ($i = 1; $i <= count($hostAndParameters) - 1; $i++) {
                if (strpos($routeParameters[$i], ':') !== false) {
                    $routeParameters[$i] = $this->stripAll($routeParameters[$i]);
                    $this->response['parameters'][$routeParameters[$i]] = $hostAndParameters[$i];
                } elseif ($this->stripAll($routeParameters[$i]) !== $hostAndParameters[$i]) {
                    $this->isValid = false;
                }
            }
        }

        return $this;
    }

    private function stripAll($string)
    {
        return preg_replace('/[^[:alnum:]]/', '',$string); // returns 'ABC123'
    }

    public function getData()
    {
        $this
            ->validate()
            ->parseParameters();

        if (!$this->isValid) {
            return '{}';
        }

        return json_encode($this->response, JSON_UNESCAPED_SLASHES|JSON_FORCE_OBJECT);
    }
}

// input -> ('/', 'https://www.expertlead.com'));
// input -> ('/:lang', 'https://www.expertlead.com/en'));
// input -> ('/:lang/products', 'https://www.expertlead.com/en/products'));
// input -> ('/:lang/products/:id', 'https://www.expertlead.com/en/products/418'));
// input -> ('/:lang/product/:id', 'https://www.expertlead.com/en/products/418'));
// input -> ('/:lang/products/:id/compare/:compareId', 'https://www.expertlead.com/en/products/418/compare/420'));
// input -> ('/:lang/products/:id/images[/:imageId]', 'https://www.expertlead.com/en/products/418/images'));
