<?php defined('_JEXEC') or die;

define('YA_MARKET_API_PATCH', 'https://api.partner.market.yandex.ru/v2');

/**
 * KMSystem
 * 
 * @package   
 * @version 2013
 * @access public
 */
class KMSystem {

    private static $_user                = array();
    private static $_Itemid              = null;
    private static $_context             = 'com_ksenmart';
    private static $_template_controller = null;

    /**
     * KMSystem::loadPlugins()
     * 
     * @return
     */
    public static function loadPlugins() {
        $db     = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $query
            ->select('folder')
            ->from('#__extensions')
            ->where('name LIKE "KSM_%"')
            ->where('type="plugin"')
            ->group('folder')
        ;
        $db->setQuery($query);
        $plugins = $db->loadObjectList();
        foreach($plugins as $plugin){
            JPluginHelper::importPlugin($plugin->folder);
        }
    }
    
    public static function getKMVersion() {
        $db     = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $query
            ->select('manifest_cache')
            ->from('#__extensions')
            ->where('element="com_ksenmart"')
        ;
        $db->setQuery($query, 0, 1);
        $km_extension = $db->loadObject();
        
        if(!empty($km_extension)){
            $params = json_decode($km_extension->manifest_cache);
            return $params->version;
        }
        return null;
    }

    /**
     * KMSystem::loadModules()
     * 
     * @param mixed $position
     * @param mixed $params
     * @return
     */
    public static function loadModules($position, $params = array()) {
        $document = JFactory::getDocument();
        $renderer = $document->loadRenderer('modules');
        return $renderer->render($position, $params, null);
    }

    /**
     * KMSystem::loadModule()
     * 
     * @param mixed $name
     * @param mixed $params
     * @return
     */
    public static function loadModule($name, $params = array()) {
        $document = JFactory::getDocument();
        $module   = JModuleHelper::getModule($name);
        $renderer = $document->loadRenderer('module');
        return $renderer->render($module, $params, null);
    }

    /**
     * KMSystem::loadModuleFiles()
     * 
     * @param mixed $module_name
     * @return
     */
    public static function loadModuleFiles($module_name) {
        $document = JFactory::getDocument();
        $jinput   = JFactory::getApplication()->input;
        $view     = $jinput->get('view', 'panel', 'string');
        $layout   = $jinput->get('layout', null, 'string');
        
        if(file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/css/' . $view . '.css')){
            $document->addStyleSheet(JURI::base() . 'modules/' . $module_name . '/css/' . $view . '.css');
        }
        
        if(file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/css/default.css')){
            $document->addStyleSheet(JURI::base() . 'modules/' . $module_name . '/css/default.css');
        }
        
        if(!empty($layout) && file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/js/' . $view . '-' . $layout . '.js')){
            $document->addScript(JURI::base() . 'modules/' . $module_name . '/js/' . $view . '-' . $layout . '.js');
        }elseif(file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/js/' . $view . '.js')){
            $document->addScript(JURI::base() . 'modules/' . $module_name . '/js/' . $view . '.js');
        }
        
        if(file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/js/default.js')){
            $document->addScript(JURI::base() . 'modules/' . $module_name . '/js/default.js');
        }
    }

    /**
     * KMSystem::getModuleLayout()
     * 
     * @param mixed $module_name
     * @return
     */
    public static function getModuleLayout($module_name) {
        $jinput   = JFactory::getApplication()->input;
        $view     = $jinput->get('view', 'panel', 'string');
        $layout   = $jinput->get('layout', null, 'string');
        
        if(!empty($layout) && file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/tmpl/' . $view . '-' . $layout . '.php')){
            return $view . '-' . $layout;
        }elseif(file_exists(JPATH_ADMINISTRATOR . '/modules/' . $module_name . '/tmpl/' . $view . '.php')){
            return $view;
        }else {
            return 'default';
        }
    }

