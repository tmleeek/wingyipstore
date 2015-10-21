<?php
/**
 * @category    Bubble
 * @package     Bubble_CodeReview
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
require_once 'abstract.php';
/**
 * Class Bubble_Shell_CodeReview
 *
 * @method Bubble_Output_Renderer_Abstract output(string $str)
 * @method Bubble_Output_Renderer_Abstract start(string $str)
 * @method Bubble_Output_Renderer_Abstract success(string $str)
 * @method Bubble_Output_Renderer_Abstract error(string $str)
 * @method Bubble_Output_Renderer_Abstract bold(string $str)
 * @method Bubble_Output_Renderer_Abstract red(string $str)
 * @method Bubble_Output_Renderer_Abstract green(string $str)
 * @method Bubble_Output_Renderer_Abstract pad(string $str, int $length, int $type = STR_PAD_RIGHT)
 * @method Bubble_Output_Renderer_Abstract br()
 * @method Bubble_Output_Renderer_Abstract wash($str)
 */
class Bubble_Shell_CodeReview extends Mage_Shell_Abstract
{
    const VERSION = '1.1.0';

    const REGEX_TEMPLATES = '#\$GLOBALS|\$_SESSION|\$_ENV|\$_SERVER|\$_REQUEST|\$_COOKIE|\$_GET|\$_POST|\$_FILES|mysql_.*\(.*\)|var_dump\(.+\)|var_export\(.+\)|print_r\(.+\)|exit\(.*\)|die\(.*\)|Zend_Debug|(\s|\t)mail\([^\)]+,[^\)]+,[^\)]+\)|header\(.+\)|new\s+.+\(.*\)#';

    const REGEX_MODULES = '#\$GLOBALS|\$_SESSION|\$_ENV|\$_SERVER|\$_REQUEST|\$_COOKIE|\$_GET|\$_POST|\$_FILES|mysql_.*\(.*\)|var_dump\(.+\)|var_export\(.+\)|print_r\(.+\)|exit\(.*\)|die\(.*\)|Zend_Debug|(\s|\t)mail\([^\)]+,[^\)]+,[^\)]+\)|new\s+(?!Zend_|Varien_|.*PHPUnit_)[a-zA-Z]+_[a-zA-Z_]+\(.*\)#';

    protected $_appCode = 'default';

    protected $_config = null;

    protected $_renderer = null;

    protected $_capture = '';

    protected $_ignoreFiles = array(
        'app/code/local/Bubble/CodeReview/Model/Observer.php',
        'app/code/local/Zend/Db/Adapter/Pdo/Abstract.php',
    );

    protected $_count = 2;

    protected $_sectionLength = 120;

