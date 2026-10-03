<?php

namespace Omnischolar\Tests;

use Omnischolar\Merger;
use Omnischolar\Model\Contributor;
use Omnischolar\Model\Identifier;
use Omnischolar\Model\Identifiers;
use Omnischolar\Model\Venue;
use Omnischolar\Model\Work;
use Omnischolar\Model\WorkType;
use PHPUnit\Framework\TestCase;

final class MergerTest extends TestCase
{
    private static function work(string $source, string $title, ?int $year, array $identifiers = [], array $fields = []): Work
    {
        return new Work(...$fields + [
            'title' => $title,
            'type' => WorkType::ARTICLE,
            'year' => $year,
            'identifiers' => new Identifiers($identifiers),
            'source' => $source,
            'authors' => [Contributor::fromName('Keitaro Nakatani')],
        ]);
    }

    public function testTheSameDoiIsOneWorkEachFieldFromItsPreferredSource(): void
    {
        $doi = Identifier::doi('10.1039/d4sc04973j');
        $merged = (new Merger())->merge(
            [self::work('hal', 'Acid-sensitive photoswitches', 2024, [$doi, Identifier::hal('hal-04772417')], ['abstract' => 'HAL abstract', 'pdfUrl' => 'https://hal.science/hal-04772417/document', 'openAccess' => true, 'date' => '2024-09-25', 'domains' => ['chim']])],
            [self::work('openalex', 'Acid-sensitive photoswitches: towards catalytic on-demand release', 2024, [$doi, Identifier::openalex('W4402922222')], ['abstract' => 'OpenAlex abstract', 'citations' => 6, 'openAccess' => false, 'date' => '2024-01-01', 'keywords' => ['Photochromism']])],
            [self::work('crossref', 'Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy', 2024, [$doi], ['venue' => new Venue('Chemical Science'), 'citations' => 5, 'keywords' => ['photochromism', 'Energy']])],
        );

        self::assertCount(1, $merged);
        $work = $merged[0];
        self::assertSame('Acid-sensitive photoswitches: towards catalytic on-demand release of stored light energy', $work->title, 'Crossref first by default');
        self::assertSame('HAL abstract', $work->abstract, 'HAL first for abstracts');
        self::assertSame(6, $work->citations, 'OpenAlex first for citations');
        self::assertSame('https://hal.science/hal-04772417/document', $work->pdfUrl);
        self::assertTrue($work->openAccess, 'open when one source says so');
        self::assertSame('Chemical Science', $work->venue->name);
        self::assertSame(['doi', 'openalex', 'hal'], array_keys($work->identifiers->toArray()));
        self::assertSame(['crossref', 'openalex', 'hal'], $work->sources);
        self::assertSame(['photochromism', 'Energy'], $work->keywords, 'united in the default order (Crossref first), once regardless of case');
        self::assertSame('2024-09-25', $work->date, 'Crossref has the year alone: the date from HAL, before OpenAlex\'s January 1st');
    }

    public function testPreferencesAreConfigurable(): void
    {
        $doi = Identifier::doi('10.1234/x');
        $merged = (new Merger(['default' => ['hal', 'crossref'], 'citations' => ['crossref']]))->merge(
            [self::work('crossref', 'Crossref title', 2024, [$doi], ['citations' => 5])],
            [self::work('hal', 'HAL title', 2024, [$doi], ['citations' => 9])],
        );
        self::assertSame('HAL title', $merged[0]->title);
        self::assertSame(5, $merged[0]->citations);
    }

