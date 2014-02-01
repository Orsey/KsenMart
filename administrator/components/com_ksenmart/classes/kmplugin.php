<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

abstract class KMPlugin extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    function loadLanguage() {
        $lang = JFactory::getLanguage();
        $lang->load('plg_' . $this->_type . '_' . $this->_name . '.sys', JPATH_ADMINISTRATOR, null, false, false) || $lang->load('plg_' . $this->_type . '_' . $this->_name . '.sys', JPATH_PLUGINS . DS . $this->_type . DS . $this->_name, null, false, false) || $lang->load('plg_' . $this->_type . '_' . $this->_name . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false) || $lang->load('plg_' . $this->_type . '_' . $this->_name . '.sys', JPATH_PLUGINS . DS . $this->_type . DS . $this->_name, $lang->getDefault(), false, false);
    }

    function getDefaultCurrencyCode() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('code')->from('#__ksenmart_currencies')->where('`default`=1');
        $db->setQuery($query);
        $currency_code = $db->loadResult();
        if(empty($currency_code)) return JText::_('ksm_discount_fixed_type_currency');
        return $currency_code;
    }
}