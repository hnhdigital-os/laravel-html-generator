<?php

namespace HnhDigital\LaravelHtmlGenerator;

use HtmlGenerator\Markup;
use Illuminate\Support\Arr;

/**
 * @method class(...$arguments)
 * @method for(...$arguments)
 *
 * @mixin Html
 */
class Html extends Markup
{
    /**
     * Current tag.
     *
     * @var string
     */
    protected $tag = 'tag';

    /**
     * @param string       $tag
     * @param array<mixed> $arguments
     */
    public function __call($tag, $arguments): Html
    {
        // Reserved word: `call` ->class()
        if ($tag === 'class') {
            return $this->addClass(...$arguments);
        }

        // Reserved word: `for` ->for()
        if ($tag === 'for') {
            return $this->addFor(...$arguments);
        }

        array_unshift($arguments, $tag);

        return call_user_func_array([$this, 'addElement'], $arguments);
    }

    /**
     * @param string       $tag
     * @param array<mixed> $arguments
     */
    public static function __callStatic($tag, $arguments): Html
    {
        array_unshift($arguments, $tag);

        return call_user_func_array(['self', 'createElement'], $arguments);
    }

    /**
     * Shortcut to set('action', $url).
     */
    public function addAction(?string $url = null): Html
    {
        if (blank($url)) {
            return $this;
        }

        return parent::attr('action', $url);
    }