    public function testTheOrderOfIdentifiers(): void
    {
        $merger = new Merger();
        // An arXiv id makes the preprint and the article one.
        self::assertTrue($merger->same(
            self::work('arxiv', 'Wormholes', 2023, [Identifier::arxiv('2305.01234')], ['type' => WorkType::PREPRINT]),
            self::work('inspire', 'Traversable wormholes', 2024, [Identifier::arxiv('2305.01234'), Identifier::doi('10.1007/jhep01(2024)001')]),
        ));
        // Two DOIs are two works, whatever the titles.
        self::assertFalse($merger->same(
            self::work('openalex', 'Initiation à la démarche scientifique', 2026, [Identifier::doi('10.5281/zenodo.1')], ['type' => WorkType::DATASET]),
            self::work('openalex', 'Initiation à la démarche scientifique', 2026, [Identifier::doi('10.5281/zenodo.2')], ['type' => WorkType::DATASET]),
        ));
        // A shared arXiv id does not join two different DOIs either.
        self::assertFalse($merger->same(
            self::work('inspire', 'A', 2020, [Identifier::arxiv('2001.00001'), Identifier::doi('10.1234/a')]),
            self::work('crossref', 'B', 2021, [Identifier::arxiv('2001.00001'), Identifier::doi('10.1234/b')]),
        ));
        // A HAL id.
        self::assertTrue($merger->same(
            self::work('hal', 'Un article', 2012, [Identifier::hal('hal-00831009')]),
            self::work('openalex', 'Another title', 2012, [Identifier::hal('hal-00831009'), Identifier::doi('10.1039/c2cp23333a')]),
        ));
        // Failing identifiers, the title and the year, accents and punctuation aside.
        self::assertTrue($merger->same(
            self::work('hal', 'La publication des étudiant.es comme levier d’apprentissage', 2025),
            self::work('openalex', 'La publication des etudiant es comme levier d\'apprentissage', 2025, [Identifier::doi('10.1234/z')]),
        ));
        self::assertFalse($merger->same(self::work('hal', 'Photochromism', 2024), self::work('openalex', 'Photochromism', 2023)));
        // A preprint and its publication a year later.
        self::assertTrue($merger->same(
            self::work('arxiv', 'The no boundary density matrix', 2024, [Identifier::arxiv('2409.12345')], ['type' => WorkType::PREPRINT]),
            self::work('crossref', 'The no boundary density matrix', 2025, [Identifier::doi('10.1007/jhep02(2025)124')]),
        ));
        // Chapters share their book's ISBN: that does not make them one.
        self::assertFalse($merger->same(
            self::work('crossref', 'Chapter one', 2016, [Identifier::isbn('9781412996600')], ['type' => WorkType::CHAPTER]),
            self::work('crossref', 'Chapter two', 2016, [Identifier::isbn('9781412996600')], ['type' => WorkType::CHAPTER]),
        ));
    }

    public function testTheMergedPreprintIsTheArticle(): void
    {
        $merged = (new Merger())->merge(
            [self::work('arxiv', 'Wormholes', 2023, [Identifier::arxiv('2305.01234')], ['type' => WorkType::PREPRINT, 'pdfUrl' => 'https://arxiv.org/pdf/2305.01234'])],
            [self::work('inspire', 'Traversable wormholes', 2024, [Identifier::arxiv('2305.01234'), Identifier::doi('10.1234/w')], ['venue' => new Venue('JHEP')])],
        );
        self::assertCount(1, $merged);
        self::assertSame(WorkType::ARTICLE, $merged[0]->type);
        self::assertSame(2024, $merged[0]->year);
        self::assertSame('https://arxiv.org/pdf/2305.01234', $merged[0]->pdfUrl);
    }

    public function testARecordBridgingTwoGroupsJoinsThem(): void
    {
        $merged = (new Merger())->merge([
            self::work('hal', 'One', 2020, [Identifier::hal('hal-01234567')]),
            self::work('arxiv', 'Two', 2020, [Identifier::arxiv('2001.00002')], ['type' => WorkType::PREPRINT]),
            self::work('openalex', 'Three', 2020, [Identifier::hal('hal-01234567'), Identifier::arxiv('2001.00002')]),
        ]);
        self::assertCount(1, $merged);
        self::assertSame(['openalex', 'hal', 'arxiv'], $merged[0]->sources);
    }

