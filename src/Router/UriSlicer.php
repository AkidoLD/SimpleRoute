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
     * @param string $URI The request URI (e.g. "/auth/login/edit").
     */
    public function __construct(string $URI = "") {
        $this->segments = $URI ? $this->parsePath($URI) : [];
        $this->URI = $URI ;
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
     * @return UriSlicer For chained call
     */
    public function reset(): UriSlicer {
        $this->cursor = 0;
        return $this;
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
     * Get the current URI segment
     * 
     * @return string|null
     */
    public function current(): ?string{
        return $this->segments[$this->cursor] ?? null;
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

    /**
     * Get the URI string of this `UriSlicer`
     *
     *  This is an alias of {@see UriSlicer::getURI()()}
     * 
     * @return string
     */
    public function toString(): string {
        return $this->getURI();
    }

    /**
     * Allow to easly get the URI string use in the UriSlicer
     * 
     * This is an alias of {@see UriSlicer::toString()}
     * 
     * @return string
     */
    public function __toString(): string{
        return $this->toString();
    }

    /**
     * Factory to create a UriSlicer from an array of segments.
     *
     * @param string[] $segments Array of URI segments.
     * @return self A new UriSlicer instance.
     */
    public static function fromSegments(array $segments): self {
        $slicer = new self();
        $slicer->segments = $segments;
        $slicer->URI = '/' . implode('/', array: $segments);
        $slicer->reset();
        return $slicer;
    }
}
