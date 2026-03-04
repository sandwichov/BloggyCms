<?php
trait APIAware {
    
    public static function getModelInfo() {
        $class = get_called_class();
        $reflection = new ReflectionClass($class);
        $path = $reflection->getFileName();
        $dirName = basename(dirname($path));
        
        return [
            'name' => $dirName,
            'class' => $class,
            'path' => $path,
        ];
    }
    
    public function getAPIMethods() {

        if (property_exists($this, 'allowedAPIMethods') && !empty($this->allowedAPIMethods)) {
            return $this->allowedAPIMethods;
        }

        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        $result = [];
        foreach ($methods as $method) {
            if (strpos($method->name, '__') !== 0 && 
                $method->name !== '__construct' &&
                $method->name !== 'getAPIMethods' &&
                $method->name !== 'callAPI' &&
                $method->name !== 'getModelInfo') {
                $result[] = $method->name;
            }
        }
        return $result;
    }
    
    public function callAPI($method, $args = []) {
        $methods = $this->getAPIMethods();
        
        if (!in_array($method, $methods)) {
            throw new Exception("API method '{$method}' not allowed in " . get_class($this));
        }
        
        if (!method_exists($this, $method)) {
            throw new Exception("Method '{$method}' does not exist in " . get_class($this));
        }
        
        return call_user_func_array([$this, $method], $args);
    }
}