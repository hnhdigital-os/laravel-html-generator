<?php

namespace Bluora\LaravelHtmlGenerator;

use HtmlGenerator\Markup;

class Html extends Markup
{
    /**
     * Auto close these tags.
     *
     * @var array
     */
    protected $autocloseTagsList = [
        'img', 'br', 'hr', 'input', 'area', 'link', 'meta', 'param',
    ];

    /**
     * Current tag.
     *
     * @var string
     */
    protected $tag = 'tag';

    /**
     * Shortcut to set('action', $url).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function addAction($url)
    {
        return parent::attr('action', $url);
    }

    /**
     * Add an action link.
     *
     * @return Html instance
     */
    public function action($text, $controller_action, $parameters = [])
    {
        return $this->addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action link (static).
     *
     * @return Html instance
     */
    public static function actionLink($text, $controller_action, $parameters = [])
    {
        return self::addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action href.
     *
     * @return Html instance
     */
    public function actionHref($action, $parameters = [])
    {
        return $this->href(action($action, $parameters));
    }

    /**
     * Shortcut to set('alt', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function alt($value)
    {
        return parent::attr('alt', e($value));
    }

    /**
     * Add an array of attributes.
     *
     * @param array $attributes
     *
     * @return Html instance
     */
    public function addAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            parent::attr($name, ...$value);
        }

        return $this;
    }

    /**
     * Add a class to classList.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function addClass($value)
    {
        $paramaters = func_get_args();
        if (count($paramaters) > 1) {
            $value = $paramaters;
        }
        if (!is_array($value)) {
            $value = explode(' ', $value);
        }
        if (!isset($this->attributeList['class']) || is_null($this->attributeList['class'])) {
            $this->attributeList['class'] = [];
        }
        if (!is_array($this->attributeList['class'])) {
            if (!empty($this->attributeList['class'])) {
                $this->attributeList['class'] = [$this->attributeList['class']];
            } else {
                $this->attributeList['class'] = [];
            }
        }
        foreach ($value as $class_name) {
            $class_name = trim($class_name);
            if (!empty($class_name)) {
                if (function_exists('hookAddClassHtmlTag')) {
                    hookAddClassHtmlTag($class_name);
                }
                $this->attributeList['class'][] = $class_name;
            }
        }

        return $this;
    }

    /**
     * Add a class based on a boolean value.
     *
     * @param bool   $check
     * @param string $class_name_1
     * @param string $class_name_0
     *
     * @return Html instance
     */
    public function addClassIf($check, $class_name_1 = '', $class_name_0 = '')
    {
        return $this->addClass($check ? $class_name_1 : $class_name_0);
    }

    /**
     * Shortcut to set('for', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function addFor($value)
    {
        return parent::attr('for', $value);
    }

    /**
     * Create options.
     */
    public function addOptionsArray($data, $data_value, $data_name, $selected_value = [])
    {
        if (!empty($selected_value) && !is_array($selected_value)) {
            $selected_value = [$selected_value];
        }
        foreach ($data as $key => $data_option) {
            
            if ($data_value === false && $data_name === false) {
                $value = 0;
                $name = 1;
                $data_option = [$key, $data_option];
            } else {
                $value = $data_value;
                $name = $data_name;
            }

            $option_value = array_get($data_option, $value, '');
            $option_name = array_get($data_option, $name, '');

            if ($option_name === 'BREAK') {
                $option_name = '--------------------';
                if ($option_value !== '') {
                    $option_name = '--- '.$option_value.' ---';
                }
                $option_value = '';
                $data_option['disabled'] = 'disabled';
            }

            $option = $this->addElement('option')->value($option_value)->text($option_name);
            foreach (['style', 'class', 'id', 'disabled'] as $attr) {
                if (isset($data_option[$attr])) {
                    $option->attr($attr, $data_option[$attr]);
                }
            }
            if (!empty($selected_value) && in_array($option_value, $selected_value)) {
                $option->selected('selected');
            }
        }

        return $this;
    }

    /**
     * Shortcut to set('aria-$name', $value).
     *
     * @param string $name
     * @param string $value
     *
     * @return Html instance
     */
    public function aria($name, $value)
    {
        return parent::attr('aria-'.$name, $value);
    }

