<?php
declare(strict_types=1);

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
    public static function getLanguage(): string {
        return self::$language;
    }

    /**
     * Retrieve language scope
     * @return string
     */
    public static function getScope(): string {
        return self::$scope;
    }

    /**
     * Set active language
     * @param string $code
     * @return void
     */
    public static function setLanguage(string $code): void {
        self::$language = $code;
    }

    /**
     * Set active language scope
     * @param string $scope
     * @return void
     */
    public static function setScope(string $scope): void {
        self::$scope = $scope;
    }

    /**
     * Load language translations
     * @param string $language
     * @return void
     */
    public static function load(string $language = null): void {

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
    public static function add(string $language, string $scope, array $texts = array()): void {

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
    public static function translate(string $string, array $placeholders = array(), string $scope = null, string $language = null): string {

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
     * @param string $scope
     * @param string $language
     * @return string
     */
    public static function __(string $string, array $placeholders = array(), string $scope = null, string $language = null): string {
        return self::translate($string, $placeholders, $scope, $language);
    }

    /**
     * Translate string into language and print it
     * @param string $string
     * @param array $placeholders
     * @param string $scope
     * @param string $language
     * @return void
     */
    public static function _e(string $string, array $placeholders = array(), string $scope = null, string $language = null): void {
        echo self::translate($string, $placeholders, $scope, $language);
    }

}