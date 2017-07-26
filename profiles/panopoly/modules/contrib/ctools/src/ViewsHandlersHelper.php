<?php
/**
 * @file
 * Contains ViewsHandlersHelper.php
 */

namespace Drupal\ctools;


use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\HandlerBase;
use Drupal\views\Plugin\views\ViewsHandlerInterface;

class ViewsHandlersHelper {

  public function convertExposedValue(HandlerBase $handler, $value) {
    if ($handler->isAGroup()) {
      $converted = $handler->convertExposedInput($value);
      $handler->storeGroupInput($value, $converted);
    }
    else {
      $converted = TRUE;
    }

    if ($converted) {
      // We manually validated all values on submit, so we should tell the
      // handler so. Likewise, we manipulated the value we saved and must
      // update that as well.
      if (property_exists($handler, 'validated_exposed_input')) {
        //$value = $value[$handler->options['expose']['identifier']];
        $handler->validated_exposed_input = $value;
      }

      // The value passed to acceptExposedInput() can be expecting defaults
      // that are not passed with the input values, so we have to attempt to
      // merge the expected values on the plugin before overwriting them.
      if (is_array($value[$handler->options['expose']['identifier']]) && is_array($handler->options['value'])) {
        $value[$handler->options['expose']['identifier']] = $value[$handler->options['expose']['identifier']] + $handler->options['value'];
      }
      elseif (is_string($value[$handler->options['expose']['identifier']]) && is_array($handler->options['value'])) {
        $value[$handler->options['expose']['identifier']] = ['value' => $value[$handler->options['expose']['identifier']]] + $handler->options['value'];
      }

      $rc = $handler->acceptExposedInput($value);
      $handler->storeExposedInput($value, $rc);

      if (is_array($handler->value) && is_array($handler->options['value'])) {
        $handler->options['value'] = $handler->value + $handler->options['value'];
      }
      else {
        $handler->options['value'] = $handler->value;
      }
    }
  }

  /**
   * Checks an exposed filter value array to see if it is non-empty and not All.
   *
   * @todo rename this function and document it more; it doesn't test validity.
   *
   * @param $value
   * @param $handler
   *
   * @return bool
   */
  public function validValue($value, ViewsHandlerInterface $handler) {
    $handler_name = $handler->options['id'];
    unset($value[$handler->options['expose']['operator']]);
    $filter = (bool) array_filter($value, [$this, 'valueFilter']);
    $not_all = $value[$handler_name] != 'All';
    $not_empty_or_zero = (!empty($value[$handler_name]) || (is_numeric($value[$handler_name]) && (int) $value[$handler_name] === 0));
    return ($filter && $not_all && $not_empty_or_zero);
  }

  /**
   * Filter a potential array of values to see if any are non-0 string lengths.
   *
   * @param mixed $value
   *
   * @return int
   */
  protected function valueFilter($value) {
    if (is_array($value)) {
      foreach ($value as $key => $element) {
        // If any element returns non-0, we know all we need to.
        if ($test = $this->valueFilter($element)) {
          return $test;
        }
      }
    }
    else {
      return strlen($value);
    }
  }
}
