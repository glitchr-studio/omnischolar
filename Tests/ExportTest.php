<?php

namespace Omnischolar\Tests;

use Omnischolar\Export;
use Omnischolar\Model\Contributor;
use Omnischolar\Model\Identifier;
use Omnischolar\Model\Identifiers;
use Omnischolar\Model\Venue;
use Omnischolar\Model\Work;
use Omnischolar\Model\WorkType;
use PHPUnit\Framework\TestCase;

final class ExportTest extends TestCase
{
    private static function article(): Work
    {
        return new Work(
            title: 'Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy',
            type: WorkType::ARTICLE,
            authors: [new Contributor('Léa Chocron', 'Léa', 'Chocron'), new Contributor('Keitaro Nakatani', 'Keitaro', 'Nakatani', Identifiers::of(Identifier::orcid('0009-0005-1387-7295')))],
            year: 2024,
            date: '2024-09-25',
            venue: new Venue('Chemical Science', Venue::JOURNAL, ['2041-6520'], 'Royal Society of Chemistry (RSC)', 'Chem. Sci.'),
            volume: '15',
            issue: '39',
            pages: '16034-16039',
            abstract: 'Molecular solar thermal energy storage & release.',
            identifiers: Identifiers::of(Identifier::doi('10.1039/d4sc04973j'), Identifier::hal('hal-04772417')),
            url: 'https://pubs.rsc.org/sc/article/15/39/16034-16039/869801',
        );
    }

    private static function book(): Work
    {
        return new Work(
            title: 'Planting the Seeds of Algebra, PreK-2',
            subtitle: 'Explorations for the Early Grades',
            type: WorkType::BOOK,
            authors: [Contributor::fromName('Monica Neagoy')],
            year: 2012,
            publisher: 'Corwin',
            identifiers: Identifiers::of(Identifier::isbn('1412996600'), Identifier::doi('10.4135/9781544308616')),
        );
    }

    private static function preprint(): Work
    {
        return new Work(
            title: 'Logarithmic gravity from a very slow roll limit of inflation',
            type: WorkType::PREPRINT,
            authors: [Contributor::fromName('Jordan Cotler'), Contributor::fromName('Juan Maldacena')],
            year: 2026,
            date: '2026-09-29',
            venue: new Venue('arXiv', Venue::REPOSITORY),
            domains: ['hep-th', 'gr-qc'],
            identifiers: Identifiers::of(Identifier::arxiv('2609.38052')),
        );
    }