    /**
     * KMSystem::wrapFormField()
     * 
     * @param mixed $element
     * @param mixed $html
     * @return
     */
    public static function wrapFormField($element, $html) {
        if(!isset($element['wrap'])){
            return $html;
        }
        if(empty($element['wrap'])){
            return $html;
        }
        if(!file_exists(JPATH_COMPONENT_ADMINISTRATOR . '/models/wraps/' . $element['wrap'] . '.php')){
            return $html;
        }
        ob_start();
            require JPATH_COMPONENT_ADMINISTRATOR . '/models/wraps/' . $element['wrap'] . '.php';
            $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    /**
     * KMSystem::loadJSLanguage()
     * 
     * @return
     */
    public static function loadJSLanguage() {
        $lang = JFactory::getLanguage();
        $lang->load('com_ksenmart.js', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ksenmart', null, false, false);

        $lang       = JFactory::getLanguage()->getTag();
        $filename   = JPATH_COMPONENT . DS . 'language' . DS . $lang . DS . $lang . '.com_ksenmart.js.ini';
        $version    = phpversion();

        $php_errormsg = null;
        $track_errors = ini_get('track_errors');
        ini_set('track_errors', true);

        if($version >= '5.3.1') {
            $contents = file_get_contents($filename);
            $contents = str_replace('_QQ_', '"\""', $contents);
            $strings = @parse_ini_string($contents);
        } else {
            $strings = @parse_ini_file($filename);

            if($version == '5.3.0' && is_array($strings)) {
                foreach($strings as $key => $string) {
                    $strings[$key] = str_replace('_QQ_', '"', $string);
                }
            }
        }

        ini_set('track_errors', $track_errors);

        if(!is_array($strings)) {
            $strings = array();
        }

        foreach($strings as $key => $string){
            JText::script($key);
        }
    }

    /**
     * KMSystem::loadDbItem()
     * 
     * @param mixed $id
     * @param mixed $table
     * @return
     */
    public static function loadDbItem($id = null, $table = null) {
        if(!$table){
            return false;
        }
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ksenmart' . DS . 'tables');
        $table = JTable::getInstance($table, 'KsenmartTable', array());
        
        if($id > 0) {
            $return = $table->load($id);
            if($return === false && $table->getError()){
                return false;
            }
        }
        
        $properties = $table->getProperties(1);
        $item       = JArrayHelper::toObject($properties, 'JObject');
        
        return $item;
    }

    /**
     * KMSystem::formatAddress()
     * 
     * @param mixed $address_struct
     * @return
     */
    public static function formatAddress($address_struct) {
        $address = '';
        if(!empty($address_struct)) {

            if(!empty($address_struct->city)) {
                $address .= 'г. ' . $address_struct->city;
                if(!empty($address_struct->street)) {
                    $address .= ', ';
                }
            }
            if(!empty($address_struct->street)) {
                $address .= 'ул. ' . $address_struct->street;
                if(!empty($address_struct->house)) {
                    $address .= ', ';
                }
            }
            if(!empty($address_struct->house)) {
                $address .= 'д. ' . $address_struct->house;
                if(!empty($address_struct->flat)) {
                    $address .= ', ';
                }
            }
            if(!empty($address_struct->flat)) {
                $address .= 'кв. ' . $address_struct->flat;
            }
            return $address;
        }
        return $address;
    }

    /**
     * KMSystem::getShopItemid()
     * 
     * @return
     */
    public static function getShopItemid() {
        if(self::$_Itemid === null) {
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query->select('id')->from('#__menu')->where('link LIKE ' . $db->quote('index.php?option=com_ksenmart&view=shopcatalog&layout=catalog%'))->where('published=1');
            $db->setQuery($query, 0, 1);

            $query;
            $menuitem = $db->loadObject();
            if(count($menuitem) > 0) self::$_Itemid = $menuitem->id;
            else  self::$_Itemid = '';

        }

        return self::$_Itemid;
    }

    /**
     * KMSystem::issetReview()
     * 
     * @param mixed $uid
     * @return
     */
    public static function issetReview($uid) {
        if(!empty($uid)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('user_id');
            $query->from('#__ksenmart_comments');
            $query->where('user_id=' . $uid);
            $query->where("type='shop_review'");
            $db->setQuery($query);
            $result = $db->query();

            if($db->getNumRows($result) >= 1) {
                return true;
            }
        }
        return false;
    }


    /**
     * KMSystem::getActions()
     * 
     * @return
     */
    public static function getActions() {
        jimport('joomla.access.access');
        $user = JFactory::getUser();
        $result = new JObject;
        //KMSystem::$_context = 'com_content';
        $actions = JAccess::getActions(KMSystem::$_context, 'component');

        foreach($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, 'com_ksenmart'));
        }
        return $result;
    }