    /**
     * Shortcut to set('autocomplete', $value). Only works with FORM, INPUT tags.
     *
     * @return Html instance
     */
    public function autocomplete($value = 'off')
    {
        if (in_array($this->tag, ['form', 'input'])) {
            return parent::attr('autocomplete', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('autofocus', $value). Only works with BUTTON, INPUT, KEYGEN, SELECT, TEXTAREA tags.
     *
     * @return Html instance
     */
    public function autofocus()
    {
        if (in_array($this->tag, ['button', 'input', 'keygen', 'select', 'textarea'])) {
            return parent::attr('autofocus', 'autofocus');
        }

        return $this;
    }

    /**
     * Shortcut to set('checked', $value).
     *
     * @param bool $value
     * @param bool $check_value
     *
     * @return Html instance
     */
    public function checked($value = true, $check_value = true)
    {
        if ($value === $check_value) {
            return parent::attr('checked', 'checked');
        }

        return $this;
    }

    /**
     * Create a new tag.
     *
     * @param string $tag
     * @param mixed  $attributes1
     * @param mixed  $attributes2
     *
     * @return Markup instance
     */
    public static function createElement($tag = '', $attributes1 = [], $attributes2 = [])
    {
        $tag_object = parent::createElement($tag);
        $tag_object->setTag($tag);
        $attributes = $attributes1;
        if (!is_array($attributes1) && strlen($attributes1) > 0) {
            $tag_object->text($attributes1);
            $attributes = $attributes2;
        }
        if (is_array($attributes)) {
            foreach ($attributes as $name => $value) {
                if (method_exists($tag_object, $name)) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    call_user_func_array([$tag_object, $name], $value);
                }
            }
        }

        return $tag_object;
    }

    /**
     * Shortcut to set('data-$name', $value).
     *
     * @param string $name
     * @param string $value
     *
     * @return Html instance
     */
    public function data($name, $value)
    {
        return parent::attr('data-'.$name, $value);
    }

    /**
     * Shortcut to set('disabled', $value).
     *
     * @param bool $value
     * @param bool $check_value
     *
     * @return Html instance
     */
    public function disable($value = true, $check_value = true)
    {
        if ($value === $check_value) {
            return parent::attr('disabled', 'disabled');
        }

        return $this;
    }

    /**
     * Shortcut to set('form', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function form($value)
    {
        return parent::attr('form', $value);
    }

    /**
     * Create a form object.
     */
    public static function addForm($settings = [])
    {
        $form = Html::createElement('form');

        if (array_get($settings, 'file_upload')) {
            $form->addElement('input')->type('hidden')->name('MAX_FILE_SIZE')->value(self::getFileUploadMaxSize());
        }

        return $form;
    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize
     * and post_max_size.
    *
     * @return integer|string
     */
    public static function getFileUploadMaxSize($convert_to_bytes = true)
    {
        $max_size = -1;
        $max_size_string = '0B';

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size_string = ini_get('post_max_size');
            $max_size = self::parseSize($max_size_string);

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
                $max_size_string = ini_get('upload_max_filesize');
            }
        }

        return $convert_to_bytes ? $max_size : $max_size_string;
    }

    /**
     * Converts a text based size into bytes.
     *
     * @param  string $size
     *
     * @return int
     */
    private static function parseSize($size)
    {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); 

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size);

        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }

    /**
     * Shortcut to set('download', $value).
     *
     * @return Html instance
     */
    public function download($value)
    {
        return parent::attr('download', $value);
    }

    /**
     * Shortcut to creating a FontAwesome item (static).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public static function icon($icon, $size = 0, $tag = 'i')
    {
        $icon = ($icon[0] === '-') ? substr($icon, 1) : 'fa-'.$icon;
        $size = ($size > 0) ? ' fa-'.$size : '';
        $fa = self::$tag()->addClass('fa '.$icon.$size)->aria('hidden', 'true');

        return $fa;
    }

    /**
     * Shortcut to creating a FontAwesome item.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function addicon($icon, $tag = 'i')
    {
        $icon = static::icon($icon, $tag);

        return $this->addElement($icon);
    }

    /**
     * Get the tag name.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Shortcut to createElement('h'.$size, $text).
     *
     * @param int    $size
     * @param string $text
     *
     * @return Html instance
     */
    public static function h($size, $text)
    {
        return self::createElement('h'.$size, $text);
    }

    /**
     * Shortcut to set('height', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function height($value)
    {
        return parent::attr('height', $value);
    }

    /**
     * Shortcut to set('height', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function hidden()
    {
        if (!isset($this->attributeList['style'])) {
            $this->attributeList['style'] = '';
        }
        $this->attributeList['style'] .= 'display:hidden;';

        return $this;
    }

    /**
     * Shortcut to set('href', $value). Only works with A tags.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function href($value = '')
    {
        if ($this->tag === 'a') {
            return parent::attr('href', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('id', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function id($value)
    {
        return $this->set('id', $value);
    }

    /**
     * Shortcut to set('href', 'javascript:void(0)').
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function scriptLink($value = 0)
    {
        $value = ($value !== 0) ? '\''.urlencode(htmlspecialchars($value)).'\'' : $value;

        return $this->set('href', 'javascript:void('.$value.');');
    }

    /**
     * Shortcut for creating a label.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function label($text)
    {
        $label = self::createElement('label');

        if (isset($this->attributeList['id'])) {
            $label->refer($this->attributeList['id']);
        }

        return $label->text($this)
            ->text($text);
    }

    /**
     * Shortcut to set('lang', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function lang($value)
    {
        return $this->set('lang', $value);
    }

    /**
     * Add an route link (static).
     *
     * @return Html instance
     */
    public static function urlLink($text, $url, $parameters = [], $secure = null)
    {
        return $this->createElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Add an route link.
     *
     * @return Html instance
     */
    public function url($text, $url, $parameters = [], $secure = null)
    {
        return $this->addElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Shortcut to set('min', $value).
     *
     * @param int $value
     *
     * @return Html instance
     */
    public function min($value)
    {
        if ($this->tag === 'input') {
            return parent::attr('min', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('max', $value).
     *
     * @param int $value
     *
     * @return Html instance
     */
    public function max($value)
    {
        if ($this->tag === 'input') {
            return parent::attr('max', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('maxlength', $value).
     *
     * @param int $value
     *
     * @return Html instance
     */
    public function maxlength($value)
    {
        if ($this->tag === 'input') {
            return parent::attr('maxlength', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('name', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function name($value)
    {
        return parent::attr('name', $value);
    }

    /**
     * Shortcut to set('target', '_blank').
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function openNew($open_normally = false)
    {
        if (!$open_normally && $this->tag === 'a') {
            return parent::attr('target', '_blank');
        }

        return $this;
    }

    /**
     * Shortcut to set('on...', $value).
     *
     * @param string $name
     * @param string $value
     *
     * @return Html instance
     */
    public function on($name, $value)
    {
        parent::attr('on'.$name, $value);

        return $this;
    }

    /**
     * Shortcut to set('style', 'opacity: xx').
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function opacity($value)
    {
        if (!isset($this->attributeList['style'])) {
            $this->attributeList['style'] = '';
        }
        $this->attributeList['style'] .= 'opacity: '.round($value / 100, 2).';';

        return $this;
    }

    /**
     * Shortcut to set('pattern', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function pattern($value)
    {
        return parent::attr('pattern', $value);
    }

    /**
     * Shortcut to set('placeholder', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function placeholder($value)
    {
        return parent::attr('placeholder', $value);
    }

    /**
     * Prepare key => value options array for select-options.
     *
     * @param array  $options
     * @param bool   $blank_first_option
     * @param string $value_first_option
     *
     * @return Html instance
     */
    public static function prepareOptions($options, $blank_first_option = false, $value_first_option = '')
    {
        $options = array_map(function ($key, $value) {
            return [$key, $value];
        }, array_keys($options), array_values($options));
        if ($blank_first_option) {
            array_unshift($options, [$value_first_option, '']);
        }

        return $options;
    }

    /**
     * Shortcut to set('method', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function method($value = 'POST')
    {
        return parent::attr('method', $value);
    }

    /**
     * Shortcut to set('multiple', 'multiple').
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function multiple()
    {
        return parent::attr('multiple', 'multiple');
    }

    /**
     * Remove a class from classList.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function removeClass($value)
    {
        if (!is_null($this->attributeList['class'])) {
            unset($this->attributeList['class'][array_search($value, $this->attributeList['class'])]);
        }

        return $this;
    }

    /**
     * Shortcut to set('required', $value).
     *
     * @param bool $required
     * @param bool $required_value
     *
     * @return Html instance
     */
    public function required($required = true, $required_value = true)
    {
        if ($required == $required_value) {
            $this->addClass('required');
            $this->aria('required', 'true');

            return parent::attr('required', 'required');
        }

        return $this;
    }

    /**
     * Shortcut to set('role', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function role($value)
    {
        return parent::attr('role', $value);
    }

    /**
     * Shortcut to set('rows', $value).
     *
     * @param int $rows
     *
     * @return Html instance
     */
    public function rows($rows)
    {
        if ($this->tag === 'textarea') {
            return parent::attr('rows', $rows);
        }

        return $this;
    }

    /**
     * Add an route link.
     *
     * @return Html instance
     */
    public function route($text, $route, $parameters = [], $target = '')
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';
        return $this->addElement('a')->text($text)
            ->href($href);
    }

    /**
     * Add an route href.
     *
     * @return Html instance
     */
    public function routeHref($route, $parameters = [], $target = '')
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';
        return $this->href($href);
    }

    /**
     * Shortcut to set('selected', 'selected').
     *s.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function rtl($is_rtl = false)
    {
        if ($is_rtl) {
            parent::attr('dir', 'rtl');
        } else {
            parent::attr('dir', 'ltr');
        }

        return $this;
    }

    /**
     * Shortcut to set('selected', 'selected').
     *s.
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function selected()
    {
        return parent::attr('selected', 'selected');
    }

    /**
     * Add an route link (static).
     *
     * @return Html instance
     */
    public static function routeLink($text, $route, $parameters = [], $link_attributes = [], $extra_link = '')
    {
        $element = self::createElement('a')->text($text)
            ->href(route($route, $parameters).$extra_link);

        foreach ($link_attributes as $method_name => $value) {
            if (method_exists($element, $method_name)) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                $element->$method_name(...$value);
            }
        }

        return $element;
    }

    /**
     * Return string of this object.
     *
     * @return string
     */
    public function s($return_string = true)
    {
        return ($return_string) ? (string) $this : '';
    }

    /**
     * (Re)Define an attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @return Markup instance
     */
    public function set($name, $value = null)
    {
        if ($name === 'value') {
            $value = htmlspecialchars($value);
        }
        parent::set($name, $value);

        return $this;
    }

    /**
     * Alias for setting an attribute
     *
     * @param string $name
     * @param string $value
     *
     * @return Markup instance
     */
    public function setAttribute($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Set the tag name.
     *
     * @return string
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Shortcut to set('src', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function src($value)
    {
        if ($this->tag === 'img') {
            return parent::attr('src', $value);
        }

        return $this;
    }

    /**
     * Add a style based on a boolean value.
     *
     * @param bool   $check
     * @param string $style_1
     * @param string $style_0
     *
     * @return Html instance
     */
    public function addStyleIf($check, $style_1 = '', $style_0 = '')
    {
        return $this->style($check ? $style_1 : $style_0);
    }

    /**
     * Shortcut to set('style', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function style($value, $replace = false)
    {
        if ($replace) {
            return parent::attr('style', $value);
        }
        if (!isset($this->attributeList['style'])) {
            $this->attributeList['style'] = '';
        }
        $this->attributeList['style'] .= $value;

        return $this;
    }

    /**
     * Shortcut to set('tabindex', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function tabindex($value)
    {
        return parent::attr('tabindex', $value);
    }

    /**
     * Shortcut to set('target', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function target($value)
    {
        return parent::attr('target', $value);
    }

    /**
     * Define text content.
     *
     * @param string $value
     *
     * @return Markup instance
     */
    public function text($value)
    {
        if ($this->tag === 'textarea') {
            $value = htmlspecialchars($value);
        }
        parent::text($value);

        return $this;
    }

    /**
     * Shortcut to set('title', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function title($value)
    {
        return parent::attr('title', $value);
    }

    /**
     * Shortcut to set('type', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function type($value)
    {
        switch ($value) {
            case 'checkbox':
                $this->value(1);
                break;
        }

        return parent::attr('type', $value);
    }

    /**
     * Shortcut to set('width', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function width($value)
    {
        return parent::attr('width', $value);
    }

    /**
     * Shortcut to set('value', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function value($value = '')
    {
        return parent::attr('value', $value);
    }

    /**
     * Shortcut to set('value', $value).
     *
     * @param string $value
     *
     * @return Html instance
     */
    public function valueDate($value = '', $value_format = '', $setting_format = '')
    {
        if (is_object($value)) {
            $value = $value->format($value_format);
        } else {
            $value = '';
        }
        if ($setting_format !== false) {
            $this->data('datepicker-format', $setting_format);
        }

        return parent::attr('value', $value);
    }

    /**
     * Add a new element.
     *
     * @param string $tag
     * @param array  $arguments
     *
     * @return Html instance
     */
    public function __call($tag, $arguments)
    {
        array_unshift($arguments, $tag);

        return call_user_func_array([$this, 'addElement'], $arguments);
    }

    /**
     * Create a new element.
     *
     * @param string $tag
     * @param array  $arguments
     *
     * @return Html instance
     */
    public static function __callStatic($tag, $arguments)
    {
        array_unshift($arguments, $tag);

        return call_user_func_array(['self', 'createElement'], $arguments);
    }
}