    public function __construct()
    {
        try {
            $_COOKIE = array(); // prevent potential cookies present in browser
            $this->_parseArgs();
            if (false !== $this->getArg('store')) {
                $this->_appCode = $this->getArg('store');
            }
            parent::__construct();
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::throwException('You cannot use admin store');
            }
            $this->_config = Mage::getConfig();
            $this->_renderer = $this->_getOutputRenderer();
            $this->_renderer->registerCallback($this, 'captureOutput');
            if (false !== $this->getArg('count')) {
                $this->_count = max(1, $this->getArg('count'));
            }
            set_time_limit(0);
        } catch (Mage_Core_Model_Store_Exception $e) {
            exit(sprintf('Could not initialize store "%s"', $this->_appCode));
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function __call($method, $args)
    {
        if ($this->_renderer) {
            return call_user_func_array(array($this->_renderer, $method), $args);
        }

        return $this;
    }

    public function run()
    {
        try {
            $this->section('STARTING CODE REVIEW');
            $start = microtime(true);
            $this->_copyright();
            $this->_serverInfo();
            $this->_cacheInfo();
            $this->_listStores();
            $this->_configInfo();
            $this->_miscInfo();
            $this->_countModules();
            $this->_checkRewrites();
            $this->_inspectLocalModules();
            $this->_inspectTheme();
            if (!$this->getArg('url')) {
                $this->_testHomepage();
                $this->_testCategoryPage();
                $this->_testProductPage();
            } else {
                $this->section('Debugging Custom URL');
                $url = $this->getArg('url');
                $this->_get($url); // First call to prevent cache not being generated
                $this->_callUrl($url);
                $this->_debugUrl($url);
            }
            $this->_checkForbiddenUrls();
            $end = microtime(true);
            $this->section('END');
            $this->output(sprintf('Duration: %ss', round($end - $start, 2)));
            if (!$this->getArg('no-report')) {
                $this->_saveReview();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->error($e->getMessage(), false, true);
        }
    }

    protected function _callUrl($url, $expecting = array(200))
    {
        $expecting = (array) $expecting;
        $info = $this->_get($url, false, false);
        $code = $info['http_code'];
        $output = sprintf(
            '%s %s %ss',
            in_array($code, $expecting) ? $this->green($code) : $this->red($code),
            $this->pad($url, 70),
            $this->pad(round($info['total_time'], 3), 5, STR_PAD_LEFT)
        );
        $this->output($output);

        return $this;
    }

    protected function _debugUrl($url)
    {
        $json = $this->_get($url);
        $result = json_decode($json, true);
        $this->output(sprintf('SQL Queries: %d', count($result['sql']['queries'])));

        // Compiling SQL debug information
        $debug = array();
        foreach ($result['sql']['queries'] as $data) {
            // Templates
            if (!empty($data['stack'])) {
                foreach ($data['stack'] as $trace) {
                    if (substr($trace['file'], -6) == '.phtml') {
                        @$debug['templates'][$trace['file'] . ':' . $trace['line']]++;
                        break;
                    }
                }
            }

            // SQL keywords
            $keyword = substr($data['query'], 0, strpos(ltrim($data['query'], ' ('), ' '));
            @$debug['keywords'][$keyword]++;

            // SQL queries count
            @$debug['queries'][$data['query']]++;
        }

        // SQL queries per keyword
        $this->br();
        $this->output($this->bold(sprintf('Queries per keyword (>= %d)', $this->_count)));
        $keywords = $debug['keywords'];
        arsort($keywords);
        $found = false;
        foreach ($keywords as $keyword => $count) {
            if ($count >= $this->_count) {
                $found = true;
                $this->output(sprintf('%s %s', $this->pad($count, 5), $keyword));
            }
        }
        if (!$found) {
            $this->output('None found (you can define a lower value to "count" parameter, see help for more information)');
        }

        // SQL queries per template
        $this->br();
        $this->output($this->bold(sprintf('Queries per template (>= %d)', $this->_count)));
        $templates = $debug['templates'];
        arsort($templates);
        $found = false;
        foreach ($templates as $tpl => $count) {
            if ($count >= $this->_count) {
                $found = true;
                $pieces = explode(':', $tpl);
                $this->output(sprintf('%s %s (line %d)', $this->pad($count, 5), $pieces[0], $pieces[1]));
            }
        }
        if (!$found) {
            $this->output('None found (you can define a lower value to "count" parameter, see help for more information)');
        }

        // SQL queries count
        $this->br();
        $this->output($this->bold(sprintf('SQL queries count (>= %d)', $this->_count)));
        $queries = $debug['queries'];
        arsort($queries);
        $found = false;
        foreach ($queries as $query => $count) {
            if ($count >= $this->_count) {
                $found = true;
                $this->output(sprintf('%s %s', $this->pad($count, 5), $query));
            }
        }
        if (!$found) {
            $this->output('None found (you can define a lower value to "count" parameter, see help for more information)');
        }

        // List of rendered blocks
        if (isset($result['rendered_blocks'])) {
            $this->br();
            $this->output($this->bold('List of rendered blocks'));
            foreach ($result['rendered_blocks'] as $block) {
                $this->_displayBlockInfo($block);
            }
        }

        return $this;
    }

    protected function _displayBlockInfo($block, $level = 0)
    {
        $indent = $level * 4;
        if (!empty($block['name'])) {
            $this->start($this->pad(round($block['rendered_in'], 4), 6, STR_PAD_LEFT), $indent);
            $this->start($block['name'], 1);
            $this->start($block['cache'] ? $this->green('cached') : $this->red('not cached'), 1);
            $this->br();
            $this->output($block['class'], $indent + 7);
            if (!empty($block['tpl'])) {
                $this->output($block['tpl'], $indent + 7);
            }
        }
        if (isset($block['children'])) {
            foreach ($block['children'] as $child) {
                $this->_displayBlockInfo($child, $level + 1);
            }
        }

        return $this;
    }

    protected function _testProductPage($store = null)
    {
        $store = $this->_getStore($store);
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($store);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        $ids = $collection->getAllIds();

        if (empty($ids)) {
            return $this;
        }

        $this->section(sprintf('Testing Product Page of Store "%s"', $store->getCode()));

        $productId = array_rand(array_flip($ids), 1);
        $product = Mage::getModel('catalog/product')->load($productId)
            ->setStoreId($store->getId());
        $url = preg_replace('/\?.*/', '', $product->getProductUrl());
        $this->_get($url); // First call to prevent cache not being generated
        $this->_callUrl($url);
        $this->_debugUrl($url);

        return $this;
    }

    protected function _testCategoryPage($store = null)
    {
        $store = $this->_getStore($store);
        $ids = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($store->getId())
            ->addIsActiveFilter()
            ->addFieldToFilter('level', array('gt' => 1))
            ->getAllIds();

        if (empty($ids)) {
            return $this;
        }

        $this->section(sprintf('Testing Category Page of Store "%s"', $store->getCode()));

        $categoryId = array_rand(array_flip($ids), 1);
        $category = Mage::getModel('catalog/category')->load($categoryId)
            ->setStoreId($store->getId());
        $url = preg_replace('/\?.*/', '', $category->getUrl());
        $this->_get($url); // First call to prevent cache not being generated
        $this->_callUrl($url);
        $this->_debugUrl($url);

        return $this;
    }

    protected function _testHomepage($store = null)
    {
        $store = $this->_getStore($store);
        $this->section(sprintf('Testing Homepage of Store "%s"', $store->getCode()));
        $url = $store->getBaseUrl();
        $this->_get($url); // First call to prevent cache not being generated
        $this->_callUrl($url);
        $this->_debugUrl($url);

        return $this;
    }

    protected function _getStore($store = null)
    {
        return Mage::app()->getStore($store);
    }

    protected function _getDirFiles($dir, $filter = array())
    {
        if (!is_dir($dir)) {
            return array();
        }

        $filter = (array) $filter;
        $files = array();
        $dirIterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            /** @var $file SplFileInfo */
            if (!is_dir($file->getPathname()) &&
                !in_array($this->_getRelativePath($file->getPathname()), $this->_ignoreFiles) &&
                (empty($filter) || in_array(pathinfo($file->getFilename(), PATHINFO_EXTENSION), $filter)))
            {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function _get($url, $debug = true, $response = true)
    {
        $ch = curl_init($url);
        if ($debug) {
            curl_setopt($ch, CURLOPT_COOKIE, 'debug=1; review=1');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->getArg('auth-basic')) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->getArg('auth-basic'));
        }
        $result = curl_exec($ch);

        if ($response) {
            return $result;
        }

        $info = array();
        if (!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            curl_close($ch);
        }

        return $info;
    }

    protected function _inspectLocalModules()
    {
        $this->section('Inspecting Non Core Modules');
        $this->output($this->bold('Searching for potential bad things in non core modules ...'));
        $this->br();
        $pools = array('community', 'local');
        foreach ($pools as $pool) {
            $dir = Mage::getConfig()->getOptions()->getCodeDir() . DS . $pool;
            $files = $this->_getDirFiles($dir, 'php');
            $found = false;
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $content = preg_replace('!/\*.*?\*/!s', '', $content); // remove PHP comments
                $content = preg_replace('/\n\s*\n/', "\n", $content); // remove PHP comments
                $matches = array();
                if (preg_match_all(self::REGEX_MODULES, $content, $matches)) {
                    $found = true;
                    $this->output($this->_getRelativePath($file));
                    $matches = array_unique($matches[0]);
                    foreach ($matches as $match) {
                        $this->output($this->escape(trim($match)), 2);
                    }
                    $this->br();
                }
            }
        }

        if (!$found) {
            $this->success('All seems ok in local modules files');
        }

        return $this;
    }

    protected function _inspectTheme($store = null)
    {
        $store = $this->_getStore($store);
        Mage::getDesign()->setStore($store);
        $package = Mage::getDesign()->getPackageName();
        $theme = Mage::getDesign()->getTheme('template');
        $this->section(sprintf('Inspecting Theme of store "%s"', $store->getCode()));
        $this->output($this->bold(sprintf('Searching for potential bad things in theme "%s" ...', $package . '/' . $theme)));
        $this->br();
        $themePath = Mage::getBaseDir('design') . DS . 'frontend' . DS . $package . DS . $theme . DS . 'template';
        $files = $this->_getDirFiles($themePath, 'phtml');
        $found = false;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content); // remove javascript
            $matches = array();
            if (preg_match_all(self::REGEX_TEMPLATES, $content, $matches)) {
                $found = true;
                $this->output($this->_getRelativePath($file));
                $matches = array_unique($matches[0]);
                foreach ($matches as $match) {
                    $this->output($this->escape(trim($match)), 2);
                }
                $this->br();
            }
        }

