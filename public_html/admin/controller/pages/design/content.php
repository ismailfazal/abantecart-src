<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2013 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE') || !IS_ADMIN) {
	header('Location: static_pages/');
}
class ControllerPagesDesignContent extends AController {
	private $error = array();
	public $data = array();

	/**
	 * @var AContentManager
	 */
	private $acm;
	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$this->document->initBreadcrumb(array(
			'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('design/content'),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		));

		$grid_settings = array(
			'table_id' => 'content_grid',
			'url' => $this->html->getSecureURL('listing_grid/content'),
			'editurl' => $this->html->getSecureURL('listing_grid/content/update'),
			'update_field' => $this->html->getSecureURL('listing_grid/content/update_field'),
			'sortname' => 'sort_order',
			'sortorder' => 'asc',
			'columns_search' => true,
			'grid_ready' => 'remove_view();',
			'actions' => array(
				'edit' => array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->html->getSecureURL('design/content/update', '&content_id=%ID%')
				),
				'delete' => array(
					'text' => $this->language->get('button_delete')
				),
				'save' => array(
					'text' => $this->language->get('button_save'),

				),
			),
		);

		$grid_settings['colNames'] = array(
            $this->language->get('column_title'),
			$this->language->get('column_parent'),
			$this->language->get('column_status'),
			$this->language->get('column_sort_order'),
		);
		$grid_settings['colModel'] = array(
			array(
				'name' => 'title',
				'index' => 'title',
				'width' => 250,
				'align' => 'left',
			),
			array(
				'name' => 'parent_name',
				'index' => 'parent_name',
				'width' => 100,
				'align' => 'center',
				'search' => false,
			),
			array(
				'name' => 'status',
				'index' => 'status',
				'width' => 100,
				'align' => 'center',
				'search' => false,
			),
			array(
				'name' => 'products',
				'index' => 'products',
				'width' => 100,
				'align' => 'center',
				'search' => false,
			));
		if ($this->config->get('config_show_tree_data')) {
			$grid_settings['expand_column'] = 'title';
			$grid_settings['multiaction_class'] = 'hidden';
		}


		$grid = $this->dispatch('common/listing_grid', array($grid_settings));
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());

		$this->document->setTitle($this->language->get('heading_title'));
		$this->view->assign('insert', $this->html->getSecureURL('design/content/insert'));
		$this->view->assign('help_url', $this->gen_help_url('content_listing'));