    public function testTheEditionsOfABookAreGrouped(): void
    {
        $book = static fn (string $source, string $title, int $year, array $isbns, array $fields = []) => self::work($source, $title, $year, array_map(Identifier::isbn(...), $isbns), $fields + ['type' => WorkType::BOOK, 'authors' => [Contributor::fromName('Monica Neagoy')]]);
        $merged = (new Merger())->merge(
            [
                $book('openlibrary', 'Planting the seeds of algebra, PreK-2', 2012, ['9781412996600'], ['cover' => 'https://covers.openlibrary.org/b/id/14790810-L.jpg']),
                $book('openlibrary', 'Planting the seeds of algebra, PreK-2', 2012, ['1412996600']),
                $book('openlibrary', 'Planting the Seeds of Algebra, PreK-2', 2016, ['9781452279701'], ['subtitle' => 'Explorations for the Early Grades', 'authors' => [Contributor::fromName('Monica M. Neagoy')]]),
                $book('openlibrary', 'Planting the Seeds of Algebra, 3-5', 2014, ['9781483379753']),
                $book('openlibrary', 'Méthode de Singapour CE1 - Fichier de l\'élève 1', 2020, ['9782369404125']),
                $book('openlibrary', 'Méthode de Singapour CE1 - Fichier de l\'élève 2', 2020, ['9782369404132']),
            ],
            [$book('crossref', 'Planting the Seeds of Algebra, PreK-2: Explorations for the Early Grades', 2012, ['9781412996600', '9781544308616'], ['identifiers' => Identifiers::of(Identifier::doi('10.4135/9781544308616'), Identifier::isbn('9781412996600'), Identifier::isbn('9781544308616'))])],
        );

        self::assertCount(4, $merged, 'PreK-2, 3-5, and the two volumes of the Méthode');
        $preK2 = self::titled($merged, 'Planting the Seeds of Algebra, PreK-2');
        self::assertSame('Planting the Seeds of Algebra, PreK-2', $preK2->title, 'the newest edition\'s');
        self::assertSame(2016, $preK2->year);
        self::assertCount(2, $preK2->editions);
        [$second, $first] = $preK2->editions;
        self::assertSame(['9781452279701'], $second->isbns());
        self::assertSame(2012, $first->year);
        self::assertSame('10.4135/9781544308616', $first->doi(), 'Crossref\'s record and Open Library\'s two, one edition');
        self::assertSame(['crossref', 'openlibrary'], $first->sources);
        self::assertSame('https://covers.openlibrary.org/b/id/14790810-L.jpg', $first->cover, 'Open Library first for covers');
        self::assertEqualsCanonicalizing(['9781452279701', '9781412996600', '9781544308616'], $preK2->isbns());
        self::assertSame('https://covers.openlibrary.org/b/id/14790810-L.jpg', $preK2->cover, 'the group takes a cover from an edition');

        // Merged again (a weekly sync), the group is taken apart and grouped the same.
        $again = (new Merger())->merge($merged);
        self::assertCount(4, $again);
        self::assertCount(2, self::titled($again, 'Planting the Seeds of Algebra, PreK-2')->editions);

        self::assertCount(5, (new Merger([], false))->merge($merged), 'editions left apart when asked');
    }

    public function testAnOrcidIsLentToTheSameName(): void
    {
        $doi = Identifier::doi('10.1234/n');
        $merged = (new Merger())->merge(
            [self::work('crossref', 'X', 2024, [$doi], ['authors' => [Contributor::fromName('Léa Chocron'), Contributor::fromName('Keitaro Nakatani')]])],
            [self::work('openalex', 'X', 2024, [$doi], ['authors' => [new Contributor('Keitaro Nakatani', 'Keitaro', 'Nakatani', Identifiers::of(Identifier::orcid('0009-0005-1387-7295'), Identifier::openalex('A5108007452')))]])],
        );
        self::assertSame('Léa Chocron', $merged[0]->authors[0]->name, 'Crossref\'s list');
        self::assertSame('0009-0005-1387-7295', $merged[0]->authors[1]->orcid(), 'with the ORCID OpenAlex knows');
    }

    public function testNewestFirst(): void
    {
        $merged = (new Merger())->merge([
            self::work('hal', 'Old', 2010),
            self::work('hal', 'Undated', null),
            self::work('hal', 'New', 2024, [], ['date' => '2024-09-25']),
            self::work('hal', 'Newer', 2024, [], ['date' => '2024-10-04']),
        ]);
        self::assertSame(['Newer', 'New', 'Old', 'Undated'], array_map(static fn (Work $w) => $w->title, $merged));
    }

    /** @param list<Work> $works */
    private static function titled(array $works, string $title): Work
    {
        foreach ($works as $work) {
            if ($title === $work->title) {
                return $work;
            }
        }
        self::fail($title.' not found');
    }

    public function testFingerprint(): void
    {
        self::assertSame('methode de singapour ce1 fichier de l eleve 1', Merger::fingerprint('Méthode de Singapour CE1  - Fichier de l\'élève 1'));
        self::assertSame('the large n limit', Merger::fingerprint('The Large <i>N</i> Limit'));
    }
}
