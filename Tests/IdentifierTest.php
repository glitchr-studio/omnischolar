<?php

namespace Omnischolar\Tests;

use Omnischolar\Model\Identifier;
use Omnischolar\Model\Identifiers;
use Omnischolar\Model\Scheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /** @return iterable<array{string, string|null}> */
    public static function parsed(): iterable
    {
        yield ['10.1039/D4SC04973J', 'doi:10.1039/d4sc04973j'];
        yield ['https://doi.org/10.1039/d4sc04973j', 'doi:10.1039/d4sc04973j'];
        yield ['doi:10.1023/A:1026654312961', 'doi:10.1023/a:1026654312961'];
        yield ['https://dx.doi.org/10.4135/9781544308616', 'doi:10.4135/9781544308616'];
        yield ['1-4129-9660-0', 'isbn:9781412996600'];
        yield ['978-1-4129-9660-0', 'isbn:9781412996600'];
        yield ['isbn:1452279705', 'isbn:9781452279701'];
        yield ['1412996609', null];
        yield ['arXiv:hep-th/9711200v3', 'arxiv:hep-th/9711200'];
        yield ['2609.38052v1', 'arxiv:2609.38052'];
        yield ['https://arxiv.org/abs/2401.12345', 'arxiv:2401.12345'];
        yield ['https://arxiv.org/pdf/math.GT/0309136v2', 'arxiv:math.GT/0309136'];
        yield ['hal-04772417', 'hal:hal-04772417'];
        yield ['https://hal.science/hal-04772417v1/document', 'hal:hal-04772417'];
        yield ['halshs-01234567', 'hal:halshs-01234567'];
        yield ['tel-04012345v2', 'hal:tel-04012345'];
        yield ['0009-0005-1387-7295', 'orcid:0009-0005-1387-7295'];
        yield ['https://orcid.org/0000-0002-1825-0097', 'orcid:0000-0002-1825-0097'];
        yield ['0000-0002-1825-0098', null];
        yield ['A5108007452', 'openalex:A5108007452'];
        yield ['https://openalex.org/W7133342466', 'openalex:W7133342466'];
        yield ['OL7092953A', 'openlibrary:OL7092953A'];
        yield ['https://openlibrary.org/works/OL21043513W', 'openlibrary:OL21043513W'];
        yield ['bai:Juan.M.Maldacena.1', 'inspire_bai:Juan.M.Maldacena.1'];
        yield ['inspire:451647', 'inspire:451647'];
        yield ['https://inspirehep.net/literature/451647', 'inspire:451647'];
        yield ['PMC1234567', 'pmcid:PMC1234567'];
        yield ['2041-6520', 'issn:2041-6520'];
        yield ['451647', null];
        yield ['Monica Neagoy', null];
        yield ['keitaro-nakatani', null];
    }

    #[DataProvider('parsed')]
    public function testParse(string $input, ?string $key): void
    {
        self::assertSame($key, Identifier::parse($input)?->key());
    }

    public function testNormalisationMakesSpellingsEqual(): void
    {
        self::assertTrue(Identifier::doi('10.1039/D4SC04973J')->equals(Identifier::doi('https://doi.org/10.1039/d4sc04973j')));
        self::assertTrue(Identifier::isbn('1412996600')->equals(Identifier::isbn('978-1-4129-9660-0')));
        self::assertSame('1412996600', Identifier::isbn('9781412996600')->isbn10());
        self::assertNull(Identifier::isbn('9791032305690')->isbn10(), 'a 979 ISBN has no ISBN-10');
        self::assertSame('https://doi.org/10.1039/d4sc04973j', Identifier::doi('10.1039/d4sc04973j')->url());
        self::assertSame('https://openlibrary.org/books/OL38294079M', Identifier::openlibrary('OL38294079M')->url());
        self::assertSame('keitaro-nakatani', Identifier::of('idhal', 'Keitaro-Nakatani')->value);
        self::assertNull(Identifier::tryOf(Scheme::DOI, 'not a doi'));
        $this->expectException(\InvalidArgumentException::class);
        Identifier::orcid('0000-0002-1825-0098');
    }

    public function testACollectionKeepsEachOnce(): void
    {
        $ids = Identifiers::of(Identifier::doi('10.1234/A'), Identifier::isbn('1412996600'), null, Identifier::doi('10.1234/a'), Identifier::isbn('9781544308616'));

        self::assertCount(3, $ids);
        self::assertSame('10.1234/a', $ids->value(Scheme::DOI));
        self::assertSame(['9781412996600', '9781544308616'], $ids->values(Scheme::ISBN));
        self::assertTrue($ids->has(Identifier::isbn('978-1-5443-0861-6')));
        self::assertFalse($ids->has(Scheme::ARXIV));
        self::assertSame(['doi' => ['10.1234/a'], 'isbn' => ['9781412996600', '9781544308616']], $ids->toArray());
        self::assertEquals($ids, Identifiers::fromArray($ids->toArray()));
        self::assertCount(4, $ids->with(Identifier::arxiv('2401.12345')));
    }
}