        if (!$found) {
            $this->success('All seems ok in template files');
        }

        return $this;
    }

    protected function _listStores()
    {
        $this->section('Stores Information');
        foreach (Mage::app()->getWebsites() as $website) {
            $this->output(sprintf(
                '%s (%s)',
                $this->bold($website->getName()),
                $this->bold($website->getCode())
            ));
            foreach ($website->getGroups() as $group) {
                $this->output($group->getName(), 2);
                foreach ($group->getStores() as $store) {
                    $output = sprintf('%s (%s) %s', $store->getName(), $store->getCode(), $store->getBaseUrl());
                    if ($store->getId() === $this->_getStore()->getId()) {
                        $output .= $this->red(' <= using this store for current code review');
                    }
                    $this->output($output, 4);
                }
            }
        }

        return $this;
    }

    protected function _checkRewrites()
    {
        $this->section('Rewrites Information');
        $types = array(
            'models'  => 'Models',
            'blocks'  => 'Blocks',
            'helpers' => 'Helpers',
        );
        $this->output('/!\ Only active modules are considered and core modules are ignored');
        $this->output('The script will also try to find possible conflicts between classes', 4);
        $this->br();
        $rewrites = array();
        $modules = $this->_config->getNode('modules')->children();
        foreach ($modules as $moduleName => $module) {
            if ($module->is('active') && $module->codePool != 'core') {
                $mergeModel = new Mage_Core_Model_Config_Base();
                $configFile = $this->_config->getModuleDir('etc', $moduleName) . DS . 'config.xml';
                if ($data = $mergeModel->loadFile($configFile)) {
                    foreach ($types as $type => $label) {
                        $list = $mergeModel->getNode()->global->$type;
                        if ($list) {
                            foreach ($list->children() as $key => $data) {
                                if ($data->rewrite) {
                                    foreach ($data->rewrite->children() as $path => $value) {
                                        $rewrites[$type]["$key/$path"][$moduleName] = (string) $value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($rewrites)) {
            $this->output('No class rewrites found');
        } else {
            foreach ($rewrites as $type => $list) {
                $this->output(sprintf('%s (%s)', $this->bold($types[$type]), count($list)));
                foreach ($list as $key => $modules) {
                    if (count($modules) === 1) {
                        $this->output($this->bold($key), 2);
                    } else {
                        $this->error(sprintf('%s (possible classes conflicts)', $this->bold($key)), 2);
                    }
                    foreach ($modules as $module => $class) {
                        $this->start($this->pad($module, 40), 4);
                        $this->output($class);
                    }
                }
                $this->br();
            }
        }

        return $this;
    }

    protected function _countModules()
    {
        $this->section('Modules Information');
        $config = $this->_config;
        $modules = array();
        foreach ($config->getNode('modules')->children() as $name => $module) {
            $pieces = explode('_', $name);
            $ns = $pieces[0];
            $codePool = (string) $module->codePool;
            if ($module->is('active')) {
                @$modules[$codePool][$ns]['active'][] = $name;
            } else {
                @$modules[$codePool][$ns]['inactive'][] = $name;
            }
        }
        $this->start($this->pad('', 20));
        $this->start($this->pad('Active', 10, STR_PAD_LEFT));
        $this->output($this->pad('Disabled', 10, STR_PAD_LEFT));
        foreach ($modules as $codePool => $data) {
            $this->output($this->bold($codePool));
            foreach ($data as $ns => $nsModules) {
                $this->start($this->pad($ns, 18), 2);
                $this->start($this->pad(count($nsModules['active']), 10, STR_PAD_LEFT));
                $this->output($this->pad(count($nsModules['inactive']), 10, STR_PAD_LEFT));
            }
        }

        return $this;
    }

    protected function _configInfo($store = null)
    {
        $store = $this->_getStore($store);
        $this->section(sprintf('Configuration of Store "%s"', $store->getCode()));
        $check = array(
            true => array(
                'dev/js/merge_files' => array('Merge JS Files', 'Should always be enabled in production'),
                'dev/css/merge_css_files' => array('Merge CSS Files', 'Should always be enabled in production'),
                'catalog/frontend/flat_catalog_category' => array('Use Flat Catalog Category', 'For large catalog, it is recommended to enable it'),
                'catalog/frontend/flat_catalog_product' => array('Use Flat Catalog Product', 'For large catalog, it is recommended to enable it'),
                'system/log/enabled' => array('Enable Log Cleaning', 'Should be enabled to clean the database log table'),
            ),
            false => array(
                'dev/log/active' => array('Enable Developer Log', 'Should be disabled in production'),
                'dev/debug/profiler' => array('Enable Profiler', 'Must be disabled in production'),
            ),
        );
        foreach ($check as $expected => $configs) {
            foreach ($configs as $path => $data) {
                $value = Mage::getStoreConfigFlag($path, $store);
                $label = $data[0];
                $comment = $data[1];
                $color = $value == $expected ? 'green' : 'red';
                $this->start($this->pad($label, 25));
                $output = $this->$color($this->pad($value ? 'Yes' : 'No', 6, STR_PAD_LEFT));
                $this->start($output);
                $this->output($comment, 2);
            }
        }

        return $this;
    }

    protected function _serverInfo()
    {
        $this->section('Server Information');
        $response = $this->_get(Mage::getBaseUrl() . 'bubble/debug/info', false);
        if ($response) {
            $info = json_decode($response, true);
            $pad1 = 30;
            $pad2 = 20;

            // PHP Info
            $this->output($this->bold('PHP'));

            $this->start($this->pad('Version', $pad1), 2);
            $this->start($this->pad($info['php']['version'], $pad2));
            $this->output('PHP 5.4 is known to be faster than 5.3');

            $this->start($this->pad('Mode', $pad1), 2);
            $this->start($this->pad($info['php']['sapi'], 15));
            $this->output('Nginx + PHP-FPM is faster than Apache + mod_php');

            // APC Info
            $this->output($this->bold('APC'));

            $this->start($this->pad('apc.enabled', $pad1), 2);
            $this->start($this->pad($info['apc']['enabled'] ? 'Yes' : 'No', $pad2));
            $this->output('APC (or another PHP optimizer) is a must-have in production environment');

            $this->start($this->pad('apc.stat', $pad1), 2);
            $this->start($this->pad($info['apc']['stat'] ? 'Yes' : 'No', $pad2));
            $this->output('apc.stat can be turned off in production environment to optimize performances');

            $this->start($this->pad('apc.shm_size', $pad1), 2);
            $this->start($this->pad($info['apc']['shm_size'], $pad2));
            $this->output('apc.shm_size can be set to a value of 128M');

            // Magento Info
            $this->output($this->bold('Magento'));

            $this->start($this->pad('Version', $pad1), 2);
            $this->output($info['magento']['version']);

            $this->start($this->pad('Compilation', $pad1), 2);
            $this->start($this->pad($info['magento']['compiler'] ? 'Yes' : 'No', $pad2));
            $this->output('Must be enabled in production environment (System > Tools > Compilation)');

            // MySQL Info
            $this->output($this->bold('MySQL'));

            $this->start($this->pad('have_query_cache', $pad1), 2);
            $value = $info['mysql']['have_query_cache'];
            $this->start($this->pad($value, $pad2));
            $this->output('Should always be YES');

            $this->start($this->pad('query_cache_size', $pad1), 2);
            $value = round($info['mysql']['query_cache_size'] / pow(1024, 2), 2); // in megabytes
            $this->start($this->pad($value . 'M', $pad2));
            $this->output('64M is the minimum recommended value');

            $this->start($this->pad('query_cache_limit', $pad1), 2);
            $value = round($info['mysql']['query_cache_limit'] / pow(1024, 2), 2); // in megabytes
            $this->start($this->pad($value . 'M', $pad2));
            $this->output('2M is the minimum recommended value');

            $this->start($this->pad('sort_buffer_size', $pad1), 2);
            $value = round($info['mysql']['sort_buffer_size'] / pow(1024, 2), 2); // in megabytes
            $this->start($this->pad($value . 'M', $pad2));
            $this->output('8M is the recommended value');

            $this->start($this->pad('log_slow_queries', $pad1), 2);
            $value = $info['mysql']['log_slow_queries'];
            $this->start($this->pad($value, $pad2));
            $this->output('Should be disabled in production environment');

            $this->start($this->pad('thread_cache_size', $pad1), 2);
            $value = $info['mysql']['thread_cache_size'];
            $this->start($this->pad($value, $pad2));
            $this->output('[number of CPUs] * multiplier (1 < multiplier < 5 )');

            $this->start($this->pad('innodb_buffer_pool_size', $pad1), 2);
            $value = round($info['mysql']['innodb_buffer_pool_size'] / pow(1024, 3), 2); // in gigabytes
            $this->start($this->pad($value . 'G', $pad2));
            $this->output('Combined web and DB server = 50% of total RAM, dedicated DB server = 80%');

            $this->start($this->pad('innodb_thread_concurrency', $pad1), 2);
            $value = $info['mysql']['innodb_thread_concurrency'];
            $this->start($this->pad($value, $pad2));
            $this->output('2 * [number of CPUs] + 2');

            $this->start($this->pad('innodb_autoextend_increment', $pad1), 2);
            $value = $info['mysql']['innodb_autoextend_increment'];
            $this->start($this->pad($value . 'M', $pad2));
            $this->output('Default value (8M) should be set to a fairly high value (64M to 512M)');
        }
    }

    protected function _miscInfo()
    {
        $this->section('Misc Information');

        $pad1 = 18;
        $pad2 = 10;

        // Prevent bug in products count when using flat catalog product
        $useFlatProduct = Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
        if ($useFlatProduct) {
            $this->_getStore()->setConfig('catalog/frontend/flat_catalog_product', 0);
        }

        // Active products count
        $countActiveProducts = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->getSize();
        $this->start($this->pad('Active Products', $pad1));
        $this->output($this->pad($countActiveProducts, $pad2, STR_PAD_LEFT));

        // Disabled products count
        $countDisabledProducts = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
            ->getSize();
        $this->start($this->pad('Disabled Products', $pad1));
        $this->output($this->pad($countDisabledProducts, $pad2, STR_PAD_LEFT));

        // Reset use of flat catalog product if needed
        if ($useFlatProduct) {
            $this->_getStore()->setConfig('catalog/frontend/flat_catalog_product', 1);
        }

        // Categories count
        $countCategories = Mage::getModel('catalog/category')->getCollection()->getSize();
        $this->start($this->pad('Categories', $pad1));
        $this->output($this->pad($countCategories, $pad2, STR_PAD_LEFT));

        // Customers count
        $countCustomers = Mage::getModel('customer/customer')->getCollection()->getSize();
        $this->start($this->pad('Customers', $pad1));
        $this->output($this->pad($countCustomers, $pad2, STR_PAD_LEFT));

        // Orders count
        $countOrders = Mage::getModel('sales/order')->getCollection()->getSize();
        $this->start($this->pad('Orders', $pad1));
        $this->output($this->pad($countOrders, $pad2, STR_PAD_LEFT));

        // Product attributes count
        $countAttributes = Mage::getModel('eav/entity_attribute')->getCollection()
            ->setEntityTypeFilter(Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId())
            ->getSize();
        $this->start($this->pad('Product Attributes', $pad1));
        $this->output($this->pad($countAttributes, $pad2, STR_PAD_LEFT));

        // URL rewrites count
        $countUrls = Mage::getModel('core/url_rewrite')->getCollection()->getSize();
        $this->start($this->pad('URL Rewrites', $pad1));
        $this->output($this->pad($countUrls, $pad2, STR_PAD_LEFT));
    }

    protected function _cacheInfo()
    {
        $this->section('Cache Information');
        $invalidTypes = Mage::getModel('core/cache')->getInvalidatedTypes();
        $cacheTypes = Mage::getModel('core/cache')->getTypes();
        foreach ($cacheTypes as $cache) {
            $enabled = Mage::app()->useCache($cache->getId()) ? 'Enabled' : 'Disabled';
            $enabled = $this->pad($enabled, 10);
            if ($cache->getStatus()) {
                $enabled = $this->green($enabled);
                $valid = !array_key_exists($cache, $invalidTypes);
                if ($valid) {
                    $valid = $this->pad('Valid', 8);
                    $valid = $this->green($valid);
                } else {
                    $valid = $this->pad('Invalid', 8);
                    $valid = $this->red($valid);
                }
            } else {
                $enabled = $this->red($enabled);
                $valid = $this->pad('N/A', 8);
            }
            $this->start($this->pad($cache->getId(), 25));
            $this->start($enabled);
            $this->start($valid);
            $this->output($cache->getCacheType());
        }

        return $this;
    }

    protected function _getRelativePath($path)
    {
        return trim(str_replace(Mage::getBaseDir(), '', $path), DS);
    }

    protected function _checkForbiddenUrls($store = null)
    {
        $store = $this->_getStore($store);
        $this->section(sprintf('Checking Forbidden URLs of Store "%s"', $store->getCode()));
        $this->output($this->bold('Following URLs should return 403 Forbidden or 404 Not Found'));
        $this->br();
        $this->_callUrl($store->getBaseUrl() . 'app/', array(403, 404));
        $this->_callUrl($store->getBaseUrl() . 'app/etc/local.xml', array(403, 404));
        $this->_callUrl($store->getBaseUrl() . 'var/', array(403, 404));
        $this->_callUrl($store->getBaseUrl('media'), array(403, 404));
        $this->_callUrl($store->getBaseUrl('skin'), array(403, 404));

        return $this;
    }

    protected function _copyright()
    {
        $this->output(sprintf('Bubble_CodeReview v%s for Magento', self::VERSION));
        $this->output('(c) 2013 BubbleCode, by Johann Reinke');
        $this->output('http://shop.bubblecode.net');

        return $this;
    }

    protected function _saveReview()
    {
        $dir = Mage::getConfig()->getVarDir('review');
        $filename = 'review_' . date('Ymd_His') . '.txt';
        $fh = fopen($dir . DS . $filename, 'w');
        if ($fh) {
            $capture = $this->wash($this->_capture);
            if (fwrite($fh, $capture)) {
                $path = $this->_getRelativePath($dir);
                $this->output('Review successfully saved to ' . $path . DS . $filename);
            }
            fclose($fh);
        }

        return $this;
    }

    protected function _getOutputRenderer()
    {
        $name = $this->getArg('r') ? $this->getArg('r') : php_sapi_name();
        $renderer = Bubble_Output_Renderer::factory($name);
        $renderer->setSectionLength($this->_sectionLength);

        return $renderer;
    }

    protected function _enableCache($types = array())
    {
        $allTypes = Mage::app()->useCache();
        foreach ($allTypes as $code => $state) {
            if (in_array($code, $types) || empty($types)) {
                $allTypes[$code] = 1;
            }
        }
        Mage::app()->saveUseCache($allTypes);

        return $this;
    }

    protected function _disableCache($types = array())
    {
        $allTypes = Mage::app()->useCache();
        foreach ($allTypes as $code => $state) {
            if (in_array($code, $types) || empty($types)) {
                $allTypes[$code] = 0;
            }
        }
        Mage::app()->saveUseCache($allTypes);

        return $this;
    }

    protected function _validate()
    {
        if (!Mage::isInstalled()) {
            exit('Please install magento before running this script.');
        }

        if (!Mage::helper('core')->isDevAllowed()) {
            exit('You are not allowed to run this script.');
        }

        if (!Mage::helper('core')->isModuleEnabled('Bubble_CodeReview')) {
            exit('Please enable Bubble_CodeReview module before running this script.');
        }

        if (!extension_loaded('curl')) {
            exit('This script needs cURL.');
        }

        return true;
    }

    public function section($str)
    {
        return $this->br() . $this->_renderer->section($this->bold($str)) . $this->br();
    }

    public function captureOutput($str)
    {
        $this->_capture .= $str;
    }

    protected function _parseArgs()
    {
        if (empty($this->_args) && !empty($_SERVER['argv'])) {
            return parent::_parseArgs();
        }

        return $this;
    }

    public function getArg($name)
    {
        $arg = parent::getArg($name);
        if (false === $arg && isset($_GET[$name])) {
            $arg = $_GET[$name];
        }

        return $arg;
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shell/review.php -- [options]

  -r            Output renderer (default is php_sapi_name())
  -h            Short alias for help
  --store       Store view to review (default store view is used if not specified)
  --count       Ignore SQL query count under this value (default is 2)
  --url         Debug a specific URL instead of default ones (home, category, product)
  --no-report   Disable report generation at the end of code review
  --auth-basic  Specify basic auth credentials as username:password
  help          This help

USAGE;
    }
}

$shell = new Bubble_Shell_CodeReview();
$shell->run();
