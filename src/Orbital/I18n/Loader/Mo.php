<?php

namespace Orbital\I18n\Loader;

class Mo {

    /**
     * File
     * @var string
     */
    protected $file;

    /**
     * File Resource
     * @var resource
     */
    protected $resource;

    /**
     * littleEndian
     * @var boolean
     */
    protected $littleEndian;

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

        $scope = str_replace('.mo', '', $this->getFile());
        $scope = explode('/', $scope);
        $scope = end($scope);

        return $scope;
    }

    /**
     * Read MO file and return it texts content
     * @return array
     */
    public function retrieveTexts(){

        $texts = array();
        $file = $this->getFile();

        $this->resource = fopen($file, 'rb');

        if( FALSE === $this->resource ){
            $message = 'Could not open file '. $file. ' for reading.';
            throw new \Exception($message);
        }

        // Verify magic number
        $magic = fread($this->resource, 4);

        if( $magic == "\x95\x04\x12\xde" ){
            $this->littleEndian = FALSE;
        }elseif( $magic == "\xde\x12\x04\x95" ){
            $this->littleEndian = TRUE;
        }else{
            fclose($this->resource);
            $message = $file. ' is not a valid gettext MO file.';
            throw new \Exception($message);
        }

        // Verify major revision (only 0 and 1 supported)
        $majorRevision = $this->readResourceInteger();
        $majorRevision = ($majorRevision >> 16);

        if( $majorRevision !== 0
            AND $majorRevision !== 1 ){

            fclose($this->resource);
            $message = $file. ' has an unknown major revision.';
            throw new Exception($message);
        }

        // Gather main information
        $numStrings = $this->readResourceInteger();
        $originalTableOffset = $this->readResourceInteger();
        $translationTableOffset = $this->readResourceInteger();

        // Usually there follow size and offset of the hash table,
        // but we have no need for it, so we skip them.
        fseek($this->resource, $originalTableOffset);
        $originalTable = $this->readResourceIntegerList(2 * $numStrings);

        fseek($this->resource, $translationTableOffset);
        $translationTable = $this->readResourceIntegerList(2 * $numStrings);

        // Read in all translations
        for( $current = 0; $current < $numStrings; $current++ ){

            $sizeKey = $current * 2 + 1;
            $offsetKey = $current * 2 + 2;

            $originalSize = $originalTable[$sizeKey];
            $originalOffset = $originalTable[$offsetKey];
            $originalString = [''];

            $translationSize = $translationTable[$sizeKey];
            $translationOffset = $translationTable[$offsetKey];

            if( $originalSize > 0 ){
                fseek($this->resource, $originalOffset);
                $originalString = explode("\0", fread($this->resource, $originalSize));
            }

            if( $translationSize > 0 ){
                fseek($this->resource, $translationOffset);
                $translationString = explode("\0", fread($this->resource, $translationSize));

                if( count($originalString) > 1
                    AND count($translationString) > 1 ){

                    $texts[ $originalString[0] ] = $translationString;
                    array_shift($originalString);

                    foreach( $originalString as $string ){
                        if( !isset($texts[$string]) ){
                            $texts[ $string ] = '';
                        }
                    }

                }else{
                    $texts[ $originalString[0] ] = $translationString[0];
                }

            }

        }

        if( array_key_exists('', $texts) ){
            unset($texts['']);
        }

        fclose($this->resource);

        return $texts;
    }

    /**
     * Read a single integer from resource file
     * @return int
     */
    protected function readResourceInteger(){

        if( $this->littleEndian ){
            $result = unpack('Vint', fread($this->resource, 4));
        }else{
            $result = unpack('Nint', fread($this->resource, 4));
        }

        return $result['int'];
    }

    /**
     * Read an integer list from resource file
     * @param int $number
     * @return int
     */
    protected function readResourceIntegerList($number){

        if( $this->littleEndian ){
            return unpack('V' . $number, fread($this->resource, 4 * $number));
        }

        return unpack('N' . $number, fread($this->resource, 4 * $number));
    }

}