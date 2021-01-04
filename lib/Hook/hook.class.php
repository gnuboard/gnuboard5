<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * Library for handling hooks.
 *
 * @author    Josantonius <hello@josantonius.com>
 * @copyright 2017 (c) Josantonius - PHP-Hook
 * @license   https://opensource.org/licenses/MIT - The MIT License (MIT)
 * @link      https://github.com/Josantonius/PHP-Hook
 * @since     1.0.0
 */

/**
 * Hook handler.
 *
 * @since 1.0.0
 */
class Hook
{
    /**
     * Instance id.
     *
     * @since 1.0.5
     *
     * @var int
     */
    protected static $id = '0';

    /**
     * Callbacks.
     *
     * @since 1.0.3
     *
     * @var array
     */
    protected $callbacks = array();

    /**
     * Number of actions executed.
     *
     * @since 1.0.3
     *
     * @var array
     */
    protected $actions = array('count' => 0);

    /**
     * Current action hook.
     *
     * @since 1.0.3
     *
     * @var string|false
     */
    protected static $current = false;

    /**
     * Method to use the singleton pattern and just create an instance.
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected $singleton = 'getInstance';

    /**
     * Instances.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private static $instances = array();

    /**
     * Get instance.
     *
     * @since 1.0.0
     *
     * @param int $id
     *
     * @return object → instance
     */
    // 이부분 수정
    /*
    public static function getInstance($id = '0')
    {
        self::$id = $id;
        if (isset(self::$instances[self::$id])) {
            return self::$instances[self::$id];
        }

        return self::$instances[self::$id] = new self;
    }
    */

    public static function getInstance($id = '0')
    {
        self::$id = $id;
        if (isset(self::$instances[self::$id])) {
            return self::$instances[self::$id];
        }

        $calledClass = get_called_class();

        return self::$instances[self::$id] = new $calledClass;
    }

    /**
     * Attach custom function to action hook.
     *
     * @since 1.0.3
     *
     * @param string   $tag      → action hook name
     * @param callable $func     → function to attach to action hook
     * @param int      $priority → order in which the action is executed
     * @param int      $args     → number of arguments accepted
     *
     * @return bool
     */
    public static function addAction($tag, $func, $priority = 8, $args = 0)
    {
        $that = self::getInstance(self::$id);

        $that->callbacks[$tag][$priority][] = array(
            'function' => $func,
            'arguments' => $args,
        );

        return true;
    }

    /**
     * Add actions hooks from array.
     *
     * @since 1.0.3
     *
     * @param array $actions
     *
     * @return bool
     */
    public static function addActions($actions)
    {
        foreach ($actions as $arguments) {
            call_user_func_array(array(__CLASS__, 'addAction'), $arguments);
        }

        return true;
    }

    /**
     * Run all hooks attached to the hook.
     *
     * By default it will look for getInstance method to use singleton
     * pattern and create a single instance of the class. If it does not
     * exist it will create a new object.
     *
     * @see setSingletonName() for change the method name.
     *
     * @since 1.0.3
     *
     * @param string $tag    → action hook name
     * @param mixed  $args   → optional arguments
     * @param bool   $remove → delete hook after executing actions
     *
     * @return returns the output of the last action or false
     */
    public static function doAction($tag, $args = array(), $remove = true)
    {
        $that = self::getInstance(self::$id);
        
        self::$current = $tag;

        $that->actions['count']++;

        if (! array_key_exists($tag, $that->actions)) {
            $that->actions[$tag] = 0;
        }

        $that->actions[$tag]++;
        $actions = $that->getActions($tag, $remove);
        //asort($actions);
        // 이 부분 수정 priority 로 정렬 하려면 ksort를 써야함
        ksort($actions);

        foreach ($actions as $priority) {
            foreach ($priority as $action) {
                $action = $that->runAction($action, $args);
            }
        }

        self::$current = false;

        return (isset($action)) ? $action : false;
    }

    /**
     * Set method name for use singleton pattern.
     *
     * @since 1.0.0
     *
     * @param string $method → singleton method name
     */
    public static function setSingletonName($method)
    {
        $that = self::getInstance(self::$id);

        $that->singleton = $method;
    }

    /**
     * Returns the current action hook.
     *
     * @since 1.0.3
     *
     * @return string|false → current action hook
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * Check if there is a certain action hook.
     *
     * @since 1.0.7
     *
     * @param string $tag → action hook name
     *
     * @return bool
     */
    public static function isAction($tag)
    {
        $that = self::getInstance(self::$id);

        return isset($that->callbacks[$tag]);
    }

    /**
     * Run action hook.
     *
     * @since 1.0.3
     *
     * @param string $action → action hook
     * @param int    $args   → arguments
     *
     * @return callable|false → returns the calling function
     */
    protected function runAction($action, $args)
    {
        $function = $action['function'];
        $argsNumber = $action['arguments'];

        $class = (isset($function[0])) ? $function[0] : false;
        $method = (isset($function[1])) ? $function[1] : false;

        $args = $this->getArguments($argsNumber, $args);

        if (! ($class && $method) && function_exists($function)) {
            return call_user_func($function, $args);
        } elseif ($obj = call_user_func(array($class, $this->singleton))) {
            if ($obj !== false) {
                return call_user_func_array(array($obj, $method), $args);
            }
        } elseif (class_exists($class)) {
            $instance = new $class;

            return call_user_func_array(array($instance, $method), $args);
        }

        return null;
    }

    /**
     * Get actions for hook
     *
     * @since 1.0.3
     *
     * @param string $tag    → action hook name
     * @param bool   $remove → delete hook after executing actions
     *
     * @return object|false → returns the calling function
     */
    protected function getActions($tag, $remove)
    {
        if (isset($this->callbacks[$tag])) {
            $actions = $this->callbacks[$tag];
            if ($remove) {
                unset($this->callbacks[$tag]);
            }
        }

        return (isset($actions)) ? $actions : array();
    }

    /**
     * Get arguments for action.
     *
     * @since 1.0.3
     *
     * @param int   $argsNumber → arguments number
     * @param mixed $arguments  → arguments
     *
     * @return array → arguments
     */
    protected function getArguments($argsNumber, $arguments)
    {
        if ($argsNumber == 1 && is_string($arguments)) {
            return array($arguments);
        } elseif ($argsNumber === count($arguments)) {
            return $arguments;
        }

        for ($i = 0; $i < $argsNumber; $i++) {
            if (array_key_exists($i, $arguments)) {
                $args[] = $arguments[$i];
                continue;
            }

            return $args;
        }

        return array();
    }
}