    /**
     * Add an action link.
     *
     * @param array<mixed> $parameters
     */
    public function action(?string $text = null, ?string $controller_action = null, array $parameters = []): Html
    {
        if (blank($text) || blank($controller_action)) {
            return $this;
        }

        return $this->addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action link (static).
     *
     * @param array<mixed> $parameters
     */
    public static function actionLink(?string $text = null, ?string $controller_action = null, array $parameters = []): Html
    {
        $text = $text ?? '';

        if (blank($controller_action)) {
            return self::createElement('a')->text($text);
        }

        return self::createElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action href.
     *
     * @param array<mixed> $parameters
     */
    public function actionHref(?string $action = null, array $parameters = []): Html
    {
        if (blank($action)) {
            return $this;
        }

        return $this->href(action($action, $parameters));
    }

    /**
     * Shortcut to set('alt', $value).
     */
    public function alt(?string $value = null): Html
    {
        if (blank($value)) {
            return $this;
        }

        return parent::attr('alt', e($value));
    }

    /**
     * Add an array of attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function addAttributes(array $attributes = []): Html
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
     * Add an array of attributes.
     *
     * @param array<mixed> $attributes
     */
    public function attributes(array $attributes = []): Html
    {
        return $this->addAttributes($attributes);
    }

    /**
     * Add a class to classList.
     *
     * @param array<int, string>|string|null $value
     */
    public function addClass(array|string|null $value = ''): Html
    {
        if (blank($value)) {
            return $this;
        }

        $paramaters = func_get_args();

        if (count($paramaters) > 1) {
            $value = $paramaters;
        }

        if (!is_array($value)) {
            $value = explode(' ', $value);
        }

        if (!isset($this->attributeList['class'])) {
            $this->attributeList['class'] = [];
        }

        if (!is_array($this->attributeList['class'])) {
            if (filled($this->attributeList['class'])) {
                $this->attributeList['class'] = [$this->attributeList['class']];
            } else {
                $this->attributeList['class'] = [];
            }
        }

        foreach ($value as $class_name) {
            $class_name = trim($class_name);
            if (filled($class_name)) {
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
     * @param array<int, string>|string|null $class_name_1
     * @param array<int, string>|string|null $class_name_0
     */
    public function addClassIf(
        ?bool $check = false,
        array|string|null $class_name_1 = '',
        array|string|null $class_name_0 = ''
    ): Html {
        return $this->addClass($check ? $class_name_1 : $class_name_0);
    }

    /**
     * Alias for addClassIf.
     *
     * @param array<int, string>|string|null $class_name_1
     * @param array<int, string>|string|null $class_name_0
     */
    public function classIf(
        ?bool $check = false,
        array|string|null $class_name_1 = '',
        array|string|null $class_name_0 = ''
    ): Html {
        return $this->addClassIf($check, $class_name_1, $class_name_0);
    }

    /**
     * Add attribute if check is true.
     *
     * @param mixed ...$attr
     */
    public function addAttrIf(?bool $check = false, mixed ...$attr): Html
    {
        if ($check) {
            return $this->attr(...$attr);
        }

        return $this;
    }

    public function attrIf(?bool $check = false, mixed ...$attr): Html
    {
        return $this->addAttrIf($check, ...$attr);
    }

    /**
     * Shortcut to set('for', $value).
     */
    public function addFor(string $value = null): Html
    {
        return parent::attr('for', $value);
    }

    /**
     * Create options.
     *
     * @param array<mixed>             $data
     * @param array<mixed>|string|null $selected_value
     */
    public function addOptionsArray(
        array $data,
        bool|string $data_value,
        bool|string|null $data_name,
        array|string|null $selected_value = []
    ): Html {
        if (!is_array($selected_value) && (strlen($selected_value) || filled($selected_value))) {
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

            $option = $this->addElement('option')
                ->value($option_value)
                ->text($option_name);

            foreach ($data_option as $key => $value) {
                if ($key === $data_value || $key === $data_name || is_int($key)) {
                    continue;
                }

                $option->attr($key, $value);
            }
            if (filled($selected_value) && in_array($option_value, $selected_value)) {
                $option->attr('selected', 'selected');
            }
        }

        return $this;
    }

    /**
     * Shortcut to set('aria-$name', $value).
     */
    public function aria(string $name = null, string $value = null): Html
    {
        return parent::attr('aria-'.$name, $value);
    }

    /**
     * Shortcut to set('autocomplete', $value). Only works with FORM, INPUT tags.
     */
    public function autocomplete(string $value = 'off'): Html
    {
        if (in_array($this->tag, ['form', 'input', 'textarea', 'select'])) {
            return parent::attr('autocomplete', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('readonly', $value). Only works with FORM, INPUT tags.
     */
    public function readonly(bool $value = true): Html
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
     */
    public function checked(?bool $value = true, ?bool $check_value = true): Html
    {
        if (is_null($value)) {
            return $this;
        }

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
                if (!method_exists($tag_object, $name)) {
                    continue;
                }

                if (!is_array($value)) {
                    $value = [$value];
                }

                call_user_func_array([$tag_object, $name], $value);
            }
        }

        $tag_object->configDefaults($tag);

        return $tag_object;
    }

    /**
     * Shortcut to set('data-$name', $value).
     */
    public function data(?string $name = null, mixed $value = null): Html
    {
        if (is_null($name)) {
            return $this;
        }

        $value = blank($value) ? '' : $value;

        return parent::attr('data-'.$name, $value);
    }

    /**
     * Shortcut to set('disabled', $value).
     */
    public function disable(bool $value = true, bool $check_value = true): Html
    {
        if ($value === $check_value) {
            return parent::attr('disabled', 'disabled')
                ->addClass('disabled');
        }

        return $this;
    }

    /**
     * Shortcut to set('form', $value).
     */
    public function form(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('form', $value);
    }

    /**
     * Create a form object.
     *
     * @param array<string, mixed> $settings
     */
    public static function addForm(array $settings = []): Html
    {
        $form = self::createElement('form');

        if (Arr::get($settings, 'file_upload')) {
            $form->addElement('input')
                ->type('hidden')
                ->name('MAX_FILE_SIZE')
                ->value(self::getFileUploadMaxSize());
        }

        return $form;
    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize
     * and post_max_size.
     */
    public static function getFileUploadMaxSize(string|int|null $convert_to_bytes = null): int
    {
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

        return (int) ($convert_to_bytes ? $max_size : $max_size_string);
    }

    /**
     * Converts a text based size into bytes.
     */
    private static function parseSize(string|int $size): float
    {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^bkmgtpezy]/i', '', (string) $size);

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', (string) $size);

        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        if ($unit) {
            return round(floatval($size) * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round(floatval($size));
    }

    /**
     * Shortcut to set('download', $value).
     */
    public function download(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('download', $value);
    }

    /**
     * Shortcut to creating a FontAwesome item (static).
     */
    public static function icon(string $icon, string|int $size = 0, string $tag = 'i'): Html
    {
        $icon = preg_replace('/(")(.*?)(")/', '$2', $icon);

        $icon_array = explode(',', $icon, 2);
        $icon = Arr::get($icon_array, 0);

        if (Arr::has($icon_array, '1')) {
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
     */
    public function addicon(string $icon, string $tag = 'i'): Html
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
     */
    public static function h(string|int $size, string $text): Html
    {
        return self::createElement('h'.$size, $text);
    }

    /**
     * Shortcut to set('height', $value).
     */
    public function height(string|int|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

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
     */
    public function href(?string $value = ''): Html
    {
        if ($this->tag === 'a' && !is_null($value)) {
            return parent::attr('href', $value);
        }

        return $this;
    }

    /**
     * Shortcut to set('id', $value).
     */
    public function id(string|int|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return $this->set('id', $value);
    }

    /**
     * Shortcut to set('href', 'javascript:void(0)').
     */
    public function scriptLink(string|int $value = 0): Html
    {
        $value = ($value !== 0) ? '\''.urlencode(htmlspecialchars($value)).'\'' : $value;

        return $this->set('href', 'javascript:void('.$value.');');
    }

    /**
     * Shortcut for creating a label.
     */
    public function label(string $text): Html
    {
        $label = self::createElement('label');

        if (isset($this->attributeList['id'])) {
            $label->attr('refer', $this->attributeList['id']);
        }

        return $label->text($this)
            ->text($text);
    }

    /**
     * Shortcut to set('lang', $value).
     */
    public function lang(string $value): Html
    {
        return $this->set('lang', $value);
    }

    /**
     * Add an route link (static).
     *
     * @param array<mixed> $parameters
     */
    public static function urlLink(string $text, string $url, array $parameters = [], bool $secure = null): Html
    {
        return self::createElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Add an route link.
     *
     * @param array<mixed> $parameters
     */
    public function url(string $text, string $url, array $parameters = [], bool $secure = null): Html
    {
        return $this->addElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Shortcut to set('min', $value).
     */
    public function min(string|int|float|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if ($this->tag === 'input') {
            return parent::attr('min', (string) floatval($value));
        }

        return $this;
    }

    /**
     * Shortcut to set('max', $value).
     */
    public function max(string|int|float|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if ($this->tag === 'input') {
            return parent::attr('max', (string) floatval($value));
        }

        return $this;
    }

    /**
     * Shortcut to set('maxlength', $value).
     */
    public function maxlength(string|int|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if ($this->tag === 'input') {
            return parent::attr('maxlength', (string) intval($value));
        }

        return $this;
    }

    /**
     * Shortcut to set('name', $value).
     */
    public function name(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('name', $value);
    }

    /**
     * Shortcut to set('target', '_blank').
     */
    public function openNew(bool $open_normally = false): Html
    {
        if ($this->tag === 'a' && !$open_normally) {
            return parent::attr('target', '_blank');
        }

        return $this;
    }

    /**
     * Shortcut to set('on...', $value).
     */
    public function on(?string $name = null, ?string $value = null): Html
    {
        if (is_null($name) || is_null($value)) {
            return $this;
        }

        parent::attr('on'.$name, $value);

        return $this;
    }

    /**
     * Shortcut to set('style', 'opacity: xx').
     */
    public function opacity(string|float|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if (!isset($this->attributeList['style'])) {
            $this->attributeList['style'] = '';
        }

        $value = floatval($value);

        $this->attributeList['style'] .= 'opacity: '.round($value / 100, 2).';';

        return $this;
    }

    /**
     * Shortcut to set('pattern', $value).
     */
    public function pattern(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('pattern', $value);
    }

    /**
     * Shortcut to set('placeholder', $value).
     */
    public function placeholder(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('placeholder', $value);
    }

    /**
     * Prepare key => value options array for select-options.
     *
     * @param array<mixed> $options
     * @param bool         $blank_first_option
     * @param string       $value_first_option
     *
     * @return array<mixed>
     */
    public static function prepareOptions(
        array $options,
        bool $blank_first_option = false,
        string $name_first_option = '',
        string $value_first_option = ''
    ): array {
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
     */
    public function method(?string $value = 'POST'): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('method', $value);
    }

    /**
     * Shortcut to set('multiple', 'multiple').
     */
    public function multiple(): Html
    {
        return parent::attr('multiple', 'multiple');
    }

    /**
     * Remove a class from classList.
     */
    public function removeClass(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if (!is_null($this->attributeList['class'])) {
            unset($this->attributeList['class'][array_search($value, $this->attributeList['class'])]);
        }

        return $this;
    }

    /**
     * Shortcut to set('required', $value).
     */
    public function required(mixed $required = true, mixed $required_value = true): Html
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
     */
    public function role(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('role', $value);
    }

    /**
     * Shortcut to set('rows', $value).
     */
    public function rows(int|string|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if ($this->tag === 'textarea') {
            return parent::attr('rows', (string) $value);
        }

        return $this;
    }

    /**
     * Add an route link.
     *
     * @param array<mixed> $parameters
     */
    public function route(?string $text = null, ?string $route = null, array $parameters = [], string $target = ''): Html
    {
        if (is_null($text)) {
            return $this;
        }

        if (is_null($route)) {
            return $this->addElement('a')->text($text);
        }

        $href = route($route, $parameters);
        $href .= filled($target) ? '#'.$target : '';

        return $this->addElement('a')->text($text)
            ->href($href);
    }

    /**
     * Add an route href.
     *
     * @param array<mixed> $parameters
     */
    public function routeHref(?string $route = null, array $parameters = [], string $target = ''): Html
    {
        if (is_null($route)) {
            return $this;
        }

        $href = route($route, $parameters);
        $href .= filled($target) ? '#'.$target : '';

        return $this->href($href);
    }

    /**
     * Shortcut to set('dir', 'rtl').
     */
    public function rtl(bool $is_rtl = false): Html
    {
        parent::attr('dir', $is_rtl ? 'rtl' : 'ltr');

        return $this;
    }

    /**
     * Shortcut to set('selected', 'selected').
     */
    public function selected(): Html
    {
        return parent::attr('selected', 'selected');
    }

    /**
     * Add an route link (static).
     *
     * @param array<mixed>         $parameters
     * @param array<string, mixed> $link_attributes
     */
    public static function routeLink(
        ?string $text = null,
        ?string $route = null,
        array $parameters = [],
        array $link_attributes = [],
        string $extra_link = ''
    ): Html {
        if (is_null($text)) {
            return self::createElement('a');
        }

        if (is_null($route)) {
            return self::createElement('a')->text($text);
        }

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
     */
    public function s(\Closure|string|bool|null $value = true): string
    {
        if (is_callable($value)) {
            $value = $value();
        }

        return ($value) ? (string) $this : '';
    }

    /**
     * (Re)Define an attribute.
     *
     * @param string|array<mixed>|null $name
     * @param ?string                  $value
     */
    public function set($name, $value = null): Html
    {
        if (is_null($name)) {
            return $this;
        }

        if ($name === 'value') {
            $value = htmlspecialchars($value);
        }

        parent::set($name, $value);

        return $this;
    }

    /**
     * Alias for setting an attribute.
     */
    public function setAttribute(?string $name, ?string $value): Html
    {
        if (is_null($name)) {
            return $this;
        }

        return $this->set($name, $value);
    }

    /**
     * Set the tag name.
     */
    public function setTag(string $value): Html
    {
        $this->tag = $value;

        return $this;
    }

    /**
     * Shortcut to set('src', $value).
     */
    public function src(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        if ($this->tag === 'img') {
            return parent::attr('src', $value);
        }

        return $this;
    }

    /**
     * Add a style based on a boolean value.
     */
    public function addStyleIf(
        ?bool $check = false,
        ?string $style_1 = null,
        ?string $style_0 = null
    ): Html {
        return $this->style($check ? $style_1 : $style_0);
    }

    /**
     * Shortcut to set('style', $value).
     */
    public function style(?string $value = null, bool $replace = false): Html
    {
        if (is_null($value)) {
            return $this;
        }

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
     */
    public function tabindex(int|string|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('tabindex', (string) $value);
    }

    /**
     * Shortcut to set('target', $value).
     */
    public function target(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('target', $value);
    }

    /**
     * Define text content.
     *
     * @param ?string $value
     * @param mixed   $args
     */
    public function text($value, ...$args): Html
    {
        if (is_null($value)) {
            return $this;
        }

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
     * @param mixed $args
     */
    public function textIf(?bool $test = false, ?string $value = null, ...$args): Html
    {
        return $test ? $this->text($value, ...$args) : $this;
    }

    /**
     * Shortcut to set('title', $value).
     */
    public function title(?string $value): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('title', $value);
    }

    /**
     * Title toggle.
     */
    public function titleWhen(bool $is_true): Html
    {
        return parent::attr(
            'title',
            $this->offsetGet('data-title-'.($is_true ? '1' : '0'))
        );
    }

    /**
     * Shortcut to set('type', $value).
     *
     * @param ?string $value
     */
    public function type(?string $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        switch ($value) {
            case 'checkbox':
                $this->value(1);
                break;
        }

        return parent::attr('type', $value);
    }

    /**
     * Shortcut to set('width', $value).
     */
    public function width(string|int|null $value = null): Html
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::attr('width', $value);
    }

    /**
     * Shortcut to set('value', $value).
     */
    public function value(mixed $value = ''): Html
    {
        return parent::attr('value', $value ?? '');
    }

    /**
     * Shortcut to set('value', $value).
     */
    public function val(mixed $value = ''): Html
    {
        return parent::attr('value', $value);
    }

    /**
     * Shortcut to set('value', $value)
     * and set('data-datepicker-format', $setting_format).
     */
    public function valueDate(string|object $value = '', string $value_format = '', string $setting_format = ''): Html
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
     */
    public function configDefaults(string $tag): void
    {
        if (count(config('html.default.'.$tag, []))) {
            foreach (config('html.default.'.$tag) as $name => $value) {
                $this->attr($name, $value);
            }
        }
    }

    public static function a(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'a');

        return self::createElement(...$arguments);
    }

    public static function button(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'button');

        return self::createElement(...$arguments);
    }

    public static function div(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'div');

        return self::createElement(...$arguments);
    }

    public static function input(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'input');

        return self::createElement(...$arguments);
    }

    public static function textarea(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'textarea');

        return self::createElement(...$arguments);
    }

    public static function li(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'li');

        return self::createElement(...$arguments);
    }

    public static function p(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'p');

        return self::createElement(...$arguments);
    }

    public static function img(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'img');

        return self::createElement(...$arguments);
    }

    public static function span(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'span');

        return self::createElement(...$arguments);
    }

    public static function ul(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'ul');

        return self::createElement(...$arguments);
    }

    public static function table(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'table');

        return self::createElement(...$arguments);
    }

    public static function tbody(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'tbody');

        return self::createElement(...$arguments);
    }

    public static function td(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'td');

        return self::createElement(...$arguments);
    }

    public static function tr(mixed ...$arguments): Html
    {
        array_unshift($arguments, 'tr');

        return self::createElement(...$arguments);
    }
}
