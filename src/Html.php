<?php

namespace HnhDigital\LaravelHtmlGenerator;

use HtmlGenerator\Markup;
use Illuminate\Support\Arr;

/**
 * @method static a(...$arguments)
 * @method static button(...$arguments)
 * @method static div(...$arguments)
 * @method static input(...$arguments)
 * @method static li(...$arguments)
 * @method static p(...$arguments)
 * @method static img(...$arguments)
 * @method static span(...$arguments)
 * @method static td(...$arguments)
 * @method static tr(...$arguments)
 * @method static val(...$arguments)
 * @method static ul(...$arguments)
 * @method static table(...$arguments)
 * @mixin Html
 */
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
     */
    public function addAction($url): Html
    {
        return parent::attr('action', $url);
    }

    /**
     * Add an action link.
     */
    public function action($text, $controller_action, $parameters = []): Html
    {
        return $this->addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action link (static).
     */
    public static function actionLink($text, $controller_action, $parameters = []): Html
    {
        return self::addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action href.
     */
    public function actionHref($action, $parameters = []): Html
    {
        return $this->href(action($action, $parameters));
    }

    /**
     * Shortcut to set('alt', $value).
     *
     * @param string $value
     */
    public function alt($value): Html
    {
        return parent::attr('alt', e($value));
    }

    /**
     * Add an array of attributes.
     *
     * @param array $attributes
     */
    public function addAttributes($attributes): Html
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
     */
    public function addClass($value): Html
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
     */
    public function addClassIf($check, $class_name_1 = '', $class_name_0 = ''): Html
    {
        return $this->addClass($check ? $class_name_1 : $class_name_0);
    }

    /**
     * Add attribute if check is true.
     *
     * @param bool $check
     * @param ...$attr
     */
    public function addAttrIf($check, ...$attr): Html
    {
        if ($check) {
            return $this->attr(...$attr);
        }

        return $this;
    }

    /**
     * Shortcut to set('for', $value).
     *
     * @param string $value
     */
    public function addFor($value): Html
    {
        return parent::attr('for', $value);
    }

    /**
     * Create options.
     */
    public function addOptionsArray($data, $data_value, $data_name, $selected_value = []): Html
    {
        if (!is_array($selected_value) && (strlen($selected_value) || !empty($selected_value))) {
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

            $option_value = Arr::get($data_option, $value, '');
            $option_name = Arr::get($data_option, $name, '');

            if ($option_value === 'BREAK') {
                $option_value = '--------------------';
                if ($option_name !== '') {
                    $option_name = '--- '.$option_name.' ---';
                }
                $option_value = '';
                $data_option['disabled'] = 'disabled';
            }

            $option = $this->addElement('option')->value($option_value)->text($option_name);
            foreach ($data_option as $key => $value) {
                if ($key === $data_value || $key === $data_name || is_int($key)) {
                    continue;
                }

                $option->attr($key, $value);
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
     */
    public function aria($name, $value): Html
    {
        return parent::attr('aria-'.$name, $value);
    }

    /**
     * Shortcut to set('autocomplete', $value). Only works with FORM, INPUT tags.
     *
     * @param string $value
     */
    public function autocomplete($value = 'off'): Html
    {
        if (in_array($this->tag, ['form', 'input', 'textarea', 'select'])) {
            return parent::attr('autocomplete', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('readonly', $value). Only works with FORM, INPUT tags.
     *
     * @param bool $value
     */
    public function readonly($value = true): Html
    {
        if (in_array($this->tag, ['form', 'input', 'textarea', 'select'])) {
            return parent::attr('readonly', $value ? 'readonly' : '');
        }

        return $this;
    }

    /**
     * Shortcut to set('autofocus', $value). Only works with BUTTON, INPUT, KEYGEN, SELECT, TEXTAREA tags.
     */
    public function autofocus(): Html
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
     */
    public function checked($value = true, $check_value = true): Html
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
     */
    public static function createElement($tag = '', $attributes1 = [], $attributes2 = []): Html
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

        $tag_object->configDefaults($tag);

        return $tag_object;
    }

    /**
     * Shortcut to set('data-$name', $value).
     *
     * @param string $name
     * @param string $value
     */
    public function data($name, $value): Html
    {
        return parent::attr('data-'.$name, $value);
    }

    /**
     * Shortcut to set('disabled', $value).
     *
     * @param bool $value
     * @param bool $check_value
     */
    public function disable($value = true, $check_value = true): Html
    {
        if ($value === $check_value) {
            return parent::attr('disabled', 'disabled')
                ->addClass('disabled');
        }

        return $this;
    }

    /**
     * Shortcut to set('form', $value).
     *
     * @param string $value
     */
    public function form($value): Html
    {
        return parent::attr('form', $value);
    }

    /**
     * Create a form object.
     */
    public static function addForm($settings = []): Html
    {
        $form = self::createElement('form');

        if (Arr::get($settings, 'file_upload')) {
            $form->addElement('input')->type('hidden')->name('MAX_FILE_SIZE')->value(self::getFileUploadMaxSize());
        }

        return $form;
    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize
     * and post_max_size.
     *
     * @param string|int|bool $convert_to_bytes
     *
     * @return int|string
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
     * @param string|int $size
     */
    private static function parseSize($size): float
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
     */
    public function download($value): Html
    {
        return parent::attr('download', $value);
    }

    /**
     * Shortcut to creating a FontAwesome item (static).
     *
     * @param string     $value
     * @param string|int $size
     * @param string     $ta
     */
    public static function icon($icon, $size = 0, $tag = 'i'): Html
    {
        $icon = preg_replace('/(")(.*?)(")/', '$2', $icon);

        $icon_array = explode(',', $icon, 2);
        $icon = Arr::get($icon_array, 0);
        if (Arr::has($icon_array, 1)) {
            $attributes = explode(',', Arr::get($icon_array, 1, ''));
        }

        if (substr($icon, 1, 1) == ' ') {
            $type = substr($icon, 0, 1);
            $icon = substr($icon, 2);
        } else {
            $type = config('html.icon.default.type', 'l');
        }
        $icon = ($icon[0] === '-') ? substr($icon, 1) : 'fa-'.$icon;
        $size = ($size > 0) ? ' fa-'.$size : '';
        $fa = self::$tag()->addClass('fa'.$type.' fa-fw '.$icon.$size)->aria('hidden', 'true');

        if (isset($attributes) && is_array($attributes)) {
            foreach ($attributes as $attr) {
                list($attr_name, $attr_value) = explode('=', $attr);
                switch ($attr_name) {
                    case 'transform':
                        $fa->data('fa-'.$attr_name, $attr_value);
                        break;
                    default:
                        $fa->attr($attr_name, $attr_value);
                        break;
                }
            }
        }

        return $fa;
    }

    /**
     * Shortcut to creating a FontAwesome item.
     *
     * @param string $icon
     * @param string $tag
     */
    public function addicon($icon, $tag = 'i'): Html
    {
        $icon = static::icon($icon, $tag);

        return $this->addElement($icon);
    }

    /**
     * Get the tag name.
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Shortcut to createElement('h'.$size, $text).
     *
     * @param int|string $size
     * @param string     $text
     */
    public static function h($size, $text): Html
    {
        return self::createElement('h'.$size, $text);
    }

    /**
     * Shortcut to set('height', $value).
     *
     * @param string|int $value
     */
    public function height($value): Html
    {
        return parent::attr('height', $value);
    }

    /**
     * Sets a style making this element hidden.
     */
    public function hidden(): Html
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
     */
    public function href($value = ''): Html
    {
        if ($this->tag === 'a') {
            return parent::attr('href', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('id', $value).
     *
     * @param string|int $value
     */
    public function id($value): Html
    {
        return $this->set('id', $value);
    }

    /**
     * Shortcut to set('href', 'javascript:void(0)').
     *
     * @param string|int $value
     */
    public function scriptLink($value = 0): Html
    {
        $value = ($value !== 0) ? '\''.urlencode(htmlspecialchars($value)).'\'' : $value;

        return $this->set('href', 'javascript:void('.$value.');');
    }

    /**
     * Shortcut for creating a label.
     *
     * @param string $value
     */
    public function label($text): Html
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
     */
    public function lang($value): Html
    {
        return $this->set('lang', $value);
    }

    /**
     * Add an route link (static).
     */
    public static function urlLink($text, $url, $parameters = [], $secure = null): Html
    {
        return $this->createElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Add an route link.
     */
    public function url($text, $url, $parameters = [], $secure = null): Html
    {
        return $this->addElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Shortcut to set('min', $value).
     *
     * @param string|int $value
     */
    public function min($value): Html
    {
        if ($this->tag === 'input') {
            return parent::attr('min', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('max', $value).
     *
     * @param string|int $value
     */
    public function max($value): Html
    {
        if ($this->tag === 'input') {
            return parent::attr('max', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('maxlength', $value).
     *
     * @param string|int $value
     */
    public function maxlength($value): Html
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
     */
    public function name($value): Html
    {
        if ($value === false) {
            return $this;
        }

        return parent::attr('name', $value);
    }

    /**
     * Shortcut to set('target', '_blank').
     *
     * @param string $value
     */
    public function openNew($open_normally = false): Html
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
     */
    public function on($name, $value): Html
    {
        parent::attr('on'.$name, $value);

        return $this;
    }

    /**
     * Shortcut to set('style', 'opacity: xx').
     *
     * @param string $value
     */
    public function opacity($value): Html
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
     */
    public function pattern($value): Html
    {
        return parent::attr('pattern', $value);
    }

    /**
     * Shortcut to set('placeholder', $value).
     *
     * @param string $value
     */
    public function placeholder($value): Html
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
     * @return array
     */
    public static function prepareOptions($options, $blank_first_option = false, $name_first_option = '', $value_first_option = ''): array
    {
        $options = array_map(function ($key, $value) {
            return [$key, $value];
        }, array_keys($options), array_values($options));

        if ($blank_first_option) {
            array_unshift($options, [$value_first_option, $name_first_option]);
        }

        return $options;
    }

    /**
     * Shortcut to set('method', $value).
     *
     * @param string $value
     */
    public function method($value = 'POST'): Html
    {
        return parent::attr('method', $value);
    }

    /**
     * Shortcut to set('multiple', 'multiple').
     *
     * @param string $value
     */
    public function multiple(): Html
    {
        return parent::attr('multiple', 'multiple');
    }

    /**
     * Remove a class from classList.
     *
     * @param string $value
     */
    public function removeClass($value): Html
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
     */
    public function required($required = true, $required_value = true): Html
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
     */
    public function role($value): Html
    {
        return parent::attr('role', $value);
    }

    /**
     * Shortcut to set('rows', $value).
     *
     * @param int $rows
     */
    public function rows($rows): Html
    {
        if ($this->tag === 'textarea') {
            return parent::attr('rows', $rows);
        }

        return $this;
    }

    /**
     * Add an route link.
     *
     * @param string $text
     * @param string $route
     * @param array  $parameters
     * @param string $target
     */
    public function route($text, $route, $parameters = [], $target = ''): Html
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';

        return $this->addElement('a')->text($text)
            ->href($href);
    }

    /**
     * Add an route href.
     *
     * @param string $route
     * @param array  $parameters
     * @param string $target
     */
    public function routeHref($route, $parameters = [], $target = ''): Html
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';

        return $this->href($href);
    }

    /**
     * Shortcut to set('selected', 'selected').
     *
     * @param string|bool $value
     */
    public function rtl($is_rtl = false): Html
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
     *
     * @param string $value
     */
    public function selected(): Html
    {
        return parent::attr('selected', 'selected');
    }

    /**
     * Add an route link (static).
     *
     * @param string $text
     * @param string $route
     * @param array  $parameters
     * @param array  $link_attributes
     * @param string $extra_link
     */
    public static function routeLink($text, $route, $parameters = [], $link_attributes = [], $extra_link = ''): Html
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
     * @param bool|string $return_string
     */
    public function s($return_string = true): string
    {
        return ($return_string) ? (string) $this : '';
    }

    /**
     * (Re)Define an attribute.
     *
     * @param string $name
     * @param string $value
     */
    public function set($name, $value = null): Html
    {
        if ($name === 'value') {
            $value = htmlspecialchars($value);
        }
        parent::set($name, $value);

        return $this;
    }

    /**
     * Alias for setting an attribute.
     *
     * @param string $name
     * @param string $value
     */
    public function setAttribute($name, $value): Html
    {
        return $this->set($name, $value);
    }

    /**
     * Set the tag name.
     *
     * @param string $value
     */
    public function setTag($value): Html
    {
        $this->tag = $value;

        return $this;
    }

    /**
     * Shortcut to set('src', $value).
     *
     * @param string $value
     */
    public function src($value): Html
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
     */
    public function addStyleIf($check, $style_1 = '', $style_0 = ''): Html
    {
        return $this->style($check ? $style_1 : $style_0);
    }

    /**
     * Shortcut to set('style', $value).
     *
     * @param string $value
     * @param bool   $replace
     */
    public function style($value, $replace = false): Html
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
     */
    public function tabindex($value): Html
    {
        return parent::attr('tabindex', $value);
    }

    /**
     * Shortcut to set('target', $value).
     *
     * @param string $value
     */
    public function target($value): Html
    {
        return parent::attr('target', $value);
    }

    /**
     * Define text content.
     *
     * @param string $value
     * @param array  $args
     */
    public function text($value, ...$args): Html
    {
        if (count($args)) {
            $value = sprintf($value, ...$args);
        }

        if ($this->tag === 'textarea') {
            $value = htmlspecialchars($value);
        }

        parent::text($value);

        return $this;
    }

    /**
     * Define text content if test is true.
     *
     * @param bool   $test
     * @param string $value
     * @param array  $args
     */
    public function textIf($test, $value, ...$args): Html
    {
        return $test ? $this->text($value, ...$args) : $this;
    }

    /**
     * Shortcut to set('title', $value).
     *
     * @param string $value
     */
    public function title($value): Html
    {
        return parent::attr('title', $value);
    }

    /**
     * Title toggle.
     *
     * @param bool $is_true
     */
    public function titleWhen($is_true): Html
    {
        return parent::attr(
            'title',
            $this->offsetGet('data-title-'.($is_true ? '1' : '0'))
        );
    }

    /**
     * Shortcut to set('type', $value).
     *
     * @param string $value
     */
    public function type($value): Html
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
     * @param string|int $value
     */
    public function width($value): Html
    {
        return parent::attr('width', $value);
    }

    /**
     * Shortcut to set('value', $value).
     *
     * @param string|int $value
     */
    public function value($value = ''): Html
    {
        return parent::attr('value', $value);
    }

    /**
     * Shortcut to set('value', $value).
     *
     * @param string|int|null $value
     */
    public function val($value = ''): Html
    {
        return parent::attr('value', $value);
    }

    /**
     * Shortcut to set('value', $value)
     * and set('data-datepicker-format', $setting_format).
     *
     * @param string $value
     * @param string $value_format
     * @param string $setting_format
     */
    public function valueDate($value = '', $value_format = '', $setting_format = ''): Html
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
     * Apply any defaults from configuration.
     *
     * @param string $tag
     */
    public function configDefaults($tag): void
    {
        if (count(config('html.default.'.$tag, []))) {
            foreach (config('html.default.'.$tag) as $name => $value) {
                $this->attr($name, $value);
            }
        }
    }

    /**
     * Add a new element.
     *
     * @param string $tag
     * @param array  $arguments
     */
    public function __call($tag, $arguments): Html
    {
        array_unshift($arguments, $tag);

        return call_user_func_array([$this, 'addElement'], $arguments);
    }

    /**
     * Create a new element.
     *
     * @param string $tag
     * @param array  $arguments
     */
    public static function __callStatic($tag, $arguments): Html
    {
        array_unshift($arguments, $tag);

        return call_user_func_array(['self', 'createElement'], $arguments);
    }
}
