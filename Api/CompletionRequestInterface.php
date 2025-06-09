<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Api;

use Psr\Http\Message\StreamInterface;

interface CompletionRequestInterface
{
    /**
     * Build the API payload to send to the completion endpoint
     *
     * @param string $text
     * @return array
     */
    public function getApiPayload(string $text): array;

    /**
     * Convert the response stream into a usable string
     *
     * @param StreamInterface $stream
     * @return string
     */
    public function convertToResponse(StreamInterface $stream): string;

    /**
     * Get JS configuration for the frontend UI component
     *
     * @return array|null
     */
    public function getJsConfig(): ?array;

    /**
     * Send a prompt and return the AI-generated result
     *
     * @param string $prompt
     * @return string
     */
    public function query(string $prompt): string;

    /**
     * Get the completion request type (e.g., meta_description, meta_title)
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Build a prompt from the provided parameters
     *
     * @param array $params
     * @return string
     */
    public function getQuery(array $params): string;
}
