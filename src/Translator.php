<?php

namespace Orbital\I18n;

abstract class Translator {

    /**
     * Language
     * @var string
     */
    private static $language = 'en_US';

    /**
     * Language scope
     * @var string
     */
    private static $scope = 'default';

    /**
     * Translations texts
     * @var array
     */
    public static $texts = array();

    /**
     * Retrieve language
     * @return string
     */
    public static function getLanguage(){
        return self::$language;
    }

    /**
     * Retrieve language scope
     * @return string
     */
    public static function getScope(){
        return self::$scope;
    }

    /**
     * Set active language
     * @param string $code
     * @return void
     */
    public static function setLanguage($code){
        self::$language = $code;
    }

    /**
     * Set active language scope
     * @param string $scope
     * @return void
     */
    public static function setScope($scope){
        self::$scope = $scope;
    }

    /**
     * Load language translations
     * @param string $language
     * @return void
     */
    public static function load($language = NULL){

        if( is_null($language) ){
            $language = self::getLanguage();
        }

        $directory = BASE. 'i18n'. DS. $language;

        // Load PHP files
        foreach( glob($directory. DS. '*.php') as $file ){

            if( !file_exists($file) ){
                continue;
            }

            $loader = new \Orbital\I18n\Loader\Php;
            $loader->setFile($file);

            $texts = $loader->retrieveTexts();
            $scope = $loader->getScope();

            if( is_array($texts) AND $texts ){
                self::add($language, $scope, $texts);
            }

        }

        // Load MO files
        foreach( glob($directory. DS. '*.mo') as $file ){

            if( !file_exists($file) ){
                continue;
            }

            $loader = new \Orbital\I18n\Loader\Mo;
            $loader->setFile($file);

            $texts = $loader->retrieveTexts();
            $scope = $loader->getScope();

            if( is_array($texts) AND $texts ){
                self::add($language, $scope, $texts);
            }

        }

    }

    /**
     * Add translations to language
     * @param string $language
     * @param string $scope
     * @param array $texts
     * @return void
     */
    public static function add($language, $scope, $texts = array()){

        if( !isset(self::$texts[$language]) ){
            self::$texts[$language] = array();
        }

        if( !isset(self::$texts[$language][$scope]) ){
            self::$texts[$language][$scope] = array();
        }

        self::$texts[$language][$scope] = array_merge(
            self::$texts[$language][$scope],
            $texts
        );

    }

    /**
     * Translate string into language
     * @param string $string
     * @param array $placeholders
     * @param string $scope
     * @param string $language
     * @return string
     */
    public static function translate($string, $placeholders = array(), $scope = NULL, $language = NULL){

        if( is_null($language) ){
            $language = self::getLanguage();
        }

        if( is_null($scope) ){
            $scope = self::getScope();
        }

        if( !isset(self::$texts[$language]) ){
            self::load($language);
        }

        if( !empty(self::$texts[$language])
            AND !empty(self::$texts[$language][$scope])
            AND isset(self::$texts[$language][$scope][$string]) ){
            $text = self::$texts[$language][$scope][$string];
        }else{
            $text = $string;
        }

        if( is_array($placeholders)
            AND $placeholders ){
            foreach( $placeholders as $key => $value ){
                $text = str_replace($key, $value, $text);
            }
        }

        return $text;
    }

    /**
     * Translate string into language and return it
     * @param string $string
     * @param array $placeholders
     * @param mixed $scope
     * @param mixed $language
     * @return string
     */
    public static function __($string, $placeholders = array(), $scope = NULL, $language = NULL){
        return self::translate($string, $placeholders, $scope, $language);
    }

    /**
     * Translate string into language and print it
     * @param string $string
     * @param array $placeholders
     * @param mixed $scope
     * @param mixed $language
     * @return void
     */
    public static function _e($string, $placeholders = array(), $scope = NULL, $language = NULL){
        echo self::translate($string, $placeholders, $scope, $language);
    }

}