<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Api\Data;

interface QueryAttributeInterface
{
    /**
     * Get the value
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string;
}
