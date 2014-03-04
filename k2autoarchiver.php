<?php

/**
* K2 Auto Archiver plugin
* @version 2.0
* @author Elvis Sedic
* @copyright (C) Elvis Sedic, www.spletodrom.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgContentK2autoarchiver extends JPlugin {

	function plgContentK2autoarchiver( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	//function onContentPrepare(&$article, &$params, $page = 0)
	public function onContentPrepare($context, &$article, &$params, $limitstart) {
		
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();		
		
		$whereA = array();

		$plugin = JPluginHelper::getPlugin( 'content', 'k2autoarchiver' );
		$pluginparams = new JRegistry();
		$pluginparams->loadString($plugin->params);
		
		$action = $pluginparams->def('action');
		$sourcecat = trim($pluginparams->def('sourcecat'));
		$defaultcat = $pluginparams->def('defaultcat');
		
		$whereA[] = ' (a.trash != 1) ';
		if(!empty($sourcecat)) {
			$whereA[] = ' (a.catid IN (' . $sourcecat . '))';
		}

		switch($action) {
			case 'archive':
				if(!empty($defaultcat) && is_numeric($defaultcat)) {
					$query = 'UPDATE #__k2_items a SET a.catid='.$defaultcat;				
				} else {
					$query = 'UPDATE #__k2_items a SET a.catid=0 ';
					$whereA[] = ' FALSE ';
				}

			break;
		
			case 'trash':
				$query = 'UPDATE #__k2_items a SET a.trash=1 ';

			break;

            case 'unpublish':
				$query = 'UPDATE #__k2_items a SET a.published=0 ';

			break;

			
			case 'delete':
				$query = 'DELETE FROM #__k2_items ';
				
			break;
		}

		$where = implode(' AND ', $whereA);				
		$query .= ' WHERE (a.created > "0000-00-00 00:00:00") AND (a.created < NOW()) AND
						' . $where;

		$db->setQuery($query);
		$db->query();
		
	}
}
