<?php
/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Gantry\Admin;

use Gantry\Component\Request\Request;
use Gantry\Component\Response\JsonResponse;
use Gantry\Component\Response\Response;
use Gantry\Component\Router\Router as BaseRouter;

/**
 * Gantry administration router for WordPress.
 */
class Router extends BaseRouter
{
    public function boot()
    {
        /** @var Request $request */
        $request = $this->container['request'];

        $this->container['content'] = function ($c) {
            return new Content($c);
        };

        $this->method = $request->getMethod();
        $this->path = explode('/', $request->get->get('view', 'configurations/styles'));
        $this->resource = array_shift($this->path) ?: 'themes';

        // FIXME: make it better by detecting admin-ajax.php..
        $ajax = ($request->get['action'] == 'gantry5');
        $this->format = $ajax ? 'json' : 'html';

        $this->params = [
            'ajax' => $ajax,
            'location' => $this->resource,
            'method' => $this->method,
            'format' => $this->format,
            'params' => $request->post->getJsonArray('params')
        ];

        $this->setTemplate();

        return $this;
    }

    protected function makeUri($url)
    {
        $components = parse_url($url);

        $path     = isset($components['path']) ? $components['path'] : '';
        $query    = isset($components['query']) ? '?' . $components['query'] : '';
        $fragment = isset($components['fragment']) ? '#' . $components['fragment'] : '';

        return "{$path}{$query}{$fragment}";
    }

    public function setTemplate()
    {
        // FIXME: in here use pages.php, but in AJAX we need admin-ajax.php.
        $this->container['base_url'] = $this->makeUri(\admin_url( 'admin.php?page=layout-manager' ));

        $this->container['ajax_suffix'] = '&action=gantry5';

        // Create nonce
        $nonce = wp_create_nonce( 'gantry5-layout-manager' );

        $this->container['routes'] = [
            '1' => "&view=%s&_wpnonce={$nonce}",

            'themes' => "&view=themes&_wpnonce={$nonce}",
            'picker/layouts' => "&view=layouts&_wpnonce={$nonce}",
        ];

        $this->container['ajax_nonce'] = $nonce;

        return $this;
    }

    protected function checkSecurityToken()
    {
        // Check security nonce and return false on failure.
        if(check_admin_referer('gantry5-layout-manager')) {
            return true;
        }

        return false;
    }

    /**
     * Send response to the client.
     *
     * @param Response $response
     */
    protected function send(Response $response)
    {
        // Output HTTP header.
        header("HTTP/1.1 {$response->getStatus()}", true, $response->getStatusCode());
        header("Content-Type: {$response->mimeType}; charset={$response->charset}");
        foreach ($response->getHeaders() as $key => $values) {
            $replace = true;
            foreach ($values as $value) {
                header("{$key}: {$value}", $replace);
                $replace = false;
            }
        }

        if ($response instanceof JsonResponse) {
            // Output Gantry response.
            echo $response;
            die();
        }

        return $response;
    }
}