    public function testBibtex(): void
    {
        $bibtex = (new Export())->bibtex([self::article(), self::book(), self::preprint()]);

        self::assertSame(<<<'BIB'
            @article{chocron2024acid,
              author = {Chocron, Léa and Nakatani, Keitaro},
              title = {{Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy}},
              journal = {Chemical Science},
              year = {2024},
              month = {sep},
              volume = {15},
              number = {39},
              pages = {16034--16039},
              doi = {10.1039/d4sc04973j},
              issn = {2041-6520},
              hal_id = {hal-04772417},
              url = {https://pubs.rsc.org/sc/article/15/39/16034-16039/869801}
            }

            @book{neagoy2012planting,
              author = {Neagoy, Monica},
              title = {{Planting the Seeds of Algebra, PreK-2: Explorations for the Early Grades}},
              year = {2012},
              publisher = {Corwin},
              doi = {10.4135/9781544308616},
              isbn = {9781412996600}
            }

            @misc{cotler2026logarithmic,
              author = {Cotler, Jordan and Maldacena, Juan},
              title = {{Logarithmic gravity from a very slow roll limit of inflation}},
              year = {2026},
              month = {sep},
              eprint = {2609.38052},
              archiveprefix = {arXiv},
              primaryclass = {hep-th}
            }

            BIB, $bibtex);
    }

    public function testBibtexEscapesAndKeysAreUnique(): void
    {
        $export = new Export(abstracts: true);
        $bibtex = $export->bibtex([self::article(), self::article()]);
        self::assertStringContainsString('@article{chocron2024acid,', $bibtex);
        self::assertStringContainsString('@article{chocron2024acida,', $bibtex);
        self::assertStringContainsString('abstract = {Molecular solar thermal energy storage \& release.}', $bibtex);
        self::assertSame('anonymous2024untitled', $export->citationKey(new Work('', year: 2024)));
    }

    public function testAGroupedBookIsCitedByItsNewestEdition(): void
    {
        $older = self::book();
        $newer = new Work('Planting the Seeds of Algebra, PreK-2', WorkType::BOOK, [Contributor::fromName('Monica M. Neagoy')], 2016, publisher: 'Corwin Press', identifiers: Identifiers::of(Identifier::isbn('9781452279701')));
        $group = $newer->with(['editions' => [$newer, $older], 'identifiers' => $newer->identifiers->merge($older->identifiers)]);

        $bibtex = (new Export())->bibtex($group);
        self::assertStringContainsString('isbn = {9781452279701}', $bibtex);
        self::assertStringNotContainsString('doi =', $bibtex, 'not the 2012 edition\'s DOI');
    }

    public function testRis(): void
    {
        $ris = (new Export())->ris([self::article(), self::book()]);

        self::assertSame(implode("\r\n", [
            'TY  - JOUR',
            'ID  - chocron2024acid',
            'AU  - Chocron, Léa',
            'AU  - Nakatani, Keitaro',
            'TI  - Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy',
            'T2  - Chemical Science',
            'J2  - Chem. Sci.',
            'PY  - 2024',
            'DA  - 2024/09/25',
            'VL  - 15',
            'IS  - 39',
            'SP  - 16034',
            'EP  - 16039',
            'PB  - Royal Society of Chemistry (RSC)',
            'SN  - 2041-6520',
            'DO  - 10.1039/d4sc04973j',
            'UR  - https://pubs.rsc.org/sc/article/15/39/16034-16039/869801',
            'ER  -',
            '',
            'TY  - BOOK',
            'ID  - neagoy2012planting',
            'AU  - Neagoy, Monica',
            'TI  - Planting the Seeds of Algebra, PreK-2: Explorations for the Early Grades',
            'PY  - 2012',
            'PB  - Corwin',
            'SN  - 9781412996600',
            'DO  - 10.4135/9781544308616',
            'UR  - https://doi.org/10.4135/9781544308616',
            'ER  -',
        ])."\r\n", $ris);
    }

    public function testCslJson(): void
    {
        $export = new Export();
        $items = $export->csl([self::article(), self::book(), self::preprint()]);

        self::assertSame([
            'id' => 'chocron2024acid',
            'type' => 'article-journal',
            'title' => 'Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy',
            'author' => [['family' => 'Chocron', 'given' => 'Léa'], ['family' => 'Nakatani', 'given' => 'Keitaro']],
            'container-title' => 'Chemical Science',
            'container-title-short' => 'Chem. Sci.',
            'publisher' => 'Royal Society of Chemistry (RSC)',
            'issued' => ['date-parts' => [[2024, 9, 25]]],
            'volume' => '15',
            'issue' => '39',
            'page' => '16034-16039',
            'DOI' => '10.1039/d4sc04973j',
            'ISSN' => '2041-6520',
            'URL' => 'https://pubs.rsc.org/sc/article/15/39/16034-16039/869801',
        ], $items[0]);
        self::assertSame('book', $items[1]['type']);
        self::assertSame('9781412996600', $items[1]['ISBN']);
        self::assertSame(['date-parts' => [[2012]]], $items[1]['issued']);
        self::assertSame(['article', '2609.38052', 'Preprint'], [$items[2]['type'], $items[2]['number'], $items[2]['genre']]);

        $json = $export->format('csl-json', self::article());
        self::assertSame('Léa', json_decode($json, true)[0]['author'][0]['given']);
        self::assertSame('application/x-bibtex; charset=utf-8', Export::contentType('bibtex'));
        $this->expectException(\InvalidArgumentException::class);
        $export->format('endnote', []);
    }
}
