<?php

namespace MABI;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Illuminate\Cache\Repository;

/**
 * An Illuminate\Cache aware annotation reader. Based on Doctrine\Common\Annotations\CachedReader
 */
final class AnnotationReader extends SimpleAnnotationReader {
  /**
   * @var string
   */
  private static $CACHE_SALT = '@[Annot]';

  /**
   * @var Repository
   */
  private $cacheRepository;

  /**
   * @var boolean
   */
  private $debug;

  /**
   * @var array
   */
  private $loadedAnnotations;

  /**
   * Constructor
   */
  public function __construct($debug = FALSE) {
    parent::__construct();
    include_once __DIR__ . '/Annotations/Middleware.php';
    $this->addNamespace('MABI\Annotations');
    $this->debug = (Boolean) $debug;
  }

  public function addNamespace($namespace) {
    parent::addNamespace($namespace);
  }

  /**
   * @param \Illuminate\Cache\Repository $cacheRepository
   */
  public function setCacheRepository($cacheRepository) {
    $this->cacheRepository = $cacheRepository;
  }

  /**
   * @return \Illuminate\Cache\Repository
   */
  public function getCacheRepository() {
    return $this->cacheRepository;
  }

  /**
   * Get annotations for class
   *
   * @param \ReflectionClass $class
   *
   * @return array
   */
  public function getClassAnnotations(\ReflectionClass $class) {
    $cacheKey = $class->getName();

    if (isset($this->loadedAnnotations[$cacheKey])) {
      return $this->loadedAnnotations[$cacheKey];
    }

    if (FALSE === ($annots = $this->fetchFromCache($cacheKey, $class))) {
      $annots = parent::getClassAnnotations($class);
      $this->saveToCache($cacheKey, $annots);
    }

    return $this->loadedAnnotations[$cacheKey] = $annots;
  }

  /**
   * Get selected annotation for class
   *
   * @param \ReflectionClass $class
   * @param string           $annotationName
   *
   * @return null
   */
  public function getClassAnnotation(\ReflectionClass $class, $annotationName) {
    foreach ($this->getClassAnnotations($class) as $annot) {
      if ($annot instanceof $annotationName) {
        return $annot;
      }
    }

    return NULL;
  }

  /**
   * Get annotations for property
   *
   * @param \ReflectionProperty $property
   *
   * @return array
   */
  public function getPropertyAnnotations(\ReflectionProperty $property) {
    $class    = $property->getDeclaringClass();
    $cacheKey = $class->getName() . '$' . $property->getName();

    if (isset($this->loadedAnnotations[$cacheKey])) {
      return $this->loadedAnnotations[$cacheKey];
    }

    if (FALSE === ($annots = $this->fetchFromCache($cacheKey, $class))) {
      $annots = parent::getPropertyAnnotations($property);
      $this->saveToCache($cacheKey, $annots);
    }

    return $this->loadedAnnotations[$cacheKey] = $annots;
  }

  /**
   * Get selected annotation for property
   *
   * @param \ReflectionProperty $property
   * @param string              $annotationName
   *
   * @return null
   */
  public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName) {
    foreach ($this->getPropertyAnnotations($property) as $annot) {
      if ($annot instanceof $annotationName) {
        return $annot;
      }
    }

    return NULL;
  }

  /**
   * Get method annotations
   *
   * @param \ReflectionMethod $method
   *
   * @return array
   */
  public function getMethodAnnotations(\ReflectionMethod $method) {
    $class    = $method->getDeclaringClass();
    $cacheKey = $class->getName() . '#' . $method->getName();

    if (isset($this->loadedAnnotations[$cacheKey])) {
      return $this->loadedAnnotations[$cacheKey];
    }

    if (FALSE === ($annots = $this->fetchFromCache($cacheKey, $class))) {
      $annots = parent::getMethodAnnotations($method);
      $this->saveToCache($cacheKey, $annots);
    }

    return $this->loadedAnnotations[$cacheKey] = $annots;
  }

  /**
   * Get selected method annotation
   *
   * @param \ReflectionMethod $method
   * @param string            $annotationName
   *
   * @return null
   */
  public function getMethodAnnotation(\ReflectionMethod $method, $annotationName) {
    foreach ($this->getMethodAnnotations($method) as $annot) {
      if ($annot instanceof $annotationName) {
        return $annot;
      }
    }

    return NULL;
  }

  /**
   * Clear loaded annotations
   */
  public function clearLoadedAnnotations() {
    $this->loadedAnnotations = array();
  }

  /**
   * Fetches a value from the cache.
   *
   * @param string           $rawCacheKey The cache key.
   * @param \ReflectionClass $class       The related class.
   *
   * @return mixed|boolean The cached value or false when the value is not in cache.
   */
  private function fetchFromCache($rawCacheKey, \ReflectionClass $class) {
    if (empty($this->cacheRepository)) {
      return FALSE;
    }

    $cacheKey = $rawCacheKey . self::$CACHE_SALT;
    if (($data = $this->cacheRepository->get($cacheKey)) !== NULL) {
      if (!$this->debug || $this->isCacheFresh($cacheKey, $class)) {
        return $data;
      }
    }

    return FALSE;
  }

  /**
   * Saves a value to the cache
   *
   * @param string $rawCacheKey The cache key.
   * @param mixed  $value       The value.
   */
  private function saveToCache($rawCacheKey, $value) {
    if (empty($this->cacheRepository)) {
      return;
    }

    $cacheKey = $rawCacheKey . self::$CACHE_SALT;
    $this->cacheRepository->forever($cacheKey, $value);
    if ($this->debug) {
      $this->cacheRepository->forever('[C]' . $cacheKey, time());
    }
  }

  /**
   * Check if cache is fresh
   *
   * @param string           $cacheKey
   * @param \ReflectionClass $class
   *
   * @return bool
   */
  private function isCacheFresh($cacheKey, \ReflectionClass $class) {
    if (FALSE === $filename = $class->getFilename()) {
      return TRUE;
    }

    return $this->cacheRepository->get('[C]' . $cacheKey) >= filemtime($filename);
  }
}
