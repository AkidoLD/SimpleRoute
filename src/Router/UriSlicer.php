<?php

namespace SimpleRoute\Router;

/**
 * Class UriSlicer
 *
 * Utility class to split and navigate through URI paths segment by segment.
 * 
 * This class is particularly useful in routing systems where a request URI
 * needs to be parsed progressively in order to match against a route tree.
 *
 * Example:
 * ```php
 * $slicer = new UriSlicer("/auth/login/edit");
 * while ($slicer->hasNext()) {
 *     echo $slicer->next();
 * }
 * // Output: "auth", "login", "edit"
 * ```
 *
 * @author akido-ld
 * @version 1.0.1
 */
class UriSlicer {
    /**
     * @var string[] List of URI segments (split from the path)
     */
    private array $segments;

    /**
     * @var int Current cursor position in the segment list
     */
    private int $cursor;

    /**
     * @var string Original URI string
     */
    private string $URI;

    /**
     * Constructs a new UriSlicer instance from a given URI.
     *
     * @param string|null $URI The request URI (e.g. "/auth/login/edit").
     *                         If null, the slicer will be empty until setURI() is called.
     */
    public function __construct(?string $URI = null) {
        $this->segments = $URI ? $this->parsePath($URI) : [];
        $this->URI = $URI ?? "";
        $this->cursor = 0;
    }

    /**
     * Splits a URI path into individual non-empty segments.
     *
     * @param string $path The URI path to parse.
     * @return string[] An array of non-empty segments.
     */
    private function parsePath(string $path): array {
        $segments = [];
        $token = strtok($path, '/');
        while ($token !== false) {
            $segments[] = $token;
            $token = strtok('/');
        }
        return $segments;
    }

    /**
     * Sets a new URI and resets the slicer state.
     * 
     * @param string $URI The new URI string.
     * @return void
     */
    public function setURI(string $URI): void {
        $this->segments = $this->parsePath($URI);
        $this->URI = $URI;
        $this->reset();
    }

    /**
     * Returns the full URI string.
     * 
     * @return string The original URI.
     */
    public function getURI(): string {
        return $this->URI;
    }

    /**
     * Resets the cursor back to the beginning of the segments.
     *
     * @return void
     */
    public function reset(): void {
        $this->cursor = 0;
    }

    /**
     * Checks if there is another segment available.
     *
     * @return bool True if there is a next segment, false otherwise.
     */
    public function hasNext(): bool {
        return $this->cursor < count($this->segments);
    }

    /**
     * Returns the next segment and advances the cursor.
     *
     * @return string|null The next segment, or null if none remain.
     */
    public function next(): ?string {
        return $this->hasNext() ? $this->segments[$this->cursor++] : null;
    }

    /**
     * Gets the current cursor position (0-based index).
     *
     * @return int Current position in the segment list.
     */
    public function cursorPosition(): int {
        return $this->cursor;
    }

    /**
     * Returns all unused segments from the current cursor to the end.
     * This also advances the cursor to the end.
     * 
     * Example:
     * ```php
     * $slicer = new UriSlicer("/auth/login/edit");
     * $slicer->next(); // consume "auth"
     * print_r($slicer->getUnusedSegments()); // ["login", "edit"]
     * ```
     *
     * @return string[] Remaining unused segments.
     */
    public function getUnusedSegments(): array {
        $unusedSegments = [];
        while ($tmp = $this->next()) {
            $unusedSegments[] = $tmp;
        }
        return $unusedSegments;
    }

    /**
     * Allows the slicer instance to be used as a callable
     * that returns the next segment.
     *
     * Example:
     * ```php
     * $slicer = new UriSlicer("/auth/login");
     * echo $slicer(); // "auth"
     * echo $slicer(); // "login"
     * ```
     *
     * @return string|null The next segment, or null if none remain.
     */
    public function __invoke(): ?string {
        return $this->next();
    }
}