    /**
     * KMSystem::getModel()
     * 
     * @param mixed $name
     * @param mixed $config
     * @return
     */
    public static function getModel($name, $config = array()) {

        $component_name = KMSystem::$_context;
        jimport('joomla.application.component.model');

        $modelFile = JPATH_SITE . DS . 'components' . DS . $component_name . DS . 'models' . DS . $name . '.php';
        $adminModelFile = JPATH_ADMINISTRATOR . DS . 'components' . DS . $component_name . DS . 'models' . DS . $name . '.php';

        if(file_exists($modelFile)) {
            require_once ($modelFile);
        } elseif(file_exists($adminModelFile)) {
            require_once ($adminModelFile);
        } else {
            JModel::addIncludePath(JPATH_SITE . DS . 'components' . DS . $component_name . DS . 'models');
        }

        //Get the right model prefix, e.g. UserModel for com_user
        $model_name = str_replace('com_', '', $component_name);
        $model_name = ucfirst($model_name) . 'Model';

        $model = JModel::getInstance($name, $model_name, $config);
        //print_r($model);

        return $model;
    }

    /**
     * KMSystem::getController()
     * 
     * @param mixed $name
     * @param mixed $config
     * @return
     */
    public static function getController($name, $config = array()) {

        $component_name = KMSystem::$_context;
        jimport('joomla.application.component.controller');

        $controllerFile = JPATH_SITE . DS . 'components' . DS . $component_name . DS . 'controllers' . DS . $name . '.php';
        $adminControllerFile = JPATH_ADMINISTRATOR . DS . 'components' . DS . $component_name . DS . 'controllers' . DS . $name . '.php';

        if(file_exists($controllerFile)) {
            require_once ($controllerFile);
        } elseif(file_exists($adminControllerFile)) {
            require_once ($adminControllerFile);
        } else {
            JController::addIncludePath(JPATH_SITE . DS . 'components' . DS . $component_name . DS . 'controllers');
        }

        $controller_name = str_replace('com_', '', $component_name);
        $controller_name = ucfirst($controller_name) . 'Controller';
        $controller_name = $controller_name . $name;

        $controller = new $controller_name();

        return $controller;
    }


    /**
     * KMSystem::getLdmApiData()
     * 
     * @param mixed $func
     * @param mixed $action
     * @param mixed $params
     * @return
     */
    public function getLdmApiData($func, $action = null, $params = array()) {
        $model = KMSystem::getModel('account');        
        if (!empty($func) || !empty($model->_auth)) {
            $api_key = null;
            $user    = $model->getUserFullInfo();
            if(isset($user->api_key)){
                $api_key = $user->api_key;
            }
            
            $link = LDM_API_PATCH.'?func=' . $func.'&api_key='.$api_key;
            if (!empty($action)) {
                $link .= '&action=' . $action;
            }

            if (!empty($params)) {
                foreach ($params as $key => $param) {
                    $param = str_replace(' ', '%20', $param);
                    $link .= '&' . $key . '=' . $param;
                }
            }

            //echo $link;

            $data_json = file_get_contents($link);
            if (!empty($data_json)) {
                return $data_json;
            }
        }
        return false;
    }

    /**
     * KMSystem::getYaMarketData()
     * 
     * @param mixed $function
     * @param string $method
     * @param mixed $params
     * @return
     */
    public static function getYaMarketData(array $function, $method = 'GET', array $params = null) {
        if(!empty($function) || !empty($this->_auth)) {

            $link = YA_MARKET_API_PATCH;

            foreach($function as $item) {
                $link .= '/' . $item;
            }
            $link .= '.json?';

            if(!empty($params)) {
                foreach($params as $key => $value) {
                    $link .= $key . '=' . $value . '&';
                }
            }

            $link .= 'oauth_token=b59934c0d4304a4ca3a094716b083833&oauth_login=ldmco&oauth_client_id=4f509dc765af484095ea3dde00e86d94';

            $headers['Content-type'] = 'application/json';
            $headers['X-Requested-With'] = 'XMLHttpRequest';
            $options = new JRegistry;
            $uri = new JUri($link);
            $trans = new JHttpTransportCurl($options);
            $response = $trans->request($method, $uri, null, $headers);

            if(!empty($response->body)) {
                return json_decode($response->body);
            }
        }
        return false;
    }
    
