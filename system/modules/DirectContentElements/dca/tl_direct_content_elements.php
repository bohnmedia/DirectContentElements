<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Cliff Parnitzky 2014
 * @author     Cliff Parnitzky
 * @package    DirectContentElements
 * @license    LGPL
 */

/**
 * Table tl_direct_content_elements
 */
$GLOBALS['TL_DCA']['tl_direct_content_elements'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'     => 'DynamicTable',
		'oncreate_callback' => array
		(
			array('tl_direct_content_elements', 'initTable'),
		)
	)
);

/**
 * Class tl_direct_content_elements
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Cliff Parnitzky 2014
 * @author     Cliff Parnitzky
 * @package    Controller
 */
class tl_direct_content_elements extends Backend
{
	/*
	 * Initialize the table
	 */
	public function initTable($strTable)
	{
		$table = 'tl_content';
		
		// load dca and language
		$this->loadLanguageFile($table); // needed for panel layout
		$this->loadDataContainer($table);
		
		// modify config
		$GLOBALS['TL_DCA'][$table]['config']['closed'] = true;
		$GLOBALS['TL_DCA'][$table]['config']['ptable'] = 'tl_article';
		// modify sorting
		$GLOBALS['TL_DCA'][$table]['list']['sorting']['filter'] = array(array('(ptable = ? OR ptable = "")', 'tl_article'));
		$GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'] = 1;
		$GLOBALS['TL_DCA'][$table]['list']['sorting']['flag'] = 11;
		$GLOBALS['TL_DCA'][$table]['list']['sorting']['fields'] = array('pid', 'sorting'); // '(SELECT a.title FROM tl_article a where a.id = tl_content.pid)', 
		// modify label
		$GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = array('id');
		$GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'] = array('tl_direct_content_elements', 'getLabel');
		$GLOBALS['TL_DCA'][$table]['list']['label']['group_callback'] = array('tl_direct_content_elements', 'getGroupName');
		// remove some operations
		unset($GLOBALS['TL_DCA'][$table]['list']['operations']['copy']);
		unset($GLOBALS['TL_DCA'][$table]['list']['operations']['cut']);
		// modfiy edit operation
		$GLOBALS['TL_DCA'][$table]['list']['operations']['edit']['href'] = 'do=article&table=tl_content&' . $GLOBALS['TL_DCA'][$table]['list']['operations']['edit']['href'];
		// add additonal filter and search fields
		$GLOBALS['TL_DCA']['tl_content']['fields']['cssID']['filter'] = true;
		$GLOBALS['TL_DCA']['tl_content']['fields']['cssID']['search'] = true;
		$GLOBALS['TL_DCA']['tl_content']['fields']['space']['filter'] = true;
		$GLOBALS['TL_DCA']['tl_content']['fields']['space']['search'] = true;
		
		return $table;
	}
	
	/**
	 * Creates the name of the group, should be the pages name
	 */
	public function getLabel($row, $label, DataContainer $dc)
	{
		$objArticle = $objArticle = \ArticleModel::findByPk($row['pid']);
		$this->loadLanguageFile('tl_article');
		$this->loadLanguageFile('tl_content');
		
		return $objArticle->title . ' <span style="color:#b3b3b3;padding-left:3px">[' . $GLOBALS['TL_LANG']['tl_article'][$objArticle->inColumn] . ']</span> &raquo; ' . $GLOBALS['TL_LANG']['CTE'][$row['type']][0] . ' <span style="color:#b3b3b3;padding-left:3px">[ID: ' . $row['id'] . ']</span>';
	}
	
	/**
	 * Creates the name of the group, should be the pages name
	 */
	public function getGroupName($group, $sortingMode, $firstOrderBy, $row, DataContainer $dc)
	{
		$objPage = $this->Database->prepare('SELECT p.* FROM tl_page p JOIN tl_article a ON a.pid = p.id WHERE a.id = ?')->limit(1)->execute($row['pid']);
		if ($objPage->numRows)
		{
			$group = \Image::getHtml(\Controller::getPageStatusIcon($objPage), '', null) . " " . $objPage->title;
		}
		return $group;
	}
}

?>