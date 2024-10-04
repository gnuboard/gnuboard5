<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once(G5_LIB_PATH . '/Hook/hook.class.php');
include_once(G5_LIB_PATH . '/Hook/hook.extends.class.php');

const G5_HOOK_DEFAULT_PRIORITY = 8;

class ContainerHook extends GML_Hook
{
    /**
     * @param array $action
     * @param $args
     * @return mixed|void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function runAction($action, $args)
    {
        /**
         * @var \Slim\App<\Psr\Container\ContainerInterface> $app
         */
        global $app;
        $function = $action['function'];
        $args_number = $action['arguments'];

        $class = (is_array($function) && isset($function[0])) ? $function[0] : false;
        $method = (is_array($function) && isset($function[1])) ? $function[1] : false;
        $args = $this->getArguments($args_number, $args);

        if (!($class && $method) && is_callable($function)) {
            return call_user_func_array($function, $args);
        }

        if ($obj = $app->getContainer()->get($action['function'][0])) {
            return call_user_func_array([$obj, $method], $args);
        }

        if (class_exists($class)) {
            $instance = new $class;

            return call_user_func_array([$instance, $method], $args);
        }
    }
}
