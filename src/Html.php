<?php

namespace HnhDigital\LaravelHtmlGenerator;

use HtmlGenerator\Markup;
use Illuminate\Support\Arr;

/**
 * @method class(...$arguments)
 * @method static a(...$arguments)
 * @method static button(...$arguments)
 * @method static div(...$arguments)
 * @method static input(...$arguments)
 * @method static li(...$arguments)
 * @method static p(...$arguments)
 * @method static img(...$arguments)
 * @method static span(...$arguments)
 * @method static ul(...$arguments)
 * @method static table(...$arguments)
 * @method static tbody(...$arguments)
 * @method static td(...$arguments)
 * @method static tr(...$arguments)
 *
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
     */
    public function addAction(string $url): Html
    {
        return parent::attr('action', $url);
    }

    /**
     * Add an action link.
     */
    public function action(string $text, string $controller_action, array $parameters = []): Html
    {
        return $this->addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action link (static).
     */
    public static function actionLink(string $text, string $controller_action, array $parameters = []): Html
    {
        return self::addElement('a')->text($text)
            ->href(action($controller_action, $parameters));
    }

    /**
     * Add an action href.
     */
    public function actionHref(string $action, array $parameters = []): Html
    {
        return $this->href(action($action, $parameters));
    }

    /**
     * Shortcut to set('alt', $value).
     */
    public function alt(string $value): Html
    {
        return parent::attr('alt', e($value));
    }

    /**
     * Add an array of attributes.
     */
    public function addAttributes(array $attributes): Html
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
     */
    public function addClass(array|string $value): Html
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
     */
    public function addClassIf(?bool $check, string $class_name_1 = '', string $class_name_0 = ''): Html
    {
        return $this->addClass($check ? $class_name_1 : $class_name_0);
    }

    /**
     * Alias for addClassIf.
     */
    public function classIf(?bool $check, string $class_name_1 = '', string $class_name_0 = ''): Html
    {
        return $this->addClassIf($check, $class_name_1, $class_name_0);
    }

    /**
     * Add attribute if check is true.
     *
     * @param mixed ...$attr
     */
    public function addAttrIf(?bool $check, ...$attr): Html
    {
        if ($check) {
            return $this->attr(...$attr);
        }

        return $this;
    }

    /**
     * Shortcut to set('for', $value).
     */
    public function addFor(string $value): Html
    {
        return parent::attr('for', $value);
    }

    /**
     * Create options.
     */
    public function addOptionsArray(
        array $data,
        bool|string $data_value,
        bool|string|null $data_name,
        array|string|null $selected_value = []
    ): Html {
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
     */
    public function aria(string $name, string $value): Html
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
     */
    public function data(string $name, mixed $value): Html
    {
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
    public function form(string $value): Html
    {
        return parent::attr('form', $value);
    }

    /**
     * Create a form object.
     */
    public static function addForm(array $settings = []): Html
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
    public static function getFileUploadMaxSize(string|int|null $convert_to_bytes = null): string
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
     */
    private static function parseSize(string|int $size): float
    {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^bkmgtpezy]/i', '', (string) $size);

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', (string) $size);

        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }

    /**
     * Shortcut to set('download', $value).
     */
    public function download(string $value): Html
    {
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
    public function height(string|int $value): Html
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
    public function id(string|int $value): Html
    {
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
            $label->refer($this->attributeList['id']);
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
     */
    public static function urlLink(string $text, string $url, array $parameters = [], bool $secure = null): Html
    {
        return self::createElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Add an route link.
     */
    public function url(string $text, string $url, array $parameters = [], bool $secure = null): Html
    {
        return $this->addElement('a')->text($text)
            ->href(url($url, $parameters, $secure));
    }

    /**
     * Shortcut to set('min', $value).
     */
    public function min(string|int|float $value): Html
    {
        if ($this->tag === 'input') {
            return parent::attr('min', (string) floatval($value));
        }

        return $this;
    }

    /**
     * Shortcut to set('max', $value).
     */
    public function max(string|int|float $value): Html
    {
        if ($this->tag === 'input') {
            return parent::attr('max', (string) floatval($value));
        }

        return $this;
    }

    /**
     * Shortcut to set('maxlength', $value).
     */
    public function maxlength(string|int $value): Html
    {
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
        if (!$open_normally && $this->tag === 'a') {
            return parent::attr('target', '_blank');
        }

        return $this;
    }

    /**
     * Shortcut to set('on...', $value).
     */
    public function on(string $name, string $value): Html
    {
        parent::attr('on'.$name, $value);

        return $this;
    }

    /**
     * Shortcut to set('style', 'opacity: xx').
     */
    public function opacity(string|float $value): Html
    {
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
    public function pattern(string $value): Html
    {
        return parent::attr('pattern', $value);
    }

    /**
     * Shortcut to set('placeholder', $value).
     */
    public function placeholder(string $value): Html
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
    public function method(string $value = 'POST'): Html
    {
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
    public function removeClass(string $value): Html
    {
        if (!is_null($this->attributeList['class'])) {
            unset($this->attributeList['class'][array_search($value, $this->attributeList['class'])]);
        }

        return $this;
    }

    /**
     * Shortcut to set('required', $value).
     */
    public function required(bool $required = true, bool $required_value = true): Html
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
    public function role(string $value): Html
    {
        return parent::attr('role', $value);
    }

    /**
     * Shortcut to set('rows', $value).
     */
    public function rows(int|string $rows): Html
    {
        if ($this->tag === 'textarea') {
            return parent::attr('rows', (string) $rows);
        }

        return $this;
    }

    /**
     * Add an route link.
     */
    public function route(?string $text, string $route, array $parameters = [], string $target = ''): Html
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';

        return $this->addElement('a')->text($text ?? '')
            ->href($href);
    }

    /**
     * Add an route href.
     */
    public function routeHref(?string $route, array $parameters = [], string $target = ''): Html
    {
        $href = route($route, $parameters);
        $href .= !empty($target) ? '#'.$target : '';

        return $this->href($href);
    }

    /**
     * Shortcut to set('dir', 'rtl').
     */
    public function rtl(bool $is_rtl = false): Html
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
     */
    public function selected(): Html
    {
        return parent::attr('selected', 'selected');
    }

    /**
     * Add an route link (static).
     */
    public static function routeLink(
        string $text,
        string $route,
        array $parameters = [],
        array $link_attributes = [],
        string $extra_link = ''
    ): Html {
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
    public function s(string|bool|null $return_string = true): string
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
     */
    public function setAttribute(string $name, string $value): Html
    {
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
    public function src(string $value): Html
    {
        if ($this->tag === 'img') {
            return parent::attr('src', $value);
        }

        return $this;
    }

    /**
     * Add a style based on a boolean value.
     */
    public function addStyleIf(?bool $check, string $style_1 = '', string $style_0 = ''): Html
    {
        return $this->style($check ? $style_1 : $style_0);
    }

    /**
     * Shortcut to set('style', $value).
     */
    public function style(string $value, bool $replace = false): Html
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
     */
    public function tabindex(int|string $value): Html
    {
        return parent::attr('tabindex', (string) $value);
    }

    /**
     * Shortcut to set('target', $value).
     */
    public function target(string $value): Html
    {
        return parent::attr('target', $value);
    }

    /**
     * Define text content.
     *
     * @param string $value
     * @param mixed  $args
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
     * @param mixed $args
     */
    public function textIf(?bool $test, string $value, ...$args): Html
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
     * @param string $value
     */
    public function type(string $value): Html
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
     */
    public function width(string|int $value): Html
    {
        return parent::attr('width', $value);
    }

    /**
     * Shortcut to set('value', $value).
     */
    public function value(mixed $value = ''): Html
    {
        return parent::attr('value', $value);
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
    public function valueDate(string $value = '', string $value_format = '', string $setting_format = ''): Html
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

    /**
     * Add a new element.
     *
     * @param string $tag
     * @param array  $arguments
     */
    public function __call($tag, $arguments): Html
    {
        // Capture a call to ->class()
        if ($tag === 'class') {
            return $this->addClass(...$arguments);
        }

        array_unshift($arguments, $tag);

        return call_user_func_array([$this, 'addElement'], $arguments);
    }

    public static function a(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function button(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function div(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function input(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function li(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function p(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function img(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function span(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function ul(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function table(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function tbody(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function td(...$arguments)
    {
        return self::createElement(...$arguments);
    }

    public static function tr(...$arguments)
    {
        return self::createElement(...$arguments);
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
