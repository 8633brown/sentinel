<?php

/*
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    3.0.1
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Permissions;

class StandardPermissions implements PermissionsInterface
{
    use PermissionsTrait;

    /**
     * {@inheritdoc}
     */
    protected function createPreparedPermissions(): array
    {
        $prepared = [];

        if (! empty($this->secondaryPermissions)) {
            foreach ($this->secondaryPermissions as $permissions) {
                $this->preparePermissions($prepared, $permissions);
            }
        }

        if (! empty($this->permissions)) {
            $permissions = [];

            $this->preparePermissions($permissions, $this->permissions);

            $prepared = array_merge($prepared, $permissions);
        }

        return $prepared;
    }
}