    /**
     * KMSystem::getTableByIds()
     * 
     * @param mixed $ids
     * @param mixed $table
     * @param mixed $fields
     * @param bool $published
     * @param bool $implode_keys
     * @return
     */
    public static function getTableByIds(array $ids, $table, array $fields, $published = true, $implode_keys = false, $single = false){
        if(!empty($ids) && is_array($ids)){
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select($fields)
                ->from('#__ksenmart_'.$table.' AS t')
            ;
            
            if($implode_keys){
                $query->where('(t.id IN (' . KMSystem::key_implode(', ', $ids).'))');
            }else{
                $query->where('(t.id IN (' . implode(', ', $ids).'))');
            }
            if($published){
                $query->where('t.published=1');
            }
            $db->setQuery($query);
            if($single){
                $object = $db->loadObject();
            }else{
                $object = $db->loadObjectList();
            }
            if(!empty($object)){
                return $object;
            }
        }
        return new stdClass;
    }
    
    /**
     * KMSystem::key_implode()
     * 
     * @param mixed $separator
     * @param mixed $array
     * @return
     */
    public function key_implode($separator, $array) {
        $keys = array_keys($array);
        return implode($separator, $keys);
    }
    
    /**
     * KMSystem::getSeoTitlesConfig()
     * 
     * @param mixed $part
     * @param string $type
     * @return
     */
    public static function getSeoTitlesConfig($part, $type = 'title'){
        if(!empty($part)){
            $db  = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select('config')
                ->from('#__ksenmart_seo_config')
                ->where('type="'.$type.'"')
                ->where('part="'.$db->getEscaped($part).'"')
            ;
            $db->setQuery($query);
            $config = json_decode($db->loadResult());
            return $config;
        }
        return false;
    }


    /**
     * KMSystem::formatCommentDate()
     * 
     * @param mixed $date
     * @return
     */
    public static function formatCommentDate($date) {
        $str_date = '';
        $date = explode(' ', $date);
        $date = explode('-', $date[0]);
        $mon = '';
        switch($date[1]) {
            case '01':
                $mon = 'января';
                break;
            case '02':
                $mon = 'февраля';
                break;
            case '03':
                $mon = 'марта';
                break;
            case '04':
                $mon = 'апреля';
                break;
            case '05':
                $mon = 'мая';
                break;
            case '06':
                $mon = 'июня';
                break;
            case '07':
                $mon = 'июля';
                break;
            case '08':
                $mon = 'августа';
                break;
            case '09':
                $mon = 'сентября';
                break;
            case '10':
                $mon = 'октября';
                break;
            case '11':
                $mon = 'ноября';
                break;
            case '12':
                $mon = 'декабря';
                break;
        }
        $str_date = $date[2] . ' ' . $mon . ' ' . $date[0];
        return $str_date;
    }
    
    public static function loadTemplate(array $vars, $view = 'shopcatalog', $layout = 'default', $tmpl = 'item'){
        
        $option = JFactory::getApplication()->input->get('option', null, 'string');
        
		if (!is_object(self::$_template_controller)){
            if($option != 'com_ksenmart'){
                self::$_template_controller = KMSystem::getController($view);
            }else{
                self::$_template_controller = JController::getInstance('KsenMartController' . ucfirst($view));
            }
		}
        
        $view           = self::$_template_controller->getView($view, 'html');
        $model          = self::$_template_controller->getModel('shopcatalog');
        $current_layout =  $view->getLayout();
        
        $view->setLayout($layout);
        if(empty($layout)){
            $view->setLayout($tmpl);
        }
        
        $view->setModel($model, true);
        foreach($vars as $name => $var){
            $view->assign($name, $var);
        }
        
        $html = $view->loadTemplate($tmpl);
        $view->setLayout($current_layout);
        return $html;
    }
}