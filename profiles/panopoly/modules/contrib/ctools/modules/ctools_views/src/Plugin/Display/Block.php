<?php

namespace Drupal\ctools_views\Plugin\Display;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\views\Plugin\views\display\Block as CoreBlock;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Provides a Block display plugin that allows for greater control over Views
 * block settings.
 */
class Block extends CoreBlock {

  public function blockSettings(array $settings) {
    $settings = parent::blockSettings($settings);
    $settings['exposed'] = [];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);
    $filtered_allow = array_filter($this->getOption('allow'));
    $filter_options = [
      'items_per_page' => $this->t('Items per page'),
      'offset' => $this->t('Pager offset'),
      'pager' => $this->t('Pager type'),
      'hide_fields' => $this->t('Hide fields'),
      'sort_fields' => $this->t('Reorder fields'),
      'configure_filters' => $this->t('Configure filters'),
      'disable_filters' => $this->t('Disable filters'),
      'configure_sorts' => $this->t('Configure sorts')
    ];
    $filter_intersect = array_intersect_key($filter_options, $filtered_allow);

    $options['allow'] = array(
      'category' => 'block',
      'title' => $this->t('Allow settings'),
      'value' => empty($filtered_allow) ? $this->t('None') : implode(', ', $filter_intersect),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $options = $form['allow']['#options'];
    $options['offset'] = $this->t('Pager offset');
    $options['pager'] = $this->t('Pager type');
    $options['hide_fields'] = $this->t('Hide fields');
    $options['sort_fields'] = $this->t('Reorder fields');
    $options['configure_filters'] = $this->t('Configure filters');
    $options['disable_filters'] = $this->t('Disable filters');
    $options['configure_sorts'] = $this->t('Configure sorts');
    $form['allow']['#options'] = $options;
    // Update the items_per_page if set.
    $defaults = array_filter($form['allow']['#default_value']);
    if (isset($defaults['items_per_page'])) {
      $defaults['items_per_page'] = 'items_per_page';
    }
    $form['allow']['#default_value'] = $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(ViewsBlock $block, array &$form, FormStateInterface $form_state) {
    $form = parent::blockForm($block, $form, $form_state);

    $allow_settings = array_filter($this->getOption('allow'));
    $block_configuration = $block->getConfiguration();

    // Modify "Items per page" block settings form.
    if (!empty($allow_settings['items_per_page'])) {
      // Items per page
      $form['override']['items_per_page']['#type'] = 'number';
      unset($form['override']['items_per_page']['#options']);
    }

    // Provide "Pager offset" block settings form.
    if (!empty($allow_settings['offset'])) {
      $form['override']['pager_offset'] = [
        '#type' => 'number',
        '#title' => $this->t('Pager offset'),
        '#default_value' => isset($block_configuration['pager_offset']) ? $block_configuration['pager_offset'] : 0,
        '#description' => $this->t('For example, set this to 3 and the first 3 items will not be displayed.'),
      ];
    }

    // Provide "Pager type" block settings form.
    if (!empty($allow_settings['pager'])) {
      $pager_options = [
        'view' => $this->t('Inherit from view'),
        'some' => $this->t('Display a specified number of items'),
        'none' => $this->t('Display all items')
      ];
      $form['override']['pager'] = [
        '#type' => 'radios',
        '#title' => $this->t('Pager'),
        '#options' => $pager_options,
        '#default_value' => isset($block_configuration['pager']) ? $block_configuration['pager'] : 'view'
      ];
    }

    // Provide "Hide fields" / "Reorder fields" block settings form.
    if (!empty($allow_settings['hide_fields']) || !empty($allow_settings['sort_fields'])) {
      // Set up the configuration table for hiding / sorting fields.
      $fields = $this->getHandlers('field');
      $header = [];
      if (!empty($allow_settings['hide_fields'])) {
        $header['hide'] = $this->t('Hide');
      }
      $header['label'] = $this->t('Label');
      if (!empty($allow_settings['sort_fields'])) {
        $header['weight'] = $this->t('Weight');
      }
      $form['override']['order_fields'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => array(),
      ];
      if (!empty($allow_settings['sort_fields'])) {
        $form['override']['order_fields']['#tabledrag'] = [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'field-weight',
          ]
        ];
        $form['override']['order_fields']['#attributes'] = ['id' => 'order-fields'];
      }

      // Sort available field plugins by their currently configured weight.
      $sorted_fields = [];
      if (!empty($allow_settings['sort_fields']) && isset($block_configuration['fields'])) {
        uasort($block_configuration['fields'], '\Drupal\ctools_views\Plugin\Display\Block::sortFieldsByWeight');
        foreach (array_keys($block_configuration['fields']) as $field_name) {
          if (!empty($fields[$field_name])) {
            $sorted_fields[$field_name] = $fields[$field_name];
            unset($fields[$field_name]);
          }
        }
        if (!empty($fields)) {
          foreach ($fields as $field_name => $field_info) {
            $sorted_fields[$field_name] = $field_info;
          }
        }
      }
      else {
        $sorted_fields = $fields;
      }

      // Add each field to the configuration table.
      foreach ($sorted_fields as $field_name => $plugin) {
        $field_label = $plugin->adminLabel();
        if (!empty($plugin->options['label'])) {
          $field_label .= ' (' . $plugin->options['label'] . ')';
        }
       if (!empty($allow_settings['sort_fields'])) {
          $form['override']['order_fields'][$field_name]['#attributes']['class'][] = 'draggable';
        }
        $form['override']['order_fields'][$field_name]['#weight'] = !empty($block_configuration['fields'][$field_name]['weight']) ? $block_configuration['fields'][$field_name]['weight'] : '';
        if (!empty($allow_settings['hide_fields'])) {
          $form['override']['order_fields'][$field_name]['hide'] = [
            '#type' => 'checkbox',
            '#default_value' => !empty($block_configuration['fields'][$field_name]['hide']) ? $block_configuration['fields'][$field_name]['hide'] : 0,
          ];
        }
        $form['override']['order_fields'][$field_name]['label'] = [
          '#markup' => $field_label,
        ];
        if (!empty($allow_settings['sort_fields'])) {
          $form['override']['order_fields'][$field_name]['weight'] = [
            '#type' => 'weight',
            '#title' => $this->t('Weight for @title', ['@title' => $field_label]),
            '#title_display' => 'invisible',
            '#delta' => 50,
            '#default_value' => !empty($block_configuration['fields'][$field_name]['weight']) ? $block_configuration['fields'][$field_name]['weight'] : 0,
            '#attributes' => ['class' => ['field-weight']],
          ];
        }
      }
    }

    // Provide "Configure filters" form elements.
    if (!empty($allow_settings['configure_filters'])) {
      $this->view->setExposedInput($block_configuration["exposed"]);
      $exposed_form_state = new FormState();
      $exposed_form_state->setValidationEnforced();
      $exposed_form_state->set('view', $this->view);
      $exposed_form_state->set('display', $this->view->current_display);

      $exposed_form_state->setUserInput($this->view->getExposedInput());

      // Let form plugins know this is for exposed widgets.
      $exposed_form_state->set('exposed', TRUE);
      $exposed_form = [];
      $exposed_form['#info'] = array();

      // Initialize filter and sort handlers so that the exposed form alter
      // method works as expected.
      $this->view->filter = $this->getHandlers('filter');
      $this->view->sort = $this->getHandlers('sort');

      // Go through each handler and let it generate its exposed widget.
      /** @var \Drupal\views\Plugin\views\ViewsHandlerInterface $handler */
      foreach ($this->view->getDisplay()->getHandlers('filter') as $id => $handler) {
        // If the current handler is exposed...
        if ($handler->canExpose() && $handler->isExposed()) {
          // Grouped exposed filters have their own forms. Instead of rendering
          // the standard exposed form, a new Select or Radio form field is
          // rendered with the available groups. When a user chooses an option
          // the selected value is split into the operator and value that the
          // item represents.
          if ($handler->isAGroup()) {
            if (isset($block_configuration['exposed']['filter-' . $id])) {
              \Drupal::service('ctools.views.handlers.helper')
                ->convertExposedValue($handler, $block_configuration['exposed']['filter-' . $id]);
            }

            $handler->groupForm($exposed_form, $exposed_form_state);
            $id = $handler->options['group_info']['identifier'];
          }
          else {
            // If the current filter is not a group and has an exposed value in
            // the block configuration...
            if (isset($block_configuration['exposed']['filter-' . $id])) {
              \Drupal::service('ctools.views.handlers.helper')
                ->convertExposedValue($handler, $block_configuration['exposed']['filter-' . $id]);
            }

            $handler->buildExposedForm($exposed_form, $exposed_form_state);
          }

          if ($info = $handler->exposedInfo()) {
            $exposed_form['#info']['filter-' . $id] = $info;
          }
        }
      }

      /** @var \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginBase $exposed_form_plugin */
      $exposed_form_plugin = $this->view->display_handler->getPlugin('exposed_form');
      $exposed_form_plugin->exposedFormAlter($exposed_form, $exposed_form_state);

      $form['exposed'] = array(
        '#tree' => TRUE,
        '#title' => $this->t('Exposed filter values'),
        '#description' => $this->t('If a value is set for an exposed filter, it will be removed from the block display.'),
        '#type' => 'details',
        '#open' => FALSE,
      );

      foreach ($exposed_form['#info'] as $id => $info) {
        $form['exposed'][$id] = array(
          '#type' => 'item',
          '#id' => 'views-exposed-pane',
        );

        // @todo This can result in double titles for group filters.
        if (!empty($info['label'])) {
          $form['exposed'][$id]['#title'] = $info['label'];
        }

        if (!empty($info['operator']) && !empty($exposed_form[$info['operator']])) {
          $form['exposed'][$id][$info['operator']] = $exposed_form[$info['operator']];
        }

        $form['exposed'][$id][$info['value']] = $exposed_form[$info['value']];
      }
    }

    if (!empty($allow_settings['disable_filters'])) {
      $filters = $this->getHandlers('filter');
      // Add a settings form for each exposed filter to configure or hide it.
      foreach ($filters as $filter_name => $plugin) {
        if ($plugin->isExposed() && $exposed_info = $plugin->exposedInfo()) {
          // Render "Disable filters" settings form.
          if (!empty($allow_settings['disable_filters'])) {
            $form['override']['filters'][$filter_name]['disable'] = [
              '#type' => 'checkbox',
              '#title' => $this->t('Disable filter: @handler', ['@handler' => $plugin->options['expose']['label']]),
              '#default_value' => !empty($block_configuration['filter'][$filter_name]['disable']) ? $block_configuration['filter'][$filter_name]['disable'] : 0,
            ];
          }
        }
      }
    }

    // Provide "Configure sorts" block settings form.
    if (!empty($allow_settings['configure_sorts'])) {
      $sorts = $this->getHandlers('sort');
      $options = array(
        'ASC' => $this->t('Sort ascending'),
        'DESC' => $this->t('Sort descending'),
      );
      foreach ($sorts as $sort_name => $plugin) {
        $form['override']['sort'][$sort_name] = [
          '#type' => 'details',
          '#title' => $plugin->adminLabel(),
        ];
        $form['override']['sort'][$sort_name]['plugin'] = [
          '#type' => 'value',
          '#value' => $plugin,
        ];
        $form['override']['sort'][$sort_name]['order'] = array(
          '#title' => $this->t('Order'),
          '#type' => 'radios',
          '#options' => $options,
          '#default_value' => $plugin->options['order']
        );

        // Set default values for sorts for this block.
        if (!empty($block_configuration["sort"][$sort_name])) {
          $form['override']['sort'][$sort_name]['order']['#default_value'] = $block_configuration["sort"][$sort_name];
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate(ViewsBlock $block, array $form, FormStateInterface $form_state) {
    // checkout validateOptionsForm on filters before saving this.
    foreach ($form_state->getValue('exposed') as $key => $values) {
      list($type, $handler_name) = explode('-', $key, 2);
      $handler = $this->view->getDisplay()->getHandler($type, $handler_name);
      $handler_form_state = new FormState();
      $handler_form_state->setValues($values);
      $handler->validateExposed($form, $handler_form_state);
      foreach ($handler_form_state->getErrors() as $name => $message) {
        $form_state->setErrorByName($name, $message);
      }
      if (property_exists($handler, 'validated_exposed_input')) {
        $form_state->setValue(['exposed', $key], [$handler_name => $handler->validated_exposed_input]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit(ViewsBlock $block, $form, FormStateInterface $form_state) {
    // Set default value for items_per_page if left blank.
    if (empty($form_state->getValue(array('override', 'items_per_page')))) {
      $form_state->setValue(array('override', 'items_per_page'), "none");
    }

    parent::blockSubmit($block, $form, $form_state);
    $configuration = $block->getConfiguration();
    $allow_settings = array_filter($this->getOption('allow'));

    // Save "Pager type" settings to block configuration.
    if (!empty($allow_settings['pager'])) {
      if ($pager = $form_state->getValue(['override', 'pager'])) {
        $configuration['pager'] = $pager;
      }
    }

    // Save "Pager offset" settings to block configuration.
    if (!empty($allow_settings['offset'])) {
      $configuration['pager_offset'] = $form_state->getValue(['override', 'pager_offset']);
    }

    // Save "Hide fields" / "Reorder fields" settings to block configuration.
    if (!empty($allow_settings['hide_fields']) || !empty($allow_settings['sort_fields'])) {
      if ($fields = array_filter($form_state->getValue(['override', 'order_fields']))) {
        uasort($fields, '\Drupal\ctools_views\Plugin\Display\Block::sortFieldsByWeight');
        $configuration['fields'] = $fields;
      }
    }

    // Save "Configure filters" / "Disable filters" settings to block
    // configuration.
    if (!empty($allow_settings['configure_filters'])) {
      unset($configuration['exposed']);
      $configuration['exposed'] = $form_state->getValue('exposed');
    }
    unset($configuration['filter']);
    unset($configuration['filters']);
    if (!empty($allow_settings['disable_filters'])) {
      if ($filters = $form_state->getValue(['override', 'filters'])) {
        foreach ($filters as $filter_name => $filter) {
          $disable = $filter['disable'];
          if ($disable) {
            $configuration['filter'][$filter_name]['disable'] = $disable;
          }
        }
      }
    }

    // Save "Configure sorts" settings to block configuration.
    if (!empty($allow_settings['configure_sorts'])) {
      $sorts = $form_state->getValue(['override', 'sort']);
      foreach ($sorts as $sort_name => $sort) {
        $plugin = $sort['plugin'];
        // Check if we want to override the default sort order
        if ($plugin->options['order'] != $sort['order']) {
          $configuration['sort'][$sort_name] = $sort['order'];
        }
      }
    }

    $block->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function preBlockBuild(ViewsBlock $block) {
    parent::preBlockBuild($block);

    $allow_settings = array_filter($this->getOption('allow'));
    $config = $block->getConfiguration();
    list(, $display_id) = explode('-', $block->getDerivativeId(), 2);

    // Change pager offset settings based on block configuration.
    if (!empty($allow_settings['offset'])) {
      $this->view->setOffset($config['pager_offset']);
    }

    // Change pager style settings based on block configuration.
    if (!empty($allow_settings['pager'])) {
      $pager = $this->view->display_handler->getOption('pager');
      if (!empty($config['pager']) && $config['pager'] != 'view') {
        $pager['type'] = $config['pager'];
      }
      $this->view->display_handler->setOption('pager', $pager);
    }

    // Change fields output based on block configuration.
    if (!empty($allow_settings['hide_fields']) || !empty($allow_settings['sort_fields'])) {
      if (!empty($config['fields']) && $this->view->getStyle()->usesFields()) {
        $fields = $this->view->getHandlers('field');
        uasort($config['fields'], '\Drupal\ctools_views\Plugin\Display\Block::sortFieldsByWeight');
        $iterate_fields = !empty($allow_settings['sort_fields']) ? $config['fields'] : $fields;
        foreach (array_keys($iterate_fields) as $field_name) {
          // Remove each field in sequence and re-add them to sort
          // appropriately or hide if disabled.
          $this->view->removeHandler($display_id, 'field', $field_name);
          if (empty($allow_settings['hide_fields']) || (!empty($allow_settings['hide_fields']) && empty($config['fields'][$field_name]['hide']))) {
            $this->view->addHandler($display_id, 'field', $fields[$field_name]['table'], $fields[$field_name]['field'], $fields[$field_name], $field_name);
          }
        }
      }
    }

    // Change filters output based on block configuration.
    if (!empty($allow_settings['disable_filters'])) {
      $filters = $this->view->getHandlers('filter', $display_id);
      foreach ($filters as $filter_name => $filter) {
        // If we allow disabled filters and this filter is disabled, disable it
        // and continue.
        if (!empty($allow_settings['disable_filters']) && !empty($config["filter"][$filter_name]['disable'])) {
          $this->view->removeHandler($display_id, 'filter', $filter_name);
          // We don't want to needlessly set filter options later.
          unset($config['exposed']['filter-' . $filter_name]);
        }
      }
    }

    // Set an exposed filter value and remove it from the display if set in the
    // block configuration.
    if (!empty($allow_settings['configure_filters'])) {
      $exposed = $this->view->getExposedInput();

      // Loop over the exposed filter settings in the block configuration.
      foreach ($config['exposed'] as $key => $value) {
        // Load the handler related to the exposed filter.
        list($handler_type, $handler_name) = explode('-', $key, 2);
        $handler = $this->view->getDisplay()->getHandler($handler_type, $handler_name);

        // Set exposed filter input directly where they were entered in the
        // block configuration. Otherwise only set them if they haven't been set
        // already.
        if (\Drupal::service('ctools.views.handlers.helper')->validValue($config['exposed'][$key], $handler)) {
          $exposed[$handler_name] = $value[$handler_name];
        }
        elseif (!isset($exposed[$handler_name])) {
          $exposed[$handler_name] = $value[$handler_name];
        }
      }

      // Set the updated exposed filter input array on the View.
      $this->view->setExposedInput($exposed);

      // Loop over the exposed filter settings in the block configuration again.
      foreach (array_keys($config['exposed']) as $key) {
        // Load the handler related to this exposed filter.
        list($handler_type, $handler_name) = explode('-', $key, 2);

        if ($handler_type == 'filter') {
          $handler = $this->view->getDisplay()->getHandler($handler_type, $handler_name);

          // If the exposed filter input value for this filter came from the
          // block configuration, do not expose it on the View.
          if (\Drupal::service('ctools.views.handlers.helper')->validValue($config['exposed'][$key], $handler)) {
            $handler->options['value_from_block_configuration'] = TRUE;
          }
        }
      }
    }

    // Change sorts based on block configuration.
    if (!empty($allow_settings['configure_sorts'])) {
      $sorts = $this->view->getHandlers('sort', $display_id);
      foreach ($sorts as $sort_name => $sort) {
        if (!empty($config["sort"][$sort_name])) {
          $sort['order'] = $config["sort"][$sort_name];
          $this->view->setHandler($display_id, 'sort', $sort_name, $sort);
        }
      }
    }
  }

  protected function getFilterOptionsValue(array $filter, array $config) {
    $plugin_definition = \Drupal::service('plugin.manager.views.filter')->getDefinition($config['type']);
    if (is_subclass_of($plugin_definition['class'], '\Drupal\views\Plugin\views\filter\InOperator')) {
      return array_values($config['value']);
    }
    return $config['value'][$filter['expose']['identifier']];
  }

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    $filters = $this->getHandlers('filter');
    foreach ($filters as $filter_name => $filter) {
      if ($filter->isExposed() && !empty($filter->exposedInfo())) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function elementPreRender(array $element) {
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $element['#view'];

    // Exposed widgets typically only work with Ajax in core, but #2605218
    // breaks the rest of the functionality in this display and in the core
    // Block display as well. We allow non-Ajax block views to use exposed
    // filters by manually setting the #action to the current request URI.
    if (!empty($view->exposed_widgets['#action']) && !$view->ajaxEnabled()) {
      $view->exposed_widgets['#action'] = \Drupal::request()->getRequestUri();
    }

    // Allow the parent pre-render function to set the #exposed array on the
    // element. This allows us to bypass hiding widgets if the array is emptied.
    $element = parent::elementPreRender($element);

    // Loop over the filters on the current View looking for exposed filters
    // whose values have been derived from block configuration.
    if (!empty($element['#exposed'])) {
      foreach ($view->getDisplay()->getHandlers('filter') as $id => $handler) {
        /* @var \Drupal\views\Plugin\views\Filter\FilterPluginBase $handler */
        // If the current handler meets the conditions, hide its exposed widget.
        if ($handler->canExpose() && $handler->isExposed() && !empty($handler->options['value_from_block_configuration'])) {
          $element['#exposed'][$id]['#access'] = FALSE;
        }
      }

      // If there are no accessible child elements in the #exposed array other
      // than the actions, reset it to an empty array.
      if (Element::getVisibleChildren($element['#exposed']) == array('actions')) {
        $element['#exposed'] = array();
      }
    }

    return $element;
  }

  /**
   * Sort field config array by weight.
   *
   * @param $a
   * @param $b
   * @return int
   */
  public static function sortFieldsByWeight($a, $b) {
    $a_weight = isset($a['weight']) ? $a['weight'] : 0;
    $b_weight = isset($b['weight']) ? $b['weight'] : 0;
    if ($a_weight == $b_weight) {
      return 0;
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
