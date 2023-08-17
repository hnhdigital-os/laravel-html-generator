<?php

namespace HnhDigital\LaravelHtmlGenerator;

use ArrayAccess;

if (!defined('ENT_XML1')) {
    define('ENT_XML1', 16);
}
if (!defined('ENT_XHTML')) {
    define('ENT_XHTML', 32);
}

/**
 * @implements \ArrayAccess<mixed, mixed>
 */
class Markup implements ArrayAccess
{
    /**
     * Specifies if attribute values and text input sould be protected from XSS injection
     */
    public static bool $avoidXSS = false;

    /**
     * The language convention used for XSS avoiding.
     */
    public static int $outputLanguage = ENT_XML1;

    protected static ?self $instance = null;

    protected ?self $top = null;
    protected ?self $parent = null;

    protected ?string $tag = null;

    /**
     * @var array<mixed>
     */
    protected ?array $content = null;
    protected string $text = '';

    protected bool $autoclosed = false;

    /**
     * @var array<int, string>
     */
    protected array $autocloseTagsList = [
        'img', 'br', 'hr', 'input', 'area', 'link', 'meta', 'param', 'base', 'col', 'command', 'keygen', 'source'
    ];
    
    /**
     * @var array<mixed>
     */
    public ?array $attributeList = null;

    protected function __construct(string $tag, ?self $top = null)
    {
        $this->tag = $tag;
        $this->top =& $top;
        $this->attributeList = [];
        $this->content = [];
        $this->autoclosed = in_array($this->tag, $this->autocloseTagsList);
        $this->text = '';
    }

    /**
     * Alias for getParent()
     */
    public function __invoke(): self
    {
        return $this->getParent();
    }

    /**
     * Create a new Markup
     */
    public static function createElement(string $tag = ''): self
    {
        /* @phpstan-ignore-next-line */
        self::$instance = new static($tag);

        return self::$instance;
    }

    /**
     *
     * Add element at an existing Markup
     */
    public function addElement(self|string $tag = ''): self
    {
        if (is_object($tag) && $tag instanceof self) {
            $htmlTag = clone $tag;
        } else {
            /* @phpstan-ignore-next-line */
            $htmlTag = new static($tag);
        }

        $htmlTag->top = $this->getTop();
        $htmlTag->parent = &$this;

        $this->content[] = $htmlTag;

        return $htmlTag;
    }

    /**
     * (Re)Define an attribute or many attributes.
     * @param string|array<mixed> $attribute
     */
    public function set(string|array $attribute, ?string $value = null): self
    {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $value) {
                $this[$key] = $value;
            }
        } else {
            $this[$attribute] = $value;
        }

        return $this;
    }

    /**
     * alias to method "set"
     * @param string|array<mixed> $attribute
     */
    public function attr($attribute, ?string $value = null): self
    {
        return call_user_func_array(array($this, 'set'), func_get_args());
    }

    /**
     * Checks if an attribute is set for this tag and not null
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributeList[$offset]);
    }

    /**
     * Returns the value the attribute set for this tag
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->attributeList[$offset] : null;
    }

    /**
     * Sets the value an attribute for this tag
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributeList[$offset] = $value;
    }

    /**
     * Removes an attribute
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->attributeList[$offset]);
        }
    }

    /**
     * Define text content.
     */
    public function text(?string $value): self
    {
        $this->addElement('')->text = static::$avoidXSS ? static::unXSS($value) : $value;

        return $this;
    }

    /**
     * Returns the top element
     */
    public function getTop(): self
    {
        return $this->top === null ? $this : $this->top;
    }

    /**
     *
     * Return parent of current element
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Return first child of parent of current object
     */
    public function getFirst(): ?self
    {
        return is_null($this->parent) ? null : $this->parent->content[0];
    }

    /**
     * Return previous element or itself.
     */
    public function getPrevious(): ?static
    {
        $prev = $this;

        if (! is_null($this->parent)) {
            foreach ($this->parent->content as $c) {
                if ($c === $this) {
                    break;
                }

                $prev = $c;
            }
        }

        return $prev;
    }

    public function getNext(): ?self
    {
        $next = null;
        $find = false;
        if (!is_null($this->parent)) {
            foreach ($this->parent->content as $c) {
                if ($find) {
                    $next = &$c;
                    break;
                }

                if ($c == $this) {
                    $find = true;
                }
            }
        }
        return $next;
    }

    public function getLast(): ?static
    {
        return is_null($this->parent) ? null : $this->parent->content[count($this->parent->content) - 1];
    }

    public function remove(): ?self
    {
        $parent = $this->parent;

        if (!is_null($parent)) {
            foreach ($parent->content as $key => $value) {
                if ($parent->content[$key] == $this) {
                    unset($parent->content[$key]);
                    return $parent;
                }
            }
        }

        return null;
    }

    /**
     * Generation method
     */
    public function __toString(): string
    {
        return $this->getTop()->toString();
    }

    /**
     * Generation method
     */
    public function toString(): string
    {
        $string = '';

        if (!empty($this->tag)) {
            $string .=  '<' . $this->tag;
            $string .= $this->attributesToString();
            if ($this->autoclosed) {
                $string .= '/>';
            } else {
                $string .= '>' . $this->contentToString() . '</' . $this->tag . '>';
            }
        } else {
            $string .= $this->text;
            $string .= $this->contentToString();
        }

        return $string;
    }

    /**
     * Return current list of attribute as a string $key="$val" $key2="$val2"
     */
    protected function attributesToString(): string
    {
        $string = '';
        $XMLConvention = in_array(static::$outputLanguage, array(ENT_XML1, ENT_XHTML));
        if (!empty($this->attributeList)) {
            foreach ($this->attributeList as $key => $value) {
                if ($value!==null && ($value!==false || $XMLConvention)) {
                    $string.= ' ' . $key;
                    if ($value===true) {
                        if ($XMLConvention) {
                            $value = $key;
                        } else {
                            continue;
                        }
                    }
                    $string.= '="' . implode(
                        ' ',
                        array_map(
                            static::$avoidXSS ? 'static::unXSS' : 'strval',
                            is_array($value) ? $value : array($value)
                        )
                    ) . '"';
                }
            }
        }
        return $string;
    }

    /**
     * return current list of content as a string
     */
    protected function contentToString(): string
    {
        $string = '';

        if (!is_null($this->content)) {
            foreach ($this->content as $c) {
                $string .= $c->toString();
            }
        }

        return $string;
    }

    /**
     * Protects value from XSS injection by replacing some characters by XML / HTML entities
     */
    public static function unXSS(string $input): string
    {
        return htmlentities($input, ENT_QUOTES | ENT_DISALLOWED | static::$outputLanguage);
    }
}
