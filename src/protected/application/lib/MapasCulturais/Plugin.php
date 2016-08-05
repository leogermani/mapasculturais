<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

abstract class Plugin {
    use Traits\MagicGetter,
        Traits\MagicSetter;
<<<<<<< HEAD

    protected $_config;

    final function __construct(array $config = []) {
        $this->_config = $config;

=======
    
    protected $_config;
    
    final function __construct(array $config = []) {
        $this->_config = $config;
        
>>>>>>> master
        $app = App::i();
        $active_theme = $app->view;
        $class = get_called_class();
        $reflaction = new \ReflectionClass($class);
<<<<<<< HEAD

        while($reflaction->getName() != __CLASS__){
            $dir = dirname($reflaction->getFileName());
            $active_theme->addPath($dir);

            $reflaction = $reflaction->getParentClass();
        }

=======
        
        while($reflaction->getName() != __CLASS__){
            $dir = dirname($reflaction->getFileName());
            $active_theme->addPath($dir);
            
            $reflaction = $reflaction->getParentClass();
        }
        
>>>>>>> master
        $app->applyHookBoundTo($this, "plugin({$class}).init:before");
        $this->_init();
        $app->applyHookBoundTo($this, "plugin({$class}).init:after");
    }
<<<<<<< HEAD

    function getConfig(){
        return $this->_config;
    }

    abstract function _init();

    abstract function register();
}
=======
    
    function getConfig(){
        return $this->_config;
    }
    
    abstract function _init();
    
    abstract function register();
}
>>>>>>> master