		$this->processTemplate('pages/design/content_list.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript(RDIR_TEMPLATE . 'javascript/ckeditor/ckeditor.js');
		$this->acm = new AContentManager();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->_validateForm()) {

			$savedata = $this->request->post;
			unset($savedata['parent_content_id'], $savedata['sort_order']);
			foreach ($this->request->post['parent_content_id'] as $par_id) {
				list($tmp, $parent_id) = explode('_', $par_id);
				$savedata['parent_content_id'][] = (int)$parent_id;
				$savedata['sort_order'][] = (int)$this->request->post['sort_order'][$par_id];
			}

			$content_id = $this->acm->addContent($savedata);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('design/content/update', '&content_id=' . $content_id));
		}

		// content language switcher
		$languages = $this->language->getActiveLanguages();
		if (sizeof($languages) > 1) {

			$this->view->assign('languages', $languages);
			$this->view->assign('language_code', $this->session->data['content_language']); //selected in selectbox
			foreach ($this->request->get as $name => $value) {
				if ($name == 'content_language_code') continue;
				$hiddens[$name] = $value;
			}
			$this->view->assign('lang_action', $this->html->getSecureURL('design/content/update'));
			$this->view->assign('hiddens', $hiddens);
		}

		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('update_title'));
		$this->document->addScript(RDIR_TEMPLATE . 'javascript/ckeditor/ckeditor.js');
		$this->document->addScript(RDIR_TEMPLATE . 'javascript/ckeditor/adapters/jquery.js');

		$this->acm = new AContentManager();

		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

		if (is_int(strpos($this->request->get['content_id'], '_'))) {
			list($void, $content_id) = explode('_', $this->request->get['content_id']);
			$this->request->get['content_id'] = $content_id;
			unset($content_id);
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->_validateForm()) {
			$savedata = $this->request->post;
			unset($savedata['parent_content_id'], $savedata['sort_order']);
			foreach ($this->request->post['parent_content_id'] as $par_id) {
				list($tmp, $parent_id) = explode('_', $par_id);
				$savedata['parent_content_id'][] = (int)$parent_id;
				$savedata['sort_order'][$parent_id] = (int)$this->request->post['sort_order'][$par_id];
			}
			$this->acm->editContent($this->request->get['content_id'], $savedata);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->html->redirect($this->html->getSecureURL('design/content/update', '&content_id=' . $this->request->get['content_id']));
		}
		$this->view->assign('content_layout', $this->html->getSecureURL('design/content/edit_layout', '&content_id=' . $this->request->get['content_id']));
		$this->view->assign('content_form', $this->html->getSecureURL('design/content/update', '&content_id=' . $this->request->get['content_id']));
		$this->view->assign('tab_form', $this->language->get('tab_form'));
		$this->view->assign('tab_layout', $this->language->get('tab_layout'));
		$this->view->assign('content_id', $this->request->get['content_id']);


		$this->_getForm();

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}

	private function _getForm() {
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$this->data['error'] = $this->error;
		$this->data['language_id'] = $this->config->get('storefront_language_id');

		$this->document->initBreadcrumb(array(
			'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('design/content'),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		));

		if ((int)$this->request->get['content_id']) {
			$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/content/update', '&content_id=' . $this->request->get['content_id']),
				'text' => $this->language->get('update_title'),
				'separator' => ' :: '
			));
		} else {
			$this->document->addBreadcrumb(array(
				'href' => $this->html->getSecureURL('design/content/insert'),
				'text' => $this->language->get('insert_title'),
				'separator' => ' :: '
			));
		}

		$this->data['cancel'] = $this->html->getSecureURL('design/content');

		if (isset($this->request->get['content_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$content_info = $this->acm->getContent($this->request->get['content_id']);
		}

		$allowedFields = array('status', 'description', 'title', 'content', 'parent_content_id', 'sort_order', 'store_id', 'keyword');

		foreach ($allowedFields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (isset($content_info)) {
				$this->data[$field] = $content_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		if (!isset($this->request->get['content_id'])) {
			$this->data['action'] = $this->html->getSecureURL('design/content/insert');
			$this->data['form_title'] = $this->language->get('text_insert') . $this->language->get('heading_title');
			$this->data['update'] = '';
			$form = new AForm('ST');
		} else {

			$this->data['action'] = $this->html->getSecureURL('design/content/update', '&content_id=' . $this->request->get['content_id']);
			$this->data['form_title'] = $this->language->get('text_edit') . $this->language->get('heading_title');
			$this->data['update'] = $this->html->getSecureURL('listing_grid/content/update_field', '&id=' . $this->request->get['content_id']);
			$form = new AForm('HS');
		}


		$form->setForm(array(
			'form_name' => 'contentFrm',
			'update' => $this->data['update'],
		));

		$this->data['form']['id'] = 'contentFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array(
			'type' => 'form',
			'name' => 'contentFrm',
			'attr' => 'confirm-exit="true"',
			'action' => $this->data['action'],
		));
		$this->data['form']['submit'] = $form->getFieldHtml(array(
			'type' => 'button',
			'name' => 'submit',
			'text' => $this->language->get('button_save'),
			'style' => 'button1',
		));
		$this->data['form']['cancel'] = $form->getFieldHtml(array(
			'type' => 'button',
			'name' => 'cancel',
			'text' => $this->language->get('button_cancel'),
			'style' => 'button2',
		));

		$this->data['form']['fields']['status'] = $form->getFieldHtml(array(
			'type' => 'checkbox',
			'name' => 'status',
			'value' => $this->data['status'],
			'style' => 'btn_switch',
		));

		// we need get contents list for multiselect
		$multiSelect = $this->acm->getContentsForSelect(false);

		$selected_parents = array();
		$this->data['parent_content_id'] = (array)$this->data['parent_content_id'];
		foreach ($this->data['parent_content_id'] as $parent_id) {
			foreach ($multiSelect as $option_id => $option_value) {
				list($void, $content_id) = explode('_', $option_id);
				if ($parent_id == $content_id) {
					$selected_parents[$option_id] = $option_id;
				}
				if ($content_id == $this->request->get['content_id']) {
					$disabled_parents[$option_id] = $option_id;
				}
			}
		}
		if (!$selected_parents) {
			$selected_parents = array('0' => '0');
		}

		$this->data['form']['fields']['parent'] = $form->getFieldHtml(array(
			'type' => 'multiSelectbox',
			'name' => 'parent_content_id[]',
			'options' => $multiSelect,
			'value' => $selected_parents,
			'disabled' => $disabled_parents,
			'attr' => 'size = "' . (sizeof($multiSelect) > 10 ? 10 : sizeof($multiSelect)) . '"'
		));

		$this->data['form']['fields']['title'] = $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'title',
			'value' => $this->data['title'],
			'required' => true,
		));
		$this->data['form']['fields']['description'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'description',
			'value' => $this->data['description']
		));

		$this->data['form']['fields']['content'] = $form->getFieldHtml(array(
			'type' => 'textarea',
			'name' => 'content',
			'value' => $this->data['content'],
			'required' => true,
		));

		$this->data['form']['fields']['keyword'] = $form->getFieldHtml(array(
			'type' => 'button',
			'name' => 'generate_seo_keyword',
			'text' => $this->language->get('button_generate'),
			'style' => 'button'
		));
		$this->data['generate_seo_url'] = $this->html->getSecureURL('common/common/getseokeyword', '&object_key_name=content_id&id=' . $this->request->get['content_id']);

		$this->data['form']['fields']['keyword'] .= $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'keyword',
			'value' => $this->data['keyword'],
			'style' => 'large-field',
			'help_url' => $this->gen_help_url('seo_keyword'),
		));


		// get array with stores looks like array (store_id=>array(content_id=>store_name))
		$store_values = $store_selected = array();
		$store_values[0] = $this->language->get('text_default');
		$store_selected[0] = 0;

		$stores = $this->acm->getContentStores($this->request->get['content_id']);
		if ($stores) {
			foreach ($stores as $store_id => $store) {
				$store_values[$store_id] = trim(current($store));
				if (isset($store[$this->request->get['content_id']])) {
					$store_selected[$store_id] = $store_id;
				}
			}
		}

		$this->data['form']['fields']['store_id'] = $form->getFieldHtml(array(
			'type' => 'checkboxgroup',
			'name' => 'store_id[]',
			'value' => $store_selected,
			'options' => $store_values,
			'scrollbox' => true,
		));

		if ($selected_parents) {
			foreach ($selected_parents as $option_id) {
				list($void, $parent_id) = explode('_', $option_id);
				$this->data['form']['fields']['sort_order'] .= ' ' . $multiSelect[$option_id] . ': '
						. $form->getFieldHtml(array(
							'type' => 'input',
							'name' => 'sort_order[' . $option_id . ']',
							'value' => $this->data['sort_order'][$parent_id],
							'attr' => 'style="width: 30px;"'
						)) . '';
			}
		}


		$this->view->assign('help_url', $this->gen_help_url('content_edit'));
		$this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=&object_id&type=image&mode=url'));
		$this->view->assign('language_code', $this->session->data['language']);
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/design/content_form.tpl');
	}

	private function _validateForm() {
		if (!$this->user->canModify('design/content')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((strlen(html_entity_decode($this->request->post['title'])) < 2) || (strlen(html_entity_decode($this->request->post['title'])) > 64)) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if ((strlen(html_entity_decode($this->request->post['content'])) < 2)) {
			$this->error['content'] = $this->language->get('error_content');
		}
		if (($error_text = $this->html->isSEOkeywordExists('content_id=' . $this->request->get['content_id'], $this->request->post['keyword']))) {
			$this->error['keyword'] = $error_text;
		}

		if (!$this->error) {
			return TRUE;
		} else {
			if (!isset($this->error['warning'])) {
				$this->error['warning'] = $this->language->get('error_required_data');
			}
			return FALSE;
		}
	}


	public function edit_layout() {

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->document->setTitle($this->language->get('update_title'));
		$this->acm = new AContentManager();

		$url = '';
		if (!isset($this->request->get['content_id']) || !(int)$this->request->get['content_id']) {
			$this->redirect($this->html->getSecureURL('design/content'));
		}

		$content_id = (int)$this->request->get['content_id'];
		$url .= '&content_id=' . $content_id;
		$content = $this->acm->getContent($content_id);

		$page_id = $this->acm->getPageId($content_id);
		//$layout_id = $this->acm->getPageId($layout_id);
		$tmpl_id = $this->config->get('config_storefront_template');

		$this->view->assign('error_warning', (isset($this->error['warning']) ? $this->error['warning'] : ''));
		$this->view->assign('success', (isset($this->session->data['success']) ? $this->session->data['success'] : ''));
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$this->document->resetBreadcrumbs();
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		));
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('design/content', $url),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		));

		$this->view->assign('content_layout', $this->html->getSecureURL('design/content/edit_layout', '&content_id=' . $this->request->get['content_id']));
		$this->view->assign('content_form', $this->html->getSecureURL('design/content/update', '&content_id=' . $this->request->get['content_id']));
		$this->view->assign('tab_form', $this->language->get('tab_form'));
		$this->view->assign('tab_layout', $this->language->get('tab_layout'));


		$layout = new ALayoutManager($tmpl_id, $page_id, (int)$layout_id);

		$settings['page'] = $layout->getPageData();
		$settings['page']['content'] = $content['content'];


		$settings['layout'] = $layout->getActiveLayout();
		$settings['layout_drafts'] = $layout->getLayoutDrafts();
		$settings['layout_templates'] = $layout->getLayoutTemplates();
		$settings['_blocks'] = $layout->getInstalledBlocks();
		$settings['blocks'] = $layout->getLayoutBlocks();
		$settings['action'] = $this->html->getSecureURL('design/content/save_layout', $url);
		// hidden fields of layout form
		$settings['hidden']['page_id'] = $page_id;
		$settings['hidden']['layout_id'] = $layout_id;
		$settings['hidden']['content_id'] = $content_id;

		$layoutform = $this->dispatch('common/page_layout', array($settings));

		$this->view->assign('heading_title', $this->language->get('heading_title'));
		$this->view->assign('layoutform', $layoutform->dispatchGetOutput());
		$this->view->assign('help_url', $this->gen_help_url('content_layout'));

		$this->processTemplate('pages/design/content_layout.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

	}

	public function save_layout() {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

		$this->acm = new AContentManager();
		if (!(int)$this->request->get['content_id'] && !(int)$this->request->post['content_id']) {
			$this->redirect($this->html->getSecureURL('design/content'));
		}
		$url = '';
		if (isset($this->request->get['content_id'])) {
			$content_id = (int)$this->request->get['content_id'];
			$url .= '&content_id=' . $this->request->get['content_id'];
		} elseif (isset($this->request->post['content_id'])) {
			$content_id = (int)$this->request->post['content_id'];
			$url .= '&content_id=' . $this->request->post['content_id'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$tmpl_id = $this->config->get('config_storefront_template');

			$page_id = $this->acm->getPageId($content_id);
			$layout_id = (string)$this->acm->getLayoutId($content_id);
			if (!$layout_id) {
				$content_info = $this->acm->getContent($content_id);
				$this->request->post['layout_name'] = 'Content: ' . $content_info['name'];
			}
			$this->request->post['controller'] = 'pages/content/content';
			$layout = new ALayoutManager($tmpl_id, $page_id, $layout_id);

			$layout->savePageLayout($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success_layout');
			$this->redirect($this->html->getSecureURL('design/content/edit_layout', $url));
		}
		$this->redirect($this->html->getSecureURL('design/content/'));

	}

}
