<?php

namespace Codewiser\Notifications\Contracts;

/**
 * Contract for building messages of any type.
 */
interface MessageContract
{
    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     */
    public function when($value = null, callable $callback = null, callable $default = null);

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     */
    public function unless($value = null, callable $callback = null, callable $default = null);

    /**
     * Set the subject of the notification.
     *
     * @param string $subject
     * @return $this
     */
    public function subject($subject);

    /**
     * Set the "level" of the notification (success, error, etc.).
     *
     * @param string $level
     * @return $this
     */
    public function level($level);

    /**
     * Indicate that the notification gives information about a successful operation.
     *
     * @return $this
     */
    public function success();

    /**
     * Indicate that the notification gives information about an error.
     *
     * @return $this
     */
    public function error();

    /**
     * Add a line of text to the notification.
     *
     * @param mixed $line
     * @return $this
     */
    public function line($line);

    /**
     * Add a line of text to the notification if the given condition is true.
     *
     * @param bool $boolean
     * @param mixed $line
     * @return $this
     */
    public function lineIf($boolean, $line);

    /**
     * Add lines of text to the notification.
     *
     * @param iterable $lines
     * @return $this
     */
    public function lines($lines);

    /**
     * Add lines of text to the notification if the given condition is true.
     *
     * @param bool $boolean
     * @param iterable $lines
     * @return $this
     */
    public function linesIf($boolean, $lines);

    /**
     * Add a line of text to the notification.
     *
     * @param mixed $line
     * @return $this
     */
    public function with($line);

    /**
     * Configure the "call to action" button.
     *
     * @param string $text
     * @param string $url
     * @return $this
     */
    public function action($text, $url);
}
