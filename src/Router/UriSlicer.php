<?php

namespace SimpleRoute\Router;
/**
 * Represents an HTTP route as a list of URI segments.
 *
 * This class parses a URI path into segments and provides
 * navigation methods to iterate over them one by one.
 * Useful for custom routing systems.
 *
 * @author akido-ld
 * @version 1.0.1
 */
class UriSlicer{
    /**
     * @var array List of URI segments
     */
    private $segments;

    /**
     * @var int Current cursor position in the segment list
     */
    private $cursor;

    /**
     * @var string Current URI string
     */
    private string $URI;

    /**
     * Constructs a new RouteMap instance from a given URI.
     *
     * @param string $URI The request URI (e.g. "/auth/login/edit")
     */
    public function __construct(?string $URI = null) {
        $this->segments = $URI ? $this->parsePath($URI) : null;
        $this->URI = $URI;
        $this->cursor = 0;
    }

    /**
     * Splits a URI path into individual segments.
     *
     * @param string $path The URI path to parse
     * @return array An array of non-empty segments
     */
    private function parsePath($path) {
        $segments = [];
        $token = strtok($path, '/');
        while ($token !== false) {
            $segments[] = $token;
            $token = strtok('/');
        }
        return $segments;
    }

    /**
     * Set the URI string
     * 
     * @param string $URI
     * @return void
     */
    public function setURI(string $URI){
        $this->segments = $this->parsePath($URI);
        $this->URI = $URI;
        $this->reset();
    }

    /**
     * Get the URI string
     * 
     * @return string
     */
    public function getURI(): string{
        return $this->URI;
    }
    
    /**
     * Resets the cursor back to the beginning of the route.
     *
     * @return void
     */
    public function reset() {
        $this->cursor = 0;
    }

    /**
     * Checks if there is a next segment available.
     *
     * @return bool True if there is a next segment, false otherwise
     */
    public function hasNext(): bool {
        return sizeof($this->segments) > ($this->cursor);
    }

    /**
     * Returns the next segment and moves the cursor forward.
     *
     * @return string|null The next segment or false if none left
     */
    public function next() {
        return $this->hasNext() ? $this->segments[$this->cursor++] : null;
    }

    /**
     * Gets the current cursor position (useful for debugging).
     *
     * @return int Current position in the segment list
     */
    public function current() {
        return $this->cursor;
    }

    public function getUnusedSegments(): array{
        $unusedSegments = [];
        while($tmp = $this->next()){
            $unusedSegments[] = $tmp;
        }
        return $unusedSegments;
    }

    public function __invoke(): ?string{
        return $this->next();
    }
}
