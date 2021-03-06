<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Gantry\Framework;

use Gantry\Framework\Base\Theme as BaseTheme;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Theme extends BaseTheme
{
    protected $joomla = false;
    protected $renderer;

    public function __construct($path, $name = '')
    {
        parent::__construct($path, $name);

        $this->url = \JUri::root(true) . '/templates/' . $this->name;
    }

    public function init()
    {
        parent::init();

        \JPluginHelper::importPlugin('gantry5');

        // Trigger the onGantryThemeInit event.
        $dispatcher = \JEventDispatcher::getInstance();
        $dispatcher->trigger('onGantry5ThemeInit', ['theme' => $this]);

        if (\JFactory::getApplication()->isSite()) {
            $gantry = Gantry::instance();

            /** @var UniformResourceLocator $locator */
            $locator = $gantry['locator'];

            // Load our custom positions file as frontend requires the strings to be there.
            $lang = \JFactory::getLanguage();
            $filename = $locator("gantry-theme://language/en-GB/en-GB.tpl_{$this->name}_positions.ini");

            if ($filename) {
                $lang->load("tpl_{$this->name}_positions", dirname(dirname(dirname($filename))), 'en-GB');
            }
        }
    }

    public function renderer()
    {
        if (!$this->renderer) {
            $gantry = Gantry::instance();

            /** @var UniformResourceLocator $locator */
            $locator = $gantry['locator'];

            $loader = new \Twig_Loader_Filesystem($locator->findResources('gantry-engine://twig'));

            $params = array(
                'cache' => $locator->findResource('gantry-cache://theme/twig', true, true),
                'debug' => true,
                'auto_reload' => true,
                'autoescape' => 'html'
            );

            // Get user timezone and if not set, use Joomla default.
            $timezone = \JFactory::getUser()->getParam('timezone', \JFactory::getConfig()->get('offset', 'UTC'));

            $twig = new \Twig_Environment($loader, $params);
            $twig->getExtension('core')->setTimezone(new \DateTimeZone($timezone));

            $this->add_to_twig($twig);

            $doc = \JFactory::getDocument();
            $this->language = $doc->language;
            $this->direction = $doc->direction;

            $this->renderer = $twig;
        }

        return $this->renderer;
    }

    public function render($file, array $context = array())
    {
        // Include Gantry specific things to the context.
        $context = $this->add_to_context($context);

        return $this->renderer()->render($file, $context);
    }

    public function debug()
    {
        return JDEBUG;
    }

    public function joomla($enable = null)
    {
        if ($enable) {
            // Workaround for Joomla! not loading bootstrap when it needs it.
            \JHtml::_('bootstrap.framework');

            $this->joomla = (bool) $enable;
        }

        return $this->joomla;
    }
}
