<?php
namespace Flickering;

use Illuminate\Cache\FileStore;
use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

class Flickering
{
  /**
   * The API key
   * @var string
   */
  protected $key;

  /**
   * The API secret key
   * @var string
   */
  protected $secret;

  /**
   * The Illuminate Container
   * @var Container
   */
  protected static $container;

  /**
   * Setup an instance of the API
   *
   * @param string $key    The API key
   * @param string $secret The API secret key
   */
  public function __construct($key = null, $secret = null)
  {
    $this->key    = $key    ?: $this->getOption('api_key');
    $this->secret = $secret ?: $this->getOption('api_secret');
  }

  /**
   * Call a method on the current API
   *
   * @param string $method     The method name
   * @param array  $parameters Its parameters
   *
   * @return Method
   */
  public function callMethod($method, $parameters = array())
  {
    return new Method($this, $method, $parameters);
  }

  /**
   * Directly get the results of a method
   *
   * @param string $method     The method name
   * @param array  $parameters Its parameters
   *
   * @return Results
   */
  public function getResultsOf($method, $parameters = array())
  {
    return $this->callMethod($method, $parameters)->getResults();
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// INTERFACE ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the user's API key
   *
   * @return string
   */
  public function getApiKey()
  {
    return $this->key;
  }

  /**
   * Get authentified user
   *
   * @return string
   */
  public function getUser()
  {
    return null;
  }

  /**
   * Get an option from the config file
   *
   * @param string $option   The option to fetch
   * @param mixed  $fallback A fallback
   *
   * @return mixed
   */
  public function getOption($option, $fallback = null)
  {
    return $this->getConfig()->get('config.'.$option, $fallback);
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// DEPENDENCIES ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Build required dependencies
   */
  protected function getDependency($dependency = null)
  {
    // If no Container available, build one
    if (!static::$container) {
      $container = new Container;

      $container->bind('Filesystem', 'Illuminate\Filesystem\Filesystem');
      $container->bind('FileLoader', function($container) {
        return new FileLoader($container['Filesystem'], __DIR__.'/../..');
      });

      $container->bind('config', function($container) {
        return new Repository($container['FileLoader'], 'config');
      });

      $container->bind('cache', function($container) {
        return new FileStore($container->make('Filesystem'), __DIR__.'/../../cache');
      });

      static::$container = $container;
    }

    // If we provided a dependency, make it on the go
    if ($dependency) {
      return static::$container->make($dependency);
    }

    return static::$container;
  }

  /**
   * Get the Cache instance
   *
   * @return Cache
   */
  protected function getCache()
  {
    return $this->getDependency('cache');
  }

  /**
   * Get the Config instance
   *
   * @return Config
   */
  protected function getConfig()
  {
    return $this->getDependency('config');
  }
}
