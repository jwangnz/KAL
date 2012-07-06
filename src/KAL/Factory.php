<?php

class KAL_Factory {
    private static $instance;

    private $loader;
    private $kindClassRefl;

    private function __construct(KAL_ConfigLoaderInterface $loader, $kind_class = "KAL_Kind") {
        $this->loader = $loader;

        $refl = new ReflectionClass($kind_class);
        if (! $refl->implementsInterface("KAL_KindInterface")) {
            throw new InvalidArgumentException('argument 2 should be calss implements "KAL_KindInterface"');
        }
        $this->kindClassRefl = $refl;
    }

    public static function init(KAL_ConfigLoaderInterface $loader, $kindClass = "KAL_Kind") {
        if (! self::$instance) {
            self::$instance = new KAL_Factory($loader, $kindClass);
        }
        return self::$instance;
    }

    public static function getKind($kind_name) {
        if (!self::$instance) {
            throw new Exception("KAL_Factory is not inited, call KAL_Facoty::init first,");
        }
        return self::$instance->loadKind($kind_name);
    }

    private function loadKind($kind_name) {
        $config = $this->loadKindConfig($kind_name);
        return $this->kindClassRefl->newInstanceArgs(array($kind_name, $config));
    }

    private function loadKindConfig($kind_name) {
        $config = $this->loader->loadKindConfig($kind_name);
        if (! $config instanceof KAL_ConfigInterface) {
            throw new Exception('loadConfig for kind: "'.$kind_name.'" failed');
        }
        return $config;
    }
}
