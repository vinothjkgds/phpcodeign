<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */
if (!function_exists('current_controller')) {
    /**
     * Returns the current controller name (lowercase)
     */
    function current_controller(): string
    {
        $router = service('router');
        $controller = $router->controllerName();
        // Strip namespace, keep only class name
        $parts = explode('\\', $controller);
        return strtolower(end($parts));
    }
}

if (!function_exists('current_method')) {
    /**
     * Returns the current controller method name (lowercase)
     */
    function current_method(): string
    {
        return strtolower(service('router')->methodName());
    }
}