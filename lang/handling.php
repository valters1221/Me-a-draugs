<?php
// handling.php

// Check if the class is already defined to prevent redeclaration errors
if (!class_exists('LanguageHandler')) {
    class LanguageHandler {
        private $currentLang;
        private $availableLangs = ['lv'];
        private $defaultLang = 'lv';
        private $translations = [];

        public function __construct() {
            // Only start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $this->initializeLanguage();
            $this->loadTranslations();
        }

        private function initializeLanguage() {
            // Check URL path first (this should override session)
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            foreach ($this->availableLangs as $lang) {
                if (strpos($path, "/$lang/") === 0 || $path === "/$lang") {
                    $this->setLanguage($lang);
                    return;
                }
            }

            // Check URL parameters (this should override session)
            if (isset($_GET['lang']) && in_array($_GET['lang'], $this->availableLangs)) {
                $this->setLanguage($_GET['lang']);
                return;
            }

            // Check session only if URL doesn't specify language
            if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->availableLangs)) {
                $this->setLanguage($_SESSION['lang']);
                return;
            }

            // Check browser language only on first visit
            if (!isset($_SESSION['lang'])) {
                $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
                if (in_array($browserLang, $this->availableLangs)) {
                    $this->setLanguage($browserLang);
                    return;
                }
            }

            // Use default language if nothing else matches
            $this->setLanguage($this->defaultLang);
        }

        private function loadTranslations() {
            $langFile = __DIR__ . "/{$this->currentLang}.php";
            if (file_exists($langFile)) {
                $this->translations = require $langFile;
            }
        }

        public function setLanguage($lang) {
            if (in_array($lang, $this->availableLangs)) {
                $this->currentLang = $lang;
                $_SESSION['lang'] = $lang;
                $this->loadTranslations();
            }
        }

        public function getCurrentLang() {
            return $this->currentLang;
        }

        public function translate($key) {
            $keys = explode('.', $key);
            $value = $this->translations;
            
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $key;
                }
                $value = $value[$k];
            }
            
            return $value;
        }

        public function redirectToLanguageUrl() {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // If URL already has correct language prefix, don't redirect
            if (preg_match("#^/{$this->currentLang}(/|$)#", $path)) {
                return;
            }
            
            // Remove any existing language prefix
            foreach ($this->availableLangs as $lang) {
                $path = preg_replace("#^/$lang(/|$)#", '/', $path);
            }
            
            // Build new URL with correct language
            $newPath = "/{$this->currentLang}" . ($path === '/' ? '' : $path);
            
            // Redirect to new URL
            header("Location: $newPath");
            exit;
        }
    }
}

// Create global translation function - only if not already defined
if (!function_exists('__')) {
    function __($key) {
        global $lang;
        return $lang->translate($key);
    }
}

// Initialize language handler if not already initialized
if (!isset($lang) || !($lang instanceof LanguageHandler)) {
    $lang = new LanguageHandler();
    $lang->redirectToLanguageUrl();
    $currentLang = $lang->getCurrentLang();
}