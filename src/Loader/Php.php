<?php
declare(strict_types=1);

namespace Orbital\I18n\Loader;

use \Exception;

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
    public function setFile(string $file): void {
        $this->file = $file;
    }

    /**
     * Retrieve file source
     * @return string
     */
    public function getFile(): string {
        return $this->file;
    }

    /**
     * Retrieve scope for file
     * @return string
     */
    public function getScope(): string {

        $scope = str_replace('.php', '', $this->getFile());
        $scope = explode('/', $scope);
        $scope = end($scope);

        return $scope;
    }

    /**
     * Read PHP file and return it texts content
     * @throws Exception
     * @return array
     */
    public function retrieveTexts(): array {

        $texts = include $this->getFile();

        if( !is_array($texts) ){
            $message = $file. ' is not a valid PHP translator file.';
            throw new Exception($message);
        }

        return $texts;
    }

}