<?php

namespace Orbital\I18n\Loader;

class Php {

    /**
     * File
     * @var string
     */
    protected $file;

    /**
     * Set file source
     * @param string $file
     * @return void
     */
    public function setFile($file){
        $this->file = $file;
    }

    /**
     * Retrieve file source
     * @return string
     */
    public function getFile(){
        return $this->file;
    }

    /**
     * Retrieve scope for file
     * @return string
     */
    public function getScope(){

        $scope = str_replace('.php', '', $this->getFile());
        $scope = explode('/', $scope);
        $scope = end($scope);

        return $scope;
    }

    /**
     * Read PHP file and return it texts content
     * @return array
     */
    public function retrieveTexts(){

        $texts = include $this->getFile();

        if( !is_array($texts) ){
            $message = $file. ' is not a valid PHP translator file.';
            throw new \Exception($message);
        }

        return $texts;
    }

}