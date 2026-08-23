<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * An extension element carried by a report.
 *
 * Extensions are the forward-compatibility mechanism of the report format:
 * future documents may add their own namespaced elements, at file level inside
 * an <extension> wrapper and at record level directly after <auth_results>.
 * They are kept generic on purpose, since what will appear is unknown, and an
 * unrecognised extension is normal operation rather than an error.
 *
 * @see ExtensionType RFC 9990, Appendix A
 * @see Contents of the "extension" Element RFC 9990, Section 3.1.1.6
 * @see Extensible Reporting RFC 9990, Section 5
 */
final class Extension
{
    public function __construct(
        /**
         * The local element name, without the namespace prefix.
         */
        public readonly string $name,

        /**
         * The content of the element.
         *
         * A string for a simple element, or an array when the extension nests
         * further elements, which RFC 9990 Appendix A permits.
         */
        public readonly string|array $content,

        /**
         * The URI identifying the extension that defines this element.
         *
         * RFC 9990 Section 5 requires extensions to carry one, but it is
         * nullable because a report may use an undeclared prefix.
         */
        public readonly ?string $namespace = null,
    ) {}

    /**
     * Build the extensions found among $elements.
     *
     * Extension elements are the namespaced ones, so any key carrying a prefix
     * is an extension and every other key belongs to the report schema itself.
     *
     * @param  array<string, mixed>  $elements
     * @param  array<string, string>  $attributes  the in-scope attributes, used to resolve prefixes
     * @return list<self>
     */
    public static function listFromArray(array $elements, array $attributes = []): array
    {
        $extensions = [];

        foreach ($elements as $name => $content) {
            if (! str_contains($name, ':')) {
                continue;
            }

            [$prefix, $localName] = explode(':', $name, 2);

            $extensions[] = new self(
                name: $localName,
                content: is_array($content) ? $content : (string) $content,
                namespace: $attributes["xmlns:{$prefix}"] ?? null,
            );
        }

        return $extensions;
    }
}
