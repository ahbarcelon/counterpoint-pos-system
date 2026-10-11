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

if (! function_exists('pos_money')) {
    /** Format a stored DECIMAL amount without converting it through a float. */
    function pos_money(string $amount): string
    {
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', trim($amount), $parts)) {
            return $amount;
        }

        return number_format((int) $parts[1], 0, '.', ',')
            . '.' . str_pad($parts[2] ?? '0', 2, '0');
    }
}
