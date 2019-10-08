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
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */
namespace Cartalyst\Sentinel\Tests;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Capsule\Manager as Capsule;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Cartalyst\Sentinel\Native\Facades\Sentinel as Sentinel;

class throttlingbugTest extends TestCase
{
    static $db;
    static $migrator;
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$db = $db = new Capsule;
        $db->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            //'driver'    => 'mysql',
            //'host'      => 'localhost',
            //'database'  => 'sentinel',
            //'username'  => 'root',
            //'password'  => '',
            //'charset'   => 'utf8',
            //'collation' => 'utf8_unicode_ci',

        ]);
        $db->setAsGlobal();
        $db->bootEloquent();
        $container = new Container;
        $container->instance('db', $db->getDatabaseManager());
        Facade::setFacadeApplication($container);
        self::$migrator = new Migrator(
            $repository = new DatabaseMigrationRepository($db->getDatabaseManager(), 'migrations'),
            $db->getDatabaseManager(),
            new Filesystem
        );
        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }
        self::$migrator->run([__DIR__ . '/../src/migrations']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        self::$migrator->reset([__DIR__ . '/../src/migrations']);
        self::$migrator->run([__DIR__ . '/../src/migrations']);
    }






    /** @test */
    public function addPermission_using_string()
    {
        $sentinel = $this->createSentinel();
        $credentials = [
            'email' => 'foo@bar.com',
            'password' => 'password'
        ];
        $user = $sentinel->registerAndActivate($credentials);
        $user->addPermission('roles.create');
        $user->save();
        $user = $sentinel->findByCredentials($credentials);

        $this->assertTrue($user->hasAccess('roles.create'));
    }

    /** @test */
    public function setPermissions_using_array()
    {
        $sentinel = $this->createSentinel();
        $credentials = [
            'email' => 'foo@bar.com',
            'password' => 'password'
        ];
        $user = $sentinel->registerAndActivate($credentials);
        $user->setPermissions(['roles.create' => true]);
        $user->save();
        $user = $sentinel->findByCredentials($credentials);

        $this->assertTrue($user->hasAccess('roles.create'));
    }

    /** @test */
    public function set_permissions_directly_using_array()
    {
        $sentinel = $this->createSentinel();
        $credentials = [
            'email' => 'foo@bar.com',
            'password' => 'password'
        ];
        $user = $sentinel->registerAndActivate($credentials);
        $user->permissions = ['roles.create' => true];
        $user->save();
        $user = $sentinel->findByCredentials($credentials);

        $this->assertTrue($user->hasAccess('roles.create'));
    }

    /** @test */
    public function set_permissions_directly_using_string()
    {
        $sentinel = $this->createSentinel();
        $credentials = [
            'email' => 'foo@bar.com',
            'password' => 'password'
        ];
        $user = $sentinel->registerAndActivate($credentials);
        $user->permissions = 'roles.create';
        $user->save();
        $user = $sentinel->findByCredentials($credentials);

        $this->expectException(\TypeError::class);
        $user->hasAccess('roles.create');
    }




    protected function createSentinel()
    {
        $bootstrap = new SentinelBootStrapper();
        return $bootstrap->createSentinel();
    }
}
