<?php

defined('_JEXEC') or die;
jimport('joomla.application.component.modelkmadmin');

class KsenMartModelCurrencies extends JModelKMAdmin {

    function __construct() {
        parent::__construct();
    }

    function populateState() {
        $this->onExecuteBefore('populateState');
        
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_ksenmart');
        if($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $value = $app->getUserStateFromRequest($this->context . 'list.limit', 'limit', $params->get('admin_product_limit', 30), 'uint');
        $limit = $value;
        $this->setState('list.limit', $limit);

        $value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
        $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
        $this->setState('list.start', $limitstart);

        $order_dir = $app->getUserStateFromRequest($this->context . '.order_dir', 'order_dir', 'asc');
        $this->setState('order_dir', $order_dir);
        $order_type = $app->getUserStateFromRequest($this->context . '.order_type', 'order_type', 'id');
        $this->setState('order_type', $order_type);
        
        $this->onExecuteAfter('populateState');
    }

    function getListItems() {
        
        $this->onExecuteBefore('getListItems');
        
        $order_dir = $this->getState('order_dir');
        $order_type = $this->getState('order_type');
        $query = $this->db->getQuery(true);
        $query->select('SQL_CALC_FOUND_ROWS *')->from('#__ksenmart_currencies')->order($order_type . ' ' . $order_dir);
        $this->db->setQuery($query, $this->getState('list.start'), $this->getState('list.limit'));
        $currencies = $this->db->loadObjectList();
        $query = $this->db->getQuery(true);
        $query->select('FOUND_ROWS()');
        $this->db->setQuery($query);
        $this->total = $this->db->loadResult();
        
        $this->onExecuteAfter('getListItems', array(&$currencies));
        return $currencies;
    }

    function getTotal()
	{
		$this->onExecuteBefore('getTotal');
		
		$total=$this->total;
		
		$this->onExecuteAfter('getTotal',array(&$total));
		return $total;
	}

    function deleteListItems($ids) {
        
        $this->onExecuteBefore('deleteListItems', array(&$ids));
        
        $table = $this->getTable('currencies');
        foreach($ids as $id) $table->delete($id);
        $this->setDefaultCurrency();
        
        $this->onExecuteAfter('deleteListItems');
        return true;
    }

    function getCurrency() {
        
        $this->onExecuteBefore('getCurrency');
        
        $id = JRequest::getInt('id');
        $currency = KMSystem::loadDbItem($id, 'currencies');
        
        $this->onExecuteAfter('getCurrency', array(&$currency));
        return $currency;
    }

    function SaveCurrency($data) {
        
        $this->onExecuteBefore('SaveCurrency', array(&$data));
        
        $data['default'] = isset($data['default']) ? $data['default'] : 0;
        $table = $this->getTable('currencies');

        if(!$table->bindCheckStore($data)) {
            $this->setError($table->getError());
            return false;
        }
        $id = $table->id;
        if($data['default'] == 1) $this->setDefaultCurrency($id);

        $on_close = 'window.parent.CurrenciesRatesModule.refresh();window.parent.CurrenciesList.refreshList();';
        $return = array('id' => $id, 'on_close' => $on_close);
        
        $this->onExecuteAfter('SaveCurrency', array(&$return));
        return $return;
    }

    function setDefaultCurrency($id = null) {
        
        $this->onExecuteBefore('setDefaultCurrency', array(&$id));
        
        if(empty($id)) {
            $query = $this->db->getQuery(true);
            $query->select('id')->from('#__ksenmart_currencies')->where('`default`=1');
            $this->db->setQuery($query);
            $id = $this->db->loadResult();
            if(empty($id)) {
                $query = $this->db->getQuery(true);
                $query->select('id')->from('#__ksenmart_currencies');
                $this->db->setQuery($query, 0, 1);
                $id = $this->db->loadResult();
            }
        }
        $query = $this->db->getQuery(true);
        $query->select('*')->from('#__ksenmart_currencies')->where('id=' . $id);
        $this->db->setQuery($query);
        $default = $this->db->loadObject();
        $query = $this->db->getQuery(true);
        $query->select('*')->from('#__ksenmart_currencies');
        $this->db->setQuery($query);
        $currencies = $this->db->loadObjectList();
        foreach($currencies as $currency) {
            $rate = round($currency->rate / $default->rate, 6);
            $query = $this->db->getQuery(true);
            $query->update('#__ksenmart_currencies')->set('`default`=0')->set('rate=' . $rate)->where('id=' . $currency->id);
            $this->db->setQuery($query);
            $this->db->query();
        }
        $query = $this->db->getQuery(true);
        $query->update('#__ksenmart_currencies')->set('`default`=1')->set('rate=1')->where('id=' . $id);
        $this->db->setQuery($query);
        $this->db->query();
        
        $this->onExecuteAfter('setDefaultCurrency');
    }
}