<?php

/**
 * A PrestaShop Object Model on steroids
 */
abstract class SuperModel extends ObjectModel
{
    /**
     * {@inheritDoc}
     */
    public function validateField($field, $value, $id_lang = null, $skip = [], $human_errors = false)
    {
        static $ps_lang_default = null;
        static $ps_allow_html_iframe = null;

        if ($ps_lang_default === null) {
            $ps_lang_default = Configuration::get('PS_LANG_DEFAULT');
        }

        if ($ps_allow_html_iframe === null) {
            $ps_allow_html_iframe = (int) Configuration::get('PS_ALLOW_HTML_IFRAME');
        }

        $this->cacheFieldsRequiredDatabase();
        $data = $this->def['fields'][$field];

        // Check if field is required
        $required_fields = $this->getCachedFieldsRequiredDatabase();
        if (!$id_lang || $id_lang == $ps_lang_default) {
            if (!in_array('required', $skip) && (!empty($data['required']) || in_array($field, $required_fields))) {
                if (Tools::isEmpty($value)) {
                    if ($human_errors) {
                        return $this->trans('The %s field is required.', [$this->displayFieldName($field, get_class($this))], 'Admin.Notifications.Error');
                    } else {
                        return $this->trans('Property %s is empty.', [get_class($this) . '->' . $field], 'Admin.Notifications.Error');
                    }
                }
            }
        }

        // Default value
        if (!$value && !empty($data['default'])) {
            $value = $data['default'];
            $this->$field = $value;
        }

        // Check field values
        if (!in_array('values', $skip) && !empty($data['values']) && is_array($data['values']) && !in_array($value, $data['values'])) {
            return $this->trans('Property %1$s has a bad value (allowed values are: %2$s).', [get_class($this) . '->' . $field, implode(', ', $data['values'])], 'Admin.Notifications.Error');
        }

        // Check field size
        if (!in_array('size', $skip) && !empty($data['size'])) {
            $size = $data['size'];
            if (!is_array($data['size'])) {
                $size = ['min' => 0, 'max' => $data['size']];
            }

            $length = Tools::strlen($value);
            if ($length < $size['min'] || $length > $size['max']) {
                if ($human_errors) {
                    if (isset($data['lang']) && $data['lang']) {
                        $language = new Language((int) $id_lang);

                        return $this->trans('Your entry in field %1$s (language %2$s) exceeds max length %3$d chars (incl. html tags).', [$this->displayFieldName($field, get_class($this)), $language->name, $size['max']], 'Admin.Notifications.Error');
                    } else {
                        return $this->trans('The %1$s field is too long (%2$d chars max).', [$this->displayFieldName($field, get_class($this)), $size['max']], 'Admin.Notifications.Error');
                    }
                } else {
                    return $this->trans(
                        'The length of property %1$s is currently %2$d chars. It must be between %3$d and %4$d chars.',
                        [
                            get_class($this) . '->' . $field,
                            $length,
                            $size['min'],
                            $size['max'],
                        ],
                        'Admin.Notifications.Error'
                    );
                }
            }
        }

        // Check field validator
        if (!in_array('validate', $skip) && !empty($data['validate'])) {
            $functionName = $data['validate'];
            if (!is_callable($functionName)) {
                $functionName = 'Validate::' . $data['validate'];
            }

            if (!is_callable($functionName)) {
                throw new PrestaShopException(
                    $this->trans(
                        'Validation function not found: %s.',
                        [$data['validate']],
                        'Admin.Notifications.Error'
                    )
                );
            }

            if (!empty($value)) {
                $isValid = true;
                if (Tools::strtolower($data['validate']) === 'iscleanhtml') {
                    if (!call_user_func($functionName, $value, $ps_allow_html_iframe)) {
                        $isValid = false;
                    }
                } else {
                    if (!call_user_func($functionName, $value)) {
                        $isValid = false;
                    }
                }

                if (!$isValid) {
                    if ($human_errors) {
                        return $this->trans('The %s field is invalid.', [self::displayFieldName($field, get_class($this))], 'Admin.Notifications.Error');
                    }

                    return $this->trans('Property %s is not valid', [get_class($this) . '->' . $field], 'Admin.Notifications.Error');
                }
            }
        }

        return true;
    }
} 
