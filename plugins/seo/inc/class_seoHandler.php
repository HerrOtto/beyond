<?php

class seoHandler
{

    private string $language;
    private string $prefix;
    private beyondDatabaseDriver $database;
    private beyondTools $tools;
    private beyondConfig $config;

    /**
     * Constructor
     * @param string $language Current user language
     * @param string $prefix Prefix
     * @param beyondDatabaseDriver $database Database connection of content plugin
     * @param beyondTools $tools Global beyond tools
     * @param beyondConfig $config Configuration
     */
    public function __construct($language, $prefix, $database, $tools, $config)
    {
        $this->language = $language;
        $this->prefix = $prefix;
        $this->database = $database;
        $this->tools = $tools;
        $this->config = $config;

        $this->cache = array();
    }

    /**
     * Get site header
     * @param mixed $language If "false" use current user language otherwise the specified
     * @result string Field content
     */
    public function printHead($language = false)
    {
        print PHP_EOL;

        try {

            $backfiles = debug_backtrace();
            $callerFileAndPath = $backfiles[0]['file'];
            $callerFile = basename($backfiles[0]['file']);
            $callerPath = pathinfo($callerFileAndPath, PATHINFO_DIRNAME);
            $baseDir = $this->config->get('base', 'server.absPath');

            // Get relative path to baseDir
            if (substr($callerPath, 0, strlen($baseDir)) !== $baseDir) {
                throw new Exception('Calling script [' . $callerFile . '] not in a subfolder of [' . $baseDir . ']');
            }
            $callerRelativePath = substr($callerPath, strlen($baseDir));

            // Check if the path is valid
            $dir = $this->tools->checkDirectory($callerRelativePath);
            if (!$dir['isValid']) {
                throw new Exception('Calling script [' . $callerFile . '] is not valid');
            }

            // Load default values
            $configJson = file_get_contents(__DIR__ . '/../../../config/seo_settings.json');
            if (trim($configJson) === '') {
                $configObj = new stdClass();
            } else {
                $configObj = json_decode($configJson);
            }

            // Getting file configuration from database
            $query = $this->database->select(
                $this->prefix . 'seo_data',
                array(
                    '*'
                ),
                array(
                    'filePathName = \'' . $this->database->escape(ltrim($dir['relPath'] . '/' . $callerFile, '/')) . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'seo_data]');
            }
            $fileData = false;
            if ($row = $query->fetch()) {
                $fileData = $row['dataJson'];
            }
            if (($fileData === false) || (trim($fileData) === '')) {
                throw new Exception('seo data not found');
            }

            // Language
            if ($language === false) {
                $language = $this->language;
            }

            // Decode stored data
            $fielConfigObj = json_decode($fileData);
            if (!property_exists($fielConfigObj, 'settings_' . $language)) {
                throw new Exception('seo fields for language [' . $language . '] not found');
            }

            // Title
            print '<title>';
            if (property_exists($configObj->{'defaults_' . $language}, 'titlePrefix')) {
                print $configObj->{'defaults_' . $language}->titlePrefix;
            }
            if (property_exists($fielConfigObj->{'settings_' . $language}, 'title')) {
                print htmlspecialchars($fielConfigObj->{'settings_' . $language}->title);
            }
            if (property_exists($configObj->{'defaults_' . $language}, 'titleSuffix')) {
                print $configObj->{'defaults_' . $language}->titleSuffix;
            }
            print '</title>' . PHP_EOL;

            // Author
            $author = '';
            if (property_exists($fielConfigObj->{'settings_' . $language}, 'author')) {
                $author = $fielConfigObj->{'settings_' . $language}->author;
            }
            if ((trim($author) === '') && (property_exists($configObj->{'defaults_' . $language}, 'author'))) {
                $author = $configObj->{'defaults_' . $language}->author;
            }
            if ((trim($author) === '') && (property_exists($configObj->{'defaults_default'}, 'author'))) {
                $author = $configObj->{'defaults_default'}->author;
            }
            if (trim($author) !== '') {
                print '<meta name="author" content="' . htmlspecialchars($author) . '" />' . PHP_EOL;
            }

            // Description
            $description = '';
            if (property_exists($fielConfigObj->{'settings_' . $language}, 'description')) {
                $description = $fielConfigObj->{'settings_' . $language}->description;
            }
            if ((trim($description) === '') && (property_exists($configObj->{'defaults_' . $language}, 'description'))) {
                $description = $configObj->{'defaults_' . $language}->description;
            }
            if ((trim($description) === '') && (property_exists($configObj->{'defaults_default'}, 'description'))) {
                $description = $configObj->{'defaults_default'}->description;
            }
            if (trim($description) !== '') {
                print '<meta name="description" content="' . htmlspecialchars($description) . '" />' . PHP_EOL;
            }

            // Description
            $robots = '';
            if (property_exists($fielConfigObj->{'settings_' . $language}, 'robots')) {
                $robots = $fielConfigObj->{'settings_' . $language}->robots;
            }
            if ((trim($robots) === '') && (property_exists($configObj->{'defaults_' . $language}, 'robots'))) {
                $robots = $configObj->{'defaults_' . $language}->robots;
            }
            if ((trim($robots) === '') && (property_exists($configObj->{'defaults_default'}, 'robots'))) {
                $robots = $configObj->{'defaults_default'}->robots;
            }
            if (trim($robots) !== '') {
                print '<meta name="robots" content="' . htmlspecialchars($robots) . '" />' . PHP_EOL;
            }

        } catch (Exception $e) {
            print "<!-- seo plugin exception: " . $e->getMessage() . ' -->' . PHP_EOL;
        }

        print PHP_EOL;
    }


}
