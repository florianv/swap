<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Exchanger\Service\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Keeps the provider tables in the docs in sync with the services Exchanger registers.
 */
final class DocumentationTest extends TestCase
{
    private const ROOT = __DIR__ . '/..';

    /**
     * Registered identifiers that are deliberately absent from the README provider tables, with the reason.
     */
    private const NOT_IN_README = [
        'array' => 'in-memory service, not a provider; doc/readme.md documents it',
        'fixer_apilayer' => 'APILayer Fixer variant sending the key as a header; the README lists apilayer_fixer',
    ];

    #[Test]
    #[DataProvider('provideRegisteredIdentifiers')]
    public function it_lists_each_service_in_the_readme(string $identifier): void
    {
        if (isset(self::NOT_IN_README[$identifier])) {
            $this->markTestSkipped(self::NOT_IN_README[$identifier]);
        }

        $this->assertStringContainsString(
            '`' . $identifier . '`',
            self::read('README.md'),
            \sprintf('Add a row for `%s` to the "Commercial providers" or "Public providers" table in README.md.', $identifier),
        );
    }

    #[Test]
    #[DataProvider('provideRegisteredIdentifiers')]
    public function it_documents_each_service_in_the_doc(string $identifier): void
    {
        $this->assertStringContainsString(
            '`' . $identifier . '`',
            self::read('doc/readme.md'),
            \sprintf('Add a row for `%s` to the "Commercial providers" or "Public providers" table under "Provider configuration" in doc/readme.md.', $identifier),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRegisteredIdentifiers(): iterable
    {
        foreach (array_keys(Registry::getServices()) as $identifier) {
            yield (string) $identifier => [(string) $identifier];
        }
    }

    private static function read(string $relativePath): string
    {
        $contents = file_get_contents(self::ROOT . '/' . $relativePath);
        self::assertIsString($contents, \sprintf('Could not read %s.', $relativePath));

        return $contents;
    }
}